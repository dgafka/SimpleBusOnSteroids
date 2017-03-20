<?php


namespace CleanCode\SimpleBusOnSteroids\Worker;

use CleanCode\SimpleBusOnSteroids\Event;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Monolog\Logger;
use SimpleBus\Message\Bus\MessageBus;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AsyncRabbitmqProducer
 * @package CleanCode\SimpleBusOnSteroids\Worker
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 * @DI\Service()
 * @DI\Tag(name="console.command")
 */
class AsyncRabbitProducer extends Command
{
    /**
     * @var EventPublisher
     */
    private $eventPublisher;


    /**
     * DoctrineEventStore constructor.
     *
     * @param EventPublisher $eventPublisher

     * @DI\InjectParams({
     *      "eventPublisher" = @DI\Inject("simple_bus_event_publisher")
     * })
     */
    public function __construct(EventPublisher $eventPublisher)
    {
        parent::__construct('simplebus:async-producer');

        $this->eventPublisher = $eventPublisher;
    }

    protected function configure()
    {
        $this
            ->setName('simplebus:async-producer')
            ->setDescription('Asynchronously pushes messages from event store')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventPublisher->startPublishing();
    }
}