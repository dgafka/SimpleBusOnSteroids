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
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('simple_bus');
        $rootNode
            ->children()
                ->arrayNode('exception')
                    ->children()
                        ->scalarNode('requeue_max_times')
                            ->defaultValue('3')
                            ->end()
                        ->scalarNode('requeue_time')
                            ->defaultValue('3')
                            ->end()
                        ->scalarNode('requeue_multiply_by')
                            ->defaultValue('3')
                            ->end()
                        ->scalarNode('dead_letter_exchange_name')
                            ->defaultValue('asynchronous_events')
                            ->end()
                        ->scalarNode('dead_letter_queue_name')
                            ->defaultValue('dead_letter')
                            ->end()
                ->end()
                ->arrayNode('publisher')
                    ->children()
                        ->scalarNode('how_many_to_retrieve_at_once')
                            ->defaultValue('5')
                            ->end()
                        ->scalarNode('send_messages_every_seconds')
                            ->defaultValue('1.2')
                            ->end()
                ->end();

        return $treeBuilder;
    }
}