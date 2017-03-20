<?php

namespace CleanCode\SimpleBusOnSteroids\EventNameMapper;

use CleanCode\SimpleBusOnSteroids\EventNameMapper;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ClassNameEventNameMapper
 * @package CleanCode\SimpleBusOnSteroids\EventNameMapper
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @DI\Service(id="simple_bus_class_name_event_mapper")
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
     * @inheritDoc
     */
    public function classNameFrom(string $eventName): string
    {
        return $eventName;
    }
}