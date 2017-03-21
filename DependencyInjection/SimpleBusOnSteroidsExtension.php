<?php

namespace CleanCode\DependencyInjection;

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

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('simple_bus_max_requeue_times', $config['simple_bus_max_requeue_times']);
        $container->setParameter('simple_bus_base_time_in_seconds', $config['simple_bus_base_time_in_seconds']);
        $container->setParameter('simple_bus_requeue_multiply_time_by', $config['simple_bus_requeue_multiply_time_by']);
        $container->setParameter('simple_bus_synchronize_for_message_amount', $config['simple_bus_synchronize_for_message_amount']);
        $container->setParameter('simple_bus_how_many_events_at_once', $config['simple_bus_how_many_events_at_once']);
        $container->setParameter('simple_bus_send_messages_every_seconds', $config['simple_bus_send_messages_every_seconds']);
        $container->setParameter('simple_bus_dead_letter_exchange_name', $config['simple_bus_dead_letter_exchange_name']);
        $container->setParameter('simple_bus_dead_letter_queue_name', $config['simple_bus_dead_letter_queue_name']);
    }
}