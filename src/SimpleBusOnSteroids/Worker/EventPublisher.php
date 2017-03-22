<?php

namespace CleanCode\SimpleBusOnSteroids\Worker;

use CleanCode\SimpleBusOnSteroids\Event;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Serializer;
use Monolog\Logger;
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
     * DoctrineEventStore constructor.
     *
     * @param ManagerRegistry $managerRegistry
     * @param MessageBus $messageBus
     * @param Logger $logger
     * @param Serializer $serializer
     * @param int $howManyEventsAtOnce
     * @param float $sendMessagesEverySeconds
     */
    public function __construct(ManagerRegistry $managerRegistry, MessageBus $messageBus, Logger $logger, Serializer $serializer, int $howManyEventsAtOnce, float $sendMessagesEverySeconds)
    {
        $this->managerRegistry = $managerRegistry;
        $this->eventBus = $messageBus;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->howManyEventsAtOnce = (int)$howManyEventsAtOnce;
        $this->sendMessagesEverySeconds = $sendMessagesEverySeconds;
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
                $this->logger->addError($e->getMessage());
            }catch (\Throwable $e) {
                $this->managerRegistry->resetManager();
                $this->logger->addCritical($e->getMessage());
            }
        }
    }

    /**
     * @return array|Event[]
     */
    private function retrieveNotPublishedEvents() : array
    {
        $pstmt = $this->connection()
            ->prepare("
            SELECT * FROM sb_event_store sbes
WHERE sbes.event_meta_data_event_id NOT IN
      (
        SELECT event_id FROM sb_last_published_event
      )

LIMIT :events
            ");

        $pstmt->bindValue('events', $this->howManyEventsAtOnce ? $this->howManyEventsAtOnce : 5, \PDO::PARAM_INT);
        $pstmt->execute();

        $events = [];
        foreach ($pstmt->fetchAll(\PDO::FETCH_ASSOC) as $eventResult) {
            $events[] = $this->convertToEvent($eventResult);
        }

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
     * @param array $result
     * @return Event
     */
    private function convertToEvent(array $result) : Event
    {
        return Event::create(
            $result['event_data_event_name'],
            $result['event_data_payload'],
            $result['event_meta_data_event_id'],
            $result['event_meta_data_occurred_on'],
            $result['event_meta_data_correlation_id'],
            $result['event_meta_data_parent_id']
        );
    }

    /**
     * @return Connection
     */
    private function connection() : Connection
    {
        return $this->managerRegistry->getConnection();
    }
}