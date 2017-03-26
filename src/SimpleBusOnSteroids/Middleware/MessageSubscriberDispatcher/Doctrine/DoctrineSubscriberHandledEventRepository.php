<?php

namespace CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher\Doctrine;

use CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher\SubscriberHandledEvent;
use CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher\SubscriberHandledEventRepository;
use CleanCode\SimpleBusOnSteroids\Subscriber\SubscriberInformation;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\QueryBuilder;

/**
 * Class DoctrineSubscriberHandledEventRepository
 * @package CleanCode\SimpleBusOnSteroids\Middleware\MessageSubscriberDispatcher\Doctrine
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
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
        $pstmt = $connection->prepare("INSERT INTO sb_subscriber_handled_event VALUES (
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
        /** @var QueryBuilder $qb */
        $qb = $this->managerRegistry->getManager()->createQueryBuilder();

        $results = $qb
            ->select('she')
            ->from(SubscriberHandledEvent::class, 'she')
            ->where('she.subscriberName = :subscriberName')
            ->andWhere('she.eventId = :eventId')
            ->getQuery()
            ->execute([
                "subscriberName" => $subscriberInformation->name(),
                "eventId" => $eventId
            ]);

        if (array_key_exists(0, $results)) {
            return $results[0];
        }

        return null;
    }
}