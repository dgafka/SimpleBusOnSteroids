<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\EventStore\Doctrine;

use CleanCode\SimpleBusOnSteroids\ContextHolder;
use CleanCode\SimpleBusOnSteroids\Event;
use CleanCode\SimpleBusOnSteroids\EventExtraMetadata;
use CleanCode\SimpleBusOnSteroids\EventNameMapper;
use CleanCode\SimpleBusOnSteroids\Middleware\EventStore\EventStore;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Ramsey\Uuid\Uuid;

/**
 * Class DoctrineEventStore
 * @package CleanCode\SimpleBusOnSteroids\Doctrine
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
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
            $eventName = $this->eventNameMapper->eventNameFrom($event);

            if (!$eventName) {
                throw new \RuntimeException("Event name was not mapped for " . get_class($event));
            }

            $eventPayload = $this->serializer->serialize($event, "json");
            if ($event instanceof EventExtraMetadata) {
                $eventDecoratedWithMetaData = Event::createWithDescription(
                    $eventName,
                    $eventPayload,
                    Uuid::uuid4()->toString(),
                    $currentTime,
                    $context->currentCorrelationId(),
                    $event->eventExtraMetadata(),
                    $context->currentlyHandledEventId()
                );
            }else {
                $eventDecoratedWithMetaData = Event::create(
                    $eventName,
                    $eventPayload,
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

        if ($connection->getDriver()->getName() === 'oci8') {
            $pstmt = $this->oracleInsertStatement($connection);
        }else {
            $pstmt = $this->defaultInsertStatement($connection);
        }

        $occurredOn = $event->metaData()->occurredOn();
        $pstmt->execute([
            "eventMetaDataId" => $event->metaData()->eventId(),
            "eventName" => $event->eventName(),
            "eventDataPayload" => $event->payload(),
            "eventMetaDataParentId" => $event->metaData()->parentId(),
            "eventMetaDataCorrelationId" => $event->metaData()->correlationId(),
            "eventMetaDataOccurredOn" => (new \DateTimeImmutable($occurredOn))->format('Y-m-d H:i:s'),
            "eventMetaDataDescription" => $event->metaData()->description()
        ]);
    }

    /**
     * @param $connection
     * @return mixed
     */
    private function defaultInsertStatement(Connection $connection) : Statement
    {
        return $connection->prepare("INSERT INTO sb_event_store 
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
    }

    /**
     * @param $connection
     * @return mixed
     */
    private function oracleInsertStatement(Connection $connection) : Statement
    {
        return $connection->prepare("INSERT INTO sb_event_store 
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
            to_date(:eventMetaDataOccurredOn),
            :eventMetaDataDescription
        )");
    }
}