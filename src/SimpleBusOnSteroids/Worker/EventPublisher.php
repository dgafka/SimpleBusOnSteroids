<?php

namespace CleanCode\SimpleBusOnSteroids\Worker;

use CleanCode\SimpleBusOnSteroids\Event;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Monolog\Logger;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Connection\AMQPConnection;
use SimpleBus\Message\Bus\MessageBus;

/**
 * Class EventPublisher
 * @package CleanCode\SimpleBusOnSteroids\Worker
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class EventPublisher
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var MessageBus
     */
    private $eventBus;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Serializer
     */
    private $serializer;
    /**
     * @var int
     */
    private $howManyEventsAtOnce;
    /**
     * @var float
     */
    private $sendMessagesEverySeconds;
    /**
     * @var Producer
     */
    private $producer;

    /**
     * DoctrineEventStore constructor.
     *
     * @param ManagerRegistry $managerRegistry
     * @param MessageBus $messageBus
     * @param Logger $logger
     * @param Serializer $serializer
     * @param Producer $producer
     * @param int $howManyEventsAtOnce
     * @param float $sendMessagesEverySeconds
     */
    public function __construct(ManagerRegistry $managerRegistry, MessageBus $messageBus, Logger $logger, Serializer $serializer, Producer $producer, int $howManyEventsAtOnce, float $sendMessagesEverySeconds)
    {
        $this->managerRegistry = $managerRegistry;
        $this->eventBus = $messageBus;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->howManyEventsAtOnce = (int)$howManyEventsAtOnce;
        $this->sendMessagesEverySeconds = $sendMessagesEverySeconds;
        $this->producer = $producer;
    }


    public function startPublishing()
    {
        while (true) {
            sleep($this->sendMessagesEverySeconds);
            try {
                $eventsToBePublish = $this->retrieveNotPublishedEvents();
                foreach ($eventsToBePublish as $event) {
                    $this->logger->addInfo("Publishing {$event->eventName()} with id {$event->metaData()->eventId()}");
                    $this->eventBus->handle($event);
                    $this->saveAsPublishedEvent($event);
                }
            }catch (\Exception $e) {
                $this->managerRegistry->resetManager();
                $this->producer->reconnect();
                $this->logger->addError($e->getMessage());
            }catch (\Throwable $e) {
                $this->managerRegistry->resetManager();
                $this->producer->reconnect();
                $this->logger->addCritical($e->getMessage());
            }
        }
    }

    /**
     * @return array|Event[]
     */
    private function retrieveNotPublishedEvents() : array
    {
        /** @var EntityManager $em */
        $em = $this->managerRegistry->getManager();

        $events = $em->createQueryBuilder()
            ->select("event")
            ->from(Event::class, 'event')
            ->where(
                $em->createQueryBuilder()->expr()->notIn('event.metaData.eventId',
                    $em->createQueryBuilder()->select('pe.eventId')
                        ->from(PublishedEvent::class, 'pe')
                        ->getDQL()
                ))
            ->setMaxResults($this->howManyEventsAtOnce)
            ->getQuery()
            ->execute();

        return $events;
    }

    /**
     * @param Event $event
     */
    private function saveAsPublishedEvent(Event $event)
    {
        $pstmt = $this->connection()
            ->prepare("
              INSERT INTO sb_last_published_event VALUES ( :eventId )
            ");

        $results = $pstmt->execute([
            'eventId' => $event->metaData()->eventId()
        ]);
        if (!$results) {
            $this->logger->critical("Can't save as published event with id {$event->metaData()->eventId()}");
        }
    }

    /**
     * @return Connection
     */
    private function connection() : Connection
    {
        return $this->managerRegistry->getConnection();
    }
}