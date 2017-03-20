<?php


namespace CleanCode\SimpleBusOnSteroids\Middleware\Transactional;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use SimpleBus\Message\Bus\Middleware\MessageBusMiddleware;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class TransactionalMiddleware
 * @package CleanCode\SimpleBusOnSteroids\Middleware\Transactional
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 *
 * @DI\Service()
 * @DI\Tag(name="command_bus_middleware", attributes={"priority"="1998"})
 * @DI\Tag(name="asynchronous_command_bus_middleware", attributes={"priority"="1998"})
 */
class TransactionalMiddleware implements MessageBusMiddleware
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
     * @inheritDoc
     */
    public function handle($message, callable $next)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManager();

        $entityManager->beginTransaction();
        try {
            $next($message);
            $entityManager->flush();
            $entityManager->commit();
        }catch (\Exception $e) {
            $entityManager->rollback();
            $this->managerRegistry->resetManager();

            throw $e;
        }
    }
}