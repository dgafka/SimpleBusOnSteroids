<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\EventStore\Doctrine;

use CleanCode\SimpleBusOnSteroids\ContextHolder;
use CleanCode\SimpleBusOnSteroids\Event;
use CleanCode\SimpleBusOnSteroids\EventExtraMetadata;
use CleanCode\SimpleBusOnSteroids\EventNameMapper;
use CleanCode\SimpleBusOnSteroids\Middleware\EventStore\EventStore;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Serializer;
use Ramsey\Uuid\Uuid;

/**
 * Class DoctrineEventStore
 * @package CleanCode\SimpleBusOnSteroids\Doctrine
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @DI\Service(id="simple_bus_event_store")
 */
class DoctrineEventStore implements EventStore
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var ContextHolder
     */
    private $contextHolder;
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var EventNameMapper
     */
    private $eventNameMapper;

    /**
     * ContextApplyingMiddleware constructor.
     * @param ManagerRegistry $managerRegistry
     * @param ContextHolder $contextHolder
     * @param Serializer $serializer
     * @param EventNameMapper $eventNameMapper
     *
     * @DI\InjectParams({
     *      "managerRegistry" = @DI\Inject("doctrine"),
     *      "contextHolder" = @DI\Inject("simple_bus_context_holder"),
     *      "serializer" = @DI\Inject("serializer"),
     *      "eventNameMapper" = @DI\Inject("simple_bus_class_name_event_mapper")
     * })
     */
    public function __construct(ManagerRegistry $managerRegistry, ContextHolder $contextHolder, Serializer $serializer, EventNameMapper $eventNameMapper)
    {
        $this->managerRegistry = $managerRegistry;
        $this->contextHolder = $contextHolder;
        $this->serializer = $serializer;
        $this->eventNameMapper = $eventNameMapper;
    }

    /**
     * @inheritDoc
     */
    public function save(array $events)
    {
        $currentTime = new \DateTime("now", new \DateTimeZone("UTC"));
        $currentTime = $currentTime->format('Y-m-d H:i:s.u');
        $context = $this->contextHolder->currentContext();

        foreach ($events as $event) {
            if ($event instanceof EventExtraMetadata) {
                $eventDecoratedWithMetaData = Event::createWithDescription(
                    $this->eventNameMapper->eventNameFrom($event),
                    $this->serializer->serialize($event, "json"),
                    Uuid::uuid4()->toString(),
                    $currentTime,
                    $context->currentCorrelationId(),
                    $event->eventExtraMetadata(),
                    $context->currentlyHandledEventId()
                );
            }else {
                $eventDecoratedWithMetaData = Event::create(
                    $this->eventNameMapper->eventNameFrom($event),
                    $this->serializer->serialize($event, "json"),
                    Uuid::uuid4()->toString(),
                    $currentTime,
                    $context->currentCorrelationId(),
                    $context->currentlyHandledEventId()
                );
            }

            $this->saveIntoDatabase($eventDecoratedWithMetaData);
        }
    }

    /**
     * @param Event $event
     */
    private function saveIntoDatabase(Event $event)
    {
        /** @var Connection $connection */
        $connection = $this->managerRegistry->getConnection();
        $pstmt = $connection->prepare("INSERT INTO simple_bus_event_store 
            (
              event_meta_data_event_id, event_data_event_name, event_data_payload,
              event_meta_data_parent_id, event_meta_data_correlation_id,
              event_meta_data_occurred_on, event_meta_data_description
            )
            VALUES (
            :eventMetaDataId,
            :eventName,
            :eventDataPayload,
            :eventMetaDataParentId,
            :eventMetaDataCorrelationId,
            :eventMetaDataOccurredOn,
            :eventMetaDataDescription
        )");

        $pstmt->execute([
            "eventMetaDataId" => $event->metaData()->eventId(),
            "eventName" => $event->eventName(),
            "eventDataPayload" => $event->payload(),
            "eventMetaDataParentId" => $event->metaData()->parentId(),
            "eventMetaDataCorrelationId" => $event->metaData()->correlationId(),
            "eventMetaDataOccurredOn" => $event->metaData()->occurredOn(),
            "eventMetaDataDescription" => $event->metaData()->description()
        ]);
    }
}