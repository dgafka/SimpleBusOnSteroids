<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher;
use CleanCode\SimpleBusOnSteroids\Subscriber\SubscriberInformation;

/**
 * Class SubscriberHandledEventRepository
 * @package CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
interface SubscriberHandledEventRepository
{
    /**
     * @param SubscriberHandledEvent $subscriberHandledEvent
     */
    public function save(SubscriberHandledEvent $subscriberHandledEvent);

    /**
     * @param SubscriberInformation $subscriberInformation
     * @param string $eventId
     * @return SubscriberHandledEvent
     */
    public function findFor(SubscriberInformation $subscriberInformation, string $eventId);
}