<?php


namespace CleanCode\SimpleBusOnSteroids\Worker;

use CleanCode\SimpleBusOnSteroids\Event;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Monolog\Logger;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AsyncRabbitmqProducer
 * @package CleanCode\SimpleBusOnSteroids\Worker
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
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