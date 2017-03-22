<?php

namespace CleanCode\DependencyInjection\ContainerBuilder;

use CleanCode\SimpleBusOnSteroids\Subscriber\SubscriberInformation;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class SubscriberInformationCompilerPass
 * @package CleanCode\DependencyInjection\ContainerBuilder
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class SubscriberInformationCompilerPass implements CompilerPassInterface
{
    const SUBSCRIBER_NAME = 'subscriber_name';

    public function process(ContainerBuilder $container)
    {
        $injectTo = [];
        $taggedServices = $container->findTaggedServiceIds(
            'asynchronous_steroids_event_subscriber'
        );
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $serviceDefinition = $container->getDefinition($id);
                $subscriberName = $serviceDefinition->getClass();

                if (array_key_exists(self::SUBSCRIBER_NAME, $attributes)) {
                    $subscriberName = $attributes[self::SUBSCRIBER_NAME];
                }

                $injectTo[] = [
                    SubscriberInformation::CLASS_NAME => $serviceDefinition->getClass(),
                    SubscriberInformation::SUBSCRIBER_NAME => $subscriberName
                ];
            }
        }

        $targetService = $container->getDefinition('simple_bus_subscriber_subscriber_information_holder');
        $targetService->replaceArgument(0, $injectTo);
    }
}