<?php

namespace CleanCode\SimpleBusOnSteroids\ExceptionSubscriber;

use CleanCode\SimpleBusOnSteroids\Event;
use JMS\Serializer\Serializer;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use SimpleBus\RabbitMQBundleBridge\Event\Events;
use SimpleBus\RabbitMQBundleBridge\Event\MessageConsumptionFailed;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ErrorHandler
 * @package CleanCode\SimpleBusOnSteroids\ExceptionSubscriber
 */
class ErrorHandler implements EventSubscriberInterface
{
    const REQUEUE_COUNT = 'requeueCount';
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var Producer
     */
    private $producer;
    /**
     * @var int
     */
    private $maxRequeueTimes;
    /**
     * @var int
     */
    private $baseTimeInSeconds;
    /**
     * @var int
     */
    private $multiplyBy;
    /**
     * @var string
     */
    private $deadLetterExchangeName;
    /**
     * @var string
     */
    private $deadLetterQueueName;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * ContextApplyingMiddleware constructor.
     * @param Serializer $serializer
     * @param Producer $producer
     * @param int $maxRequeueTimes
     * @param int $baseTimeInSeconds
     * @param int $multiplyBy
     * @param string $deadLetterExchangeName
     * @param string $deadLetterQueueName
     * @param Logger $logger
     */
    public function __construct(
        Serializer $serializer, Producer $producer, int $maxRequeueTimes, int $baseTimeInSeconds, int $multiplyBy,
        string $deadLetterExchangeName, string $deadLetterQueueName, Logger $logger
    )
    {
        $this->serializer = $serializer;
        $this->producer = $producer;
        $this->maxRequeueTimes = $maxRequeueTimes;
        $this->baseTimeInSeconds = $baseTimeInSeconds;
        $this->multiplyBy = $multiplyBy;
        $this->deadLetterExchangeName = $deadLetterExchangeName;
        $this->deadLetterQueueName = $deadLetterQueueName;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [Events::MESSAGE_CONSUMPTION_FAILED => 'messageConsumptionFailed'];
    }

    public function messageConsumptionFailed(MessageConsumptionFailed $event)
    {
        $messageBody = $event->message()->getBody();
        $decodedMessage = json_decode($messageBody, true);

        if (!(array_key_exists('message_type', $decodedMessage) && $decodedMessage['message_type'] === Event::class)) {
            return;
        }

        $decodedMessage[self::REQUEUE_COUNT] = array_key_exists(self::REQUEUE_COUNT, $decodedMessage) ? ($decodedMessage[self::REQUEUE_COUNT] + 1) : 1;

        if (array_key_exists('exception', $decodedMessage)) {
            $decodedMessage['exception'] = json_decode($decodedMessage['exception'], true);
        }
        $decodedMessage['exception'][] = [$event->exception()->getMessage()];

        $serializedMessage = json_decode($decodedMessage['serialized_message'], true);
        $eventId = $serializedMessage['meta_data_']['event_id'];
        $requeueCount = $decodedMessage[self::REQUEUE_COUNT];

        if ($requeueCount >= $this->maxRequeueTimes) {
            $decodedMessage['exception'][] = [$event->exception()->getTraceAsString()];
            $decodedMessage['exception'] = json_encode($decodedMessage['exception']);

            $this->logger->alert("Message with id {$eventId} has reached max requeue times. Can't handle message, exception: {$event->exception()->getMessage()}");
            $this->publishToDeadLetterQueue($decodedMessage);
            return;
        }

        $this->logger->error("Starting to requeue message with id {$eventId}. Requeue count {$requeueCount}. Exception: {$event->exception()->getMessage()}");
        $decodedMessage['exception'] = json_encode($decodedMessage['exception']);
        $this->requeueMessage($event, $requeueCount, $decodedMessage);

        //https://www.rabbitmq.com/blog/2015/04/16/scheduling-messages-with-rabbitmq/
        //http://stackoverflow.com/questions/14264137/rabbitmq-set-message-properties-php
        //https://github.com/rabbitmq/rabbitmq-delayed-message-exchange
    }

    /**
     * @param int $requeueCount
     * @return mixed
     */
    private function calculateDelay(int $requeueCount)
    {
        if ($this->isFirstRequeue($requeueCount)) {
            return $this->baseTimeInSeconds * $this->microSecondsToSeconds();
        }

        return $requeueCount * $this->baseTimeInSeconds * $this->multiplyBy * $this->microSecondsToSeconds();
    }

    /**
     * @return int
     */
    private function microSecondsToSeconds(): int
    {
        return 1000;
    }

    /**
     * @param int $requeueCount
     * @return bool
     */
    private function isFirstRequeue(int $requeueCount): bool
    {
        return $requeueCount === 1;
    }

    /**
     * @param MessageConsumptionFailed $event
     * @param $requeueCount
     * @param $decodedMessage
     */
    private function requeueMessage(MessageConsumptionFailed $event, $requeueCount, $decodedMessage)
    {
        $properties = ["x-delay" => $this->calculateDelay($requeueCount)];
        $exchange = $event->message()->delivery_info['exchange'];
        $routingKey = $event->message()->delivery_info['routing_key'];

        $this->producer->setExchangeOptions([
            "name" => $exchange,
            "type" => 'x-delayed-message'
        ]);
        $this->producer->publish(json_encode($decodedMessage), $routingKey, [], $properties);
    }

    /**
     * @param $decodedMessage
     */
    private function publishToDeadLetterQueue($decodedMessage)
    {
        $this->producer->setExchangeOptions([
            "name" => $this->deadLetterExchangeName,
            "type" => 'x-delayed-message'
        ]);
        $this->producer->publish(json_encode($decodedMessage), $this->deadLetterQueueName, [], []);
    }
}