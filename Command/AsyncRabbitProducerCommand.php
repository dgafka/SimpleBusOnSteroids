<?php


namespace CleanCode\Command;

use CleanCode\SimpleBusOnSteroids\Worker\EventPublisher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AsyncRabbitmqProducer
 * @package CleanCode\SimpleBusOnSteroids\Worker
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class AsyncRabbitProducerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('simplebus:async-producer')
            ->setDescription('Asynchronously pushes messages from event store')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EventPublisher $eventPublisher */
        $eventPublisher = $this->getContainer()->get('simple_bus_event_publisher');

        $eventPublisher->startPublishing();
    }
}