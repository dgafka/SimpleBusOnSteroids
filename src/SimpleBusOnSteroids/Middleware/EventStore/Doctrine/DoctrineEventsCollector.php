<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\EventStore\Doctrine;

use CleanCode\SimpleBusOnSteroids\ContextHolder;
use CleanCode\SimpleBusOnSteroids\Event;
use CleanCode\SimpleBusOnSteroids\EventData;
use CleanCode\SimpleBusOnSteroids\EventNameMapper;
use CleanCode\SimpleBusOnSteroids\EventExtraMetadata;
use CleanCode\SimpleBusOnSteroids\MetaData;
use CleanCode\SimpleBusOnSteroids\Middleware\EventStore\EventStore;
use CleanCode\SimpleBusOnSteroids\Middleware\EventStore\NewEventsCollector;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use JMS\Serializer\Serializer;
use Ramsey\Uuid\Uuid;
use SimpleBus\Message\Recorder\ContainsRecordedMessages;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class DoctrineEventsCollector
 * @package CleanCode\SimpleBusOnSteroids\Doctrine
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @DI\Service()
 * @DI\Tag(name="doctrine.event_subscriber", attributes={"connection":"default"})
 */
class DoctrineEventsCollector implements EventSubscriber, NewEventsCollector
{
    /**
     * @var ContextHolder
     */
    private $contextHolder;
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var EventStore
     */
    private $eventStore;
    /**
     * @var EventNameMapper
     */
    private $eventNameMapper;

    /**
     * ContextApplyingMiddleware constructor.
     * @param ContextHolder $contextHolder
     * @param Serializer $serializer
     * @param EventStore $eventStore
     *
     * @param EventNameMapper $eventNameMapper
     * @DI\InjectParams({
     *      "contextHolder" = @DI\Inject("simple_bus_context_holder"),
     *      "serializer" = @DI\Inject("serializer"),
     *      "eventStore" = @DI\Inject("simple_bus_event_store"),
     *      "eventNameMapper" = @DI\Inject("simple_bus_class_name_event_mapper")
     * })
     */
    public function __construct(ContextHolder $contextHolder, Serializer $serializer, EventStore $eventStore, EventNameMapper $eventNameMapper)
    {
        $this->contextHolder = $contextHolder;
        $this->serializer = $serializer;
        $this->eventStore = $eventStore;
        $this->eventNameMapper = $eventNameMapper;
    }

    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
            Events::preRemove
        );
    }

    public function prePersist(LifecycleEventArgs $event)
    {
        $this->collectEventsFromEntity($event);
    }

    public function preUpdate(LifecycleEventArgs $event)
    {
        $this->collectEventsFromEntity($event);
    }

    public function preRemove(LifecycleEventArgs $event)
    {
        $this->collectEventsFromEntity($event);
    }

    /**
     * @inheritDoc
     */
    public function collectedEvents(): array
    {
        throw new \InvalidArgumentException("sadsd");
    }

    /**
     * @param LifecycleEventArgs $event
     */
    private function collectEventsFromEntity(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $events = [];

        if ($entity instanceof ContainsRecordedMessages) {
            foreach ($entity->recordedMessages() as $event) {
                $events[] = $event;
            }

            $entity->eraseMessages();
        }

        if (!$events) {
            return;
        }

        $this->eventStore->save($events);
    }
}