<?php

namespace CleanCode;

use CleanCode\DependencyInjection\ContainerBuilder\SubscriberInformationCompilerPass;
use CleanCode\DependencyInjection\SimpleBusOnSteroidsExtension;
use SimpleBus\SymfonyBridge\DependencyInjection\Compiler\RegisterSubscribers;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class AppBundle
 * @package AppBundle
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class SimpleBusOnSteroidsBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new SimpleBusOnSteroidsExtension();
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new RegisterSubscribers(
                'simple_bus.asynchronous_steroids.event_bus.event_subscribers_collection',
                'asynchronous_steroids_event_subscriber',
                'subscribes_to'
            )
        );
        $container->addCompilerPass(
            new SubscriberInformationCompilerPass()
        );
    }
}
