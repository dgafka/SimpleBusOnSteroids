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
     * Returns, if specific event is mapped, which means should be handled within system
     *
     * @param string $eventName
     * @return bool
     */
    public function isMapped(string $eventName) : bool;

    /**
     * Opposite to eventNameFrom
     *
     * @param string $eventName
     * @return string
     */
    public function classNameFrom(string $eventName) : string;
}