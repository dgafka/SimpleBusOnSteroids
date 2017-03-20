<?php

namespace CleanCode\SimpleBusOnSteroids;

use JMS\Serializer\Annotation as JMS;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class MetaData
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @ORM\Embeddable()
 */
class MetaData
{
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("event_id")
     */
    private $eventId;
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("parent_id")
     */
    private $parentId;
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("correlation_id")
     */
    private $correlationId;
    /**
     * @var \DateTime
     * @JMS\Type("DateTime")
     * @JMS\SerializedName("occurred_on")
     */
    private $occurredOn;
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\SerializedName("description")
     */
    private $description;

    /**
     * MetaData constructor.
     * @param string $eventId
     * @param \DateTime $occurredOn
     * @param string $correlationId
     * @param string|null $parentId
     * @param string $description
     */
    public function __construct(string $eventId, \DateTime $occurredOn, string $correlationId, string $description, string $parentId = null)
    {
        $this->eventId = $eventId;
        $this->parentId = $parentId;
        $this->correlationId = $correlationId;
        $this->occurredOn = $occurredOn;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function eventId() : string
    {
        return $this->eventId;
    }

    /**
     * @return string|null
     */
    public function parentId()
    {
        return $this->parentId;
    }

    /**
     * @return string
     */
    public function correlationId() : string
    {
        return $this->correlationId;
    }

    /**
     * @return string
     */
    public function occurredOn() : string
    {
        return $this->occurredOn->format("Y-m-d H:i:s.u");
    }

    /**
     * @return string
     */
    public function description() : string
    {
        return $this->description;
    }
}