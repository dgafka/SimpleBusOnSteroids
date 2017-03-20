<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher\Doctrine;

use CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher\SubscriberHandledEvent;
use CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher\SubscriberHandledEventRepository;
use CleanCode\SimpleBusOnSteroids\Subscriber\SubscriberInformation;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Driver\Connection;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class DoctrineSubscriberHandledEventRepository
 * @package CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher\Doctrine
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @DI\Service(id="simple_bus_doctrine_subscriber_handled_event_repository")
 */
class DoctrineSubscriberHandledEventRepository implements SubscriberHandledEventRepository
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * DoctrineEventStore constructor.
     * @param ManagerRegistry $managerRegistry
     *
     * @DI\InjectParams({
     *      "managerRegistry" = @DI\Inject("doctrine")
     * })
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param SubscriberHandledEvent $subscriberHandledEvent
     */
    public function save(SubscriberHandledEvent $subscriberHandledEvent)
    {
        /** @var Connection $connection */
        $connection = $this->managerRegistry->getConnection();
        $pstmt = $connection->prepare("INSERT INTO simple_bus_subscriber_handled_event VALUES (
            :subscriberName,
            :eventId
        )");

        $pstmt->execute([
            "subscriberName" => $subscriberHandledEvent->name(),
            "eventId" => $subscriberHandledEvent->eventId()
        ]);
    }

    public function findFor(SubscriberInformation $subscriberInformation, string $eventId)
    {
        /** @var Connection $connection */
        $connection = $this->managerRegistry->getConnection();
        $pstmt = $connection->prepare("SELECT * FROM simple_bus_subscriber_handled_event
            WHERE subscriber_name = :subscriberName 
            AND event_id = :eventId
        ");

        $pstmt->execute([
            "subscriberName" => $subscriberInformation->name(),
            "eventId" => $eventId
        ]);

        $results = $pstmt->fetchAll(\PDO::FETCH_ASSOC);

        if (array_key_exists(0, $results)) {
            return SubscriberHandledEvent::createWith(
                $results[0]['subscriber_name'],
                $results[0]['event_id']
            );
        }

        return null;
    }
}