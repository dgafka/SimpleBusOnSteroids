<?php

namespace CleanCode\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * Class SimpleBusOnSteroidsExtension
 * @package AppBundle\Rabbitmq\Bundle
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class SimpleBusOnSteroidsExtension extends Extension
{
    /**
     * @inheritDoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setParameter('empty_array', []);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $processor = new Processor();
        $configuration = new SimpleBusConfiguration();
        $config = $processor->processConfiguration($configuration, $configs);

        if (empty($config)) {
            throw new \RuntimeException("You must provide config for 'simple_bus_on_steroids'. At least empty one.");
        }

        $container->setParameter('simple_bus.exception.requeue_max_times', $config['requeue_max_times']);
        $container->setParameter('simple_bus.exception.requeue_time', $config['requeue_time']);
        $container->setParameter('simple_bus.exception.requeue_multiply_by', $config['requeue_multiply_by']);
        $container->setParameter('simple_bus.exception.dead_letter_exchange_name', $config['dead_letter_exchange_name']);
        $container->setParameter('simple_bus.exception.dead_letter_queue_name', $config['dead_letter_queue_name']);
        $container->setParameter('simple_bus_how_many_events_at_once', $config['how_many_to_retrieve_at_once']);
        $container->setParameter('simple_bus_send_messages_every_seconds', $config['send_messages_every_seconds']);

        if (!$container->has('simple_bus_name_mapper')) {
            $container->setAlias('simple_bus_name_mapper', 'simple_bus_class_name_event_mapper');
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new SimpleBusConfiguration();
    }
}