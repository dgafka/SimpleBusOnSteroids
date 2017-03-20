<?php

namespace CleanCode\SimpleBusOnSteroids;

/**
 * Interface EventNameMapper
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
interface EventNameMapper
{
    /**
     * Generates event name from event
     *
     * @param object $event
     * @return string
     */
    public function eventNameFrom($event) : string;

    /**
     * Opposite to eventNameFrom
     *
     * @param string $eventName
     * @return string
     */
    public function classNameFrom(string $eventName) : string;
}