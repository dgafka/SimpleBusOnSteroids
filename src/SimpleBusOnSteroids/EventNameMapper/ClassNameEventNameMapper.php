<?php

namespace CleanCode\SimpleBusOnSteroids\EventNameMapper;

use CleanCode\SimpleBusOnSteroids\EventNameMapper;

/**
 * Class ClassNameEventNameMapper
 * @package CleanCode\SimpleBusOnSteroids\EventNameMapper
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class ClassNameEventNameMapper implements EventNameMapper
{
    /**
     * @inheritDoc
     */
    public function eventNameFrom($event): string
    {
        return get_class($event);
    }

    /**
     * @inheritdoc
     */
    public function isMapped(string $eventName) : bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function classNameFrom(string $eventName): string
    {
        return $eventName;
    }
}