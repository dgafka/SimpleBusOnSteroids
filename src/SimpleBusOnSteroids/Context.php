<?php

namespace CleanCode\SimpleBusOnSteroids;

use Ramsey\Uuid\Uuid;

/**
 * Class Context
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class Context
{
    /**
     * @var string
     */
    private $currentCorrelationId;
    /**
     * @var string
     */
    private $currentlyHandledEventId;

    /**
     * Context constructor.
     * @param string $currentCorrelationId
     * @param string|null $currentlyHandledEventId
     */
    private function __construct(string $currentCorrelationId, string $currentlyHandledEventId = null)
    {
        $this->currentlyHandledEventId = $currentlyHandledEventId;
        $this->currentCorrelationId = $currentCorrelationId;
    }

    /**
     * @param MetaData $metaData
     * @return Context
     */
    public static function fromMetaData(MetaData $metaData) : self
    {
        return new self(
            $metaData->correlationId(),
            $metaData->eventId()
        );
    }

    /**
     * @return Context
     */
    public static function withNoParentEvent() : self
    {
        return new self(
            Uuid::uuid4()->toString()
        );
    }

    /**
     * @return string|null
     */
    public function currentlyHandledEventId()
    {
        return $this->currentlyHandledEventId;
    }

    /**
     * @return string
     */
    public function currentCorrelationId() : string
    {
        return $this->currentCorrelationId;
    }
}