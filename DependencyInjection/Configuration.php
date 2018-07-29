<?php

namespace Beyerz\AWSQueueBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    const DEFAULT_AWS_REGION='us-east-1';

    const KEY_AWS_REGION = 'region';
    const KEY_AWS_ACCOUNT = 'account';
    const KEY_CHANNEL_PREFIX = 'channel_prefix';
    const KEY_RUN_LOCAL = 'run_local';
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('beyerz_aws_queue');

        $rootNode->children()
            ->scalarNode(self::KEY_AWS_REGION)->defaultValue(self::DEFAULT_AWS_REGION)->end()
            ->scalarNode(self::KEY_AWS_ACCOUNT)->isRequired()->end()
            ->scalarNode(self::KEY_CHANNEL_PREFIX)->defaultNull()->end()
            ->scalarNode(self::KEY_RUN_LOCAL)->defaultFalse()->end()
            ->arrayNode('consumers')
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')->end()
                        ->scalarNode('producer')->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('producers')
                ->prototype('scalar')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
