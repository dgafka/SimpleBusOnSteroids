# Simple Bus On Steroids

#### Description

Extension for `asynchronous simplebus`. Provides multiple features for securing your async works.
 
## Installation

1. Create database structure

    CREATE TABLE simple_bus_event_store (event_meta_data_event_id VARCHAR(255) NOT NULL, event_data_event_name VARCHAR(255) NOT NULL, event_data_payload LONGTEXT NOT NULL COMMENT '(DC2Type:json_array)', event_meta_data_parent_id VARCHAR(255) DEFAULT NULL, event_meta_data_correlation_id VARCHAR(255) NOT NULL, event_meta_data_occurred_on DATETIME NOT NULL, event_meta_data_description VARCHAR(255) NOT NULL, PRIMARY KEY(event_meta_data_event_id))
    
    CREATE TABLE simple_bus_subscriber_handled_event (subscriber_name VARCHAR(255) NOT NULL, event_id VARCHAR(255) NOT NULL, PRIMARY KEY(subscriber_name, event_id))
    
    CREATE TABLE simple_bus_last_published_event (event_id VARCHAR(255) NOT NULL, PRIMARY KEY(event_id))
    
    
