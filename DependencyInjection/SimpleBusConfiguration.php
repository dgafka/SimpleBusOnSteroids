<?php

namespace CleanCode\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Knp\Bundle\MenuBundle\DependencyInjection
 * @author Dariusz Gafka <dgafka.mail@gmail.com>
 */
class SimpleBusConfiguration implements ConfigurationInterface
{
    const ALIAS = 'simple_bus_on_steroids';

    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::ALIAS);
        $rootNode
            ->children()
                ->scalarNode('requeue_max_times')
                    ->cannotBeEmpty()
                    ->defaultValue('3')
                    ->info("Max tries of requeue before message will go to the dead letter queue")
                ->end()
                ->scalarNode('requeue_time')
                    ->cannotBeEmpty()
                    ->defaultValue('3')
                    ->info("Amount of seconds before message will be handled after fail")
                ->end()
                ->scalarNode('requeue_multiply_by')
                    ->cannotBeEmpty()
                    ->defaultValue('3')
                    ->info("How many times multiply requeue time for each time message which fail")
                ->end()
                ->scalarNode('dead_letter_exchange_name')
                    ->cannotBeEmpty()
                    ->defaultValue('asynchronous_events')
                    ->info('Name of the exchange where broken message will be published')
                ->end()
                ->scalarNode('dead_letter_queue_name')
                    ->cannotBeEmpty()
                    ->defaultValue('dead_letter')
                    ->info('Name of the queue where broken messages will be published')
                ->end()
                ->scalarNode('requeue_exchange_name')
                    ->defaultValue('')
                    ->info('Requeued message will be published to passed exchange name. If not passed it will be taken directly from message')
                ->end()
                ->scalarNode('requeue_routing_key')
                    ->defaultValue('')
                    ->info('This routing key will be added to requeued message. If not passed it will be taken directly from message')
                ->end()
                ->scalarNode('how_many_to_retrieve_at_once')
                    ->cannotBeEmpty()
                    ->defaultValue('5')
                    ->info('How many message should be retrieved at once to be published')
                ->end()
                ->scalarNode('send_messages_every_seconds')
                    ->cannotBeEmpty()
                    ->defaultValue('1.2')
                    ->info('Break between publishing in seconds')
                ->end()
            ->end();

        return $treeBuilder;
    }
}