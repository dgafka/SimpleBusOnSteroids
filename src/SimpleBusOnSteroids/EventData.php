<?php


namespace CleanCode\SimpleBusOnSteroids;

use JMS\Serializer\Annotation as JMS;

/**
 * Class EventData
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class EventData
{
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("event_name")
     */
    private $eventName;
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("payload")
     */
    private $payload;

    /**
     * EventData constructor.
     * @param string $eventName
     * @param string $payload
     */
    public function __construct(string $eventName, string $payload)
    {
        $this->eventName = $eventName;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function eventName() : string
    {
        return $this->eventName;
    }

    /**
     * @return string
     */
    public function payload() : string
    {
        return $this->payload;
    }
}