<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\EventStore;

/**
 * Interface EventStore
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
interface EventStore
{
    /**
     * @param array|object[] $events
     * @return void
     */
    public function save(array $events);
}