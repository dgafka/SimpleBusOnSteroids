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
                ->arrayNode('exception')
                    ->children()
                        ->scalarNode('requeue_max_times')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('3')
                            ->info("Max tries of requeue before message will go to the dead letter queue")
                        ->end()
                        ->scalarNode('requeue_time')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('3')
                            ->info("Amount of seconds before message will be handled after fail")
                        ->end()
                        ->scalarNode('requeue_multiply_by')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('3')
                            ->info("How many times multiply requeue time for each time message which fail")
                        ->end()
                        ->scalarNode('dead_letter_exchange_name')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('asynchronous_events')
                            ->info('Name of the exchange where broken message will be published')
                        ->end()
                        ->scalarNode('dead_letter_queue_name')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('dead_letter')
                            ->info('Name of the queue where broken messages will be published')
                        ->end()
                    ->end()
                 ->end()
                ->arrayNode('publisher')
                    ->children()
                        ->scalarNode('how_many_to_retrieve_at_once')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('5')
                            ->info('How many message should be retrieved at once to be published')
                        ->end()
                        ->scalarNode('send_messages_every_seconds')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('1.2')
                            ->info('Break between publishing in seconds')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}