<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\ContextRetrieving;

use CleanCode\SimpleBusOnSteroids\Context;
use CleanCode\SimpleBusOnSteroids\ContextHolder;
use CleanCode\SimpleBusOnSteroids\Event;
use CleanCode\SimpleBusOnSteroids\EventNameMapper;
use JMS\Serializer\Serializer;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;

/**
 * Class ContextApplyingMiddleware
 * @package CleanCode\SimpleBusOnSteroids
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class ContextRetrievingMiddleware implements MessageBusMiddleware
{
    /**
     * @var ContextHolder
     */
    private $contextHolder;
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var EventNameMapper
     */
    private $eventNameMapper;

    /**
     * ContextApplyingMiddleware constructor.
     * @param ContextHolder $contextHolder
     * @param Serializer $serializer
     * @param EventNameMapper $eventNameMapper
     */
    public function __construct(ContextHolder $contextHolder, Serializer $serializer, EventNameMapper $eventNameMapper)
    {
        $this->contextHolder = $contextHolder;
        $this->serializer = $serializer;
        $this->eventNameMapper = $eventNameMapper;
    }

    /**
     * @inheritDoc
     */
    public function handle($message, callable $next)
    {
        $this->contextHolder->setCurrentContext(
            Context::withNoParentEvent()
        );

        if ($message instanceof Event) {
            if ($message->metaData()) {
                $this->contextHolder->setCurrentContext(
                    Context::fromMetaData($message->metaData())
                );
            }

            $this->runEvent($message, $next);
            return;
        }

        $this->runCommand($message, $next);
    }

    /**
     * @param $message
     * @param callable $next
     */
    private function runCommand($message, callable $next)
    {
        $next($message);
    }

    /**
     * @param Event $message
     * @param callable $next
     */
    private function runEvent(Event $message, callable $next)
    {
        $next(
            $this->serializer->deserialize(
                $message->payload(),
                $this->eventNameMapper->classNameFrom($message->eventName()),
                "json"
            )
        );
    }
}