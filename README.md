# Simple Bus On Steroids

#### Description

Extension for `asynchronous simplebus`. Provides multiple features for securing your asynchronous work.  
[Add to your composer.json cleancode/simple-bus-on-steroids](https://packagist.org/packages/cleancode/simple-bus-on-steroids) 

### What does it provides

1. Transactions exists for event subscribers
2. As there may be a lot of subscribers for each event, each subscriber is `handled` in it's own transaction.  
 If one of the subscribers fails, the rest of them are not interrupted and finish normally
3. Each event is `decorated` with
    * event id (which is just own identification of event)
    * correlation id (which is id of all correlated events)
    * parent id (which is id of event that triggered this one)
    * occurrence time
    * optionally description, which may be used to create event sourced entity
4. Each event published from entity is saved within same transaction as entity itself.   
This provides assurance, that events will never be lost
5. Events are pushed to the queue by so called `async rabbit pattern`, which 
means that async worker take them out from database and `push` to the rabbitmq.   
6. If event fails, it will be `requeued` for specific amount of time (configurable)
7. If event fails more than x (configurable) times, it will be put in dead letter queue (configurable where)
8. Each event subscriber will `handle` successfully event only one time.  
Which means, that if one subscriber fails and other finished with success, when message is requeued 
successful subscriber won't handle the message anymore
9. Possibility to `"restart"` async producer, so all the events will be send one more time. 
This can be done by removing information about published events from database.  
This can be used to recreate Read Model for example
10. Little amount of configuration to start using
12. Possibility to name the events, agnostic to class names.  
    So when you will change class name, events that are in the database already won't suffer
13. Possibility to name subscribers, agnostic to class names.
    Information about, which subscriber handled which event is stored in database. 
    So when you will change class name of subscriber it won't have any effect 
    

 
## Installation

Before:  
    If you have [LongRunningBundle](https://github.com/LongRunning/LongRunning) installed you need to remove it.  
    You can't make use of `asynchronous_event_subscriber` and `event_subscriber` anymore. It's replace with `asynchronous_steroids_event_subscriber`

1. Change rabbitmq configuration. You need to have [delayed-message plugin](https://github.com/rabbitmq/rabbitmq-delayed-message-exchange) installed in your rabbitmq instance
  
  
    
        old_sound_rabbit_mq:
            producers:
                asynchronous_events:
                    connection:       default
                    exchange_options: { name: 'asynchronous_events', type: x-delayed-message, arguments: {x-delayed-type: ['S', "topic"]} }
            consumers:
                asynchronous_events_consumer:
                    connection:       default
                    exchange_options: { name: 'asynchronous_events', type: x-delayed-message, arguments: {x-delayed-type: ['S', "topic"]} }
                    queue_options:    { name: 'queue_asynchronous_events', routing_keys: ['all'] }
                    callback:         simple_bus.on_steorids.rabbit_mq_bundle_bridge.events_consumer
                    qos_options:
                        prefetch_count: 5        

2. Create database structure



        a) using doctrine migrations. Set in config.yml
        
        doctrine:
            orm:
                entity_managers:
                    default:
                        auto_mapping: true
                        mappings:
                            SimpleBusOnSteroidsBundle:
                              type: yml
                              dir: "Resources/Doctrine"
                              prefix: CleanCode
                              is_bundle: true
    
    
        b) using direct SQLs
        
            CREATE TABLE sb_event_store (event_meta_data_event_id VARCHAR(255) NOT NULL, event_data_event_name VARCHAR(255) NOT NULL, event_data_payload LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', event_meta_data_parent_id VARCHAR(255) DEFAULT NULL, event_meta_data_correlation_id VARCHAR(255) NOT NULL, event_meta_data_occurred_on DATETIME NOT NULL, event_meta_data_description VARCHAR(255) NOT NULL, PRIMARY KEY(event_meta_data_event_id))
            
            CREATE TABLE sb_subscriber_handled_event (subscriber_name VARCHAR(255) NOT NULL, event_id VARCHAR(255) NOT NULL, PRIMARY KEY(subscriber_name, event_id))
            
            CREATE TABLE sb_last_published_event (event_id VARCHAR(255) NOT NULL, PRIMARY KEY(event_id))

2. Change subscribers tags to
        
          

         XML
         <tag name="asynchronous_steroids_event_subscriber" subscribes_to="Events\PersonWasCreated" subscriber_name="person_was_created_subscriber"/>
         Annotation   
         @DI\Tag("asynchronous_steroids_event_subscriber", attributes={"subscribes_to" = "AppBundle\Entity\PersonWasCreated", "subscriber_name" = "person_was_created_sub"})
     

3. Start async publisher

    
    bin/console simplebus:async-producer -vvv
    
    
## Configuration

        
        
        simple_bus_on_steroids:
            requeue_max_times: 3
            requeue_time: 3
            requeue_multiply_by: 3
            dead_letter_exchange_name: asynchronous_events
            dead_letter_queue_name: dead_letter
            how_many_to_retrieve_at_once: 5
            send_messages_every_seconds: 1.2
            
        requeue_max_times - Max tries of requeue before message will go to the dead letter queue (default: 3)
        requeue_time - Amount of seconds before message will be handled after fail (default: 3)
        requeue_multiply_by - How many times multiply requeue time for each time message which fail (default: 3)
        dead_letter_exchange_name - Name of the exchange where broken message will be published (default: asynchronous_events)\
        dead_letter_queue_name - Name of the queue where broken messages will be published (default: dead_letter)
        how_many_to_retrieve_at_once - How many message should be retrieved at once to be published (default: 5)
        send_messages_every_seconds - Break between publishing in seconds (default: 1.2)
        