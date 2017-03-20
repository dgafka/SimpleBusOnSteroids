<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\EventStore;

/**
 * Interface NewEventsRecorder
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
interface NewEventsCollector
{
    /**
     * @return array
     */
    public function collectedEvents() : array;
}