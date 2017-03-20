<?php

namespace CleanCode\SimpleBusOnSteroids\Worker;

/**
 * Class PublishEvent
 * @package CleanCode\SimpleBusOnSteroids\Worker
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class PublishedEvent
{
    /**
     * @var string
     */
    private $eventId;

    /**
     * PublishEvent constructor.
     * @param string $eventId
     */
    public function __construct(string $eventId)
    {
        $this->eventId = $eventId;
    }
}