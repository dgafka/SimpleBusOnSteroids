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

/**
 * Class DoctrineEventsCollector
 * @package CleanCode\SimpleBusOnSteroids\Doctrine
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
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