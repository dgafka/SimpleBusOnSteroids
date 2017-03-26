<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\ContextRetrieving;

use CleanCode\SimpleBusOnSteroids\Context;
use CleanCode\SimpleBusOnSteroids\ContextHolder;
use CleanCode\SimpleBusOnSteroids\Event;
use CleanCode\SimpleBusOnSteroids\EventNameMapper;
use JMS\Serializer\Serializer;
use Monolog\Logger;
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
     * @var Logger
     */
    private $logger;

    /**
     * ContextApplyingMiddleware constructor.
     * @param ContextHolder $contextHolder
     * @param Serializer $serializer
     * @param EventNameMapper $eventNameMapper
     * @param Logger $logger
     */
    public function __construct(ContextHolder $contextHolder, Serializer $serializer, EventNameMapper $eventNameMapper, Logger $logger)
    {
        $this->contextHolder = $contextHolder;
        $this->serializer = $serializer;
        $this->eventNameMapper = $eventNameMapper;
        $this->logger = $logger;
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
        if ($this->eventNameMapper->isMapped($message->eventName())) {
            $className = $this->eventNameMapper->classNameFrom($message->eventName());

            if ($className) {
                $this->logger->addAlert("Mapping for {$message->eventName()} doesn't exists");
                throw new \RuntimeException("Mapping for {$message->eventName()} doesn't exists");
            }

            $next(
                $this->serializer->deserialize(
                    $message->payload(),
                    $className,
                    "json"
                )
            );
        }
    }
}