<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher;

use CleanCode\SimpleBusOnSteroids\Subscriber\SubscriberInformation;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Subscriber
 * @package CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class SubscriberHandledEvent
{
    /**
     * @var string
     */
    private $subscriberName;
    /**
     * @var string
     */
    private $eventId;

    /**
     * SubscriberHandledEvent constructor.
     * @param string $subscriberName
     * @param string $eventId
     */
    private function __construct(string $subscriberName, string $eventId)
    {
        $this->subscriberName = $subscriberName;
        $this->eventId = $eventId;
    }

    /**
     * @param SubscriberInformation $subscriberInformation
     * @param string $eventId
     * @return SubscriberHandledEvent
     */
    public static function createWithSubscriberInformation(SubscriberInformation $subscriberInformation, string $eventId) : self
    {
        return new self($subscriberInformation->name(), $eventId);
    }

    /**
     * @param string $subscriberName
     * @param string $eventId
     * @return SubscriberHandledEvent
     */
    public static function createWith(string $subscriberName, string $eventId) : self
    {
        return new self($subscriberName, $eventId);
    }

    /**
     * @return string
     */
    public function name() : string
    {
        return $this->subscriberName;
    }

    /**
     * @return string
     */
    public function eventId() : string
    {
        return $this->eventId;
    }
}