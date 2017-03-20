<?php

namespace CleanCode\SimpleBusOnSteroids;

/**
 * Interface EventDescription
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
interface EventExtraMetadata
{
    /**
     * @return string
     */
    public function eventExtraMetadata() : string;
}