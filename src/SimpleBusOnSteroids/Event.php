<?php


namespace CleanCode\SimpleBusOnSteroids;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Event
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
final class Event
{
    /**
     * @var EventData
     * @JMS\Type("CleanCode\SimpleBusOnSteroids\EventData")
     * @JMS\SerializedName("event_data_")
     */
    private $eventData;
    /**
     * @var  MetaData
     * @JMS\Type("CleanCode\SimpleBusOnSteroids\MetaData")
     * @JMS\SerializedName("meta_data_")
     */
    private $metaData;

    /**
     * Event constructor.
     * @param EventData $eventData
     * @param MetaData $metaData
     */
    public function __construct(EventData $eventData, MetaData $metaData)
    {
        $this->eventData = $eventData;
        $this->metaData = $metaData;
    }

    /**
     * @param string $eventName
     * @param string $eventPayload
     * @param string $eventId
     * @param string $occurredOn
     * @param string $correlationId
     * @param string|null $parentId
     * @return Event
     */
    public static function create(
        string $eventName, string $eventPayload,
        string $eventId, string $occurredOn, string $correlationId,
        string $parentId = null
    ) : self
    {
        return new self(
            new EventData($eventName, $eventPayload),
            new MetaData($eventId, new \DateTime($occurredOn), $correlationId, '', $parentId)
        );
    }

    /**
     * @param string $eventName
     * @param string $eventPayload
     * @param string $eventId
     * @param string $occurredOn
     * @param string $correlationId
     * @param string $description
     * @param string|null $parentId
     * @return Event
     */
    public static function createWithDescription(
        string $eventName, string $eventPayload,
        string $eventId, string $occurredOn, string $correlationId,
        string $description, string $parentId = null
    ) : self
    {
        return new self(
            new EventData($eventName, $eventPayload),
            new MetaData($eventId, new \DateTime($occurredOn), $correlationId, $description, $parentId)
        );
    }

    /**
     * @inheritDoc
     */
    public function metaData(): MetaData
    {
        return $this->metaData;
    }

    /**
     * @return string
     */
    public function payload() : string
    {
        return $this->eventData->payload();
    }

    /**
     * @return string
     */
    public function eventName() : string
    {
        return $this->eventData->eventName();
    }
}