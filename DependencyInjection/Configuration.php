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
    const DEFAULT_AWS_REGION = 'us-east-1';

    const KEY_AWS_REGION = 'region';
    const KEY_AWS_ACCOUNT = 'account';
    /**
     * @deprecated since v1.0 will be removed in v2.0, use Configuration::KEY_PREFIX instead
     */
    const KEY_CHANNEL_PREFIX = 'channel_prefix';
    const KEY_PREFIX = 'prefix';
    const KEY_RUN_LOCAL = 'run_local';
    const KEY_ENABLE_FORKING = 'enable_forking';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('beyerz_aws_queue');

        $rootNode->children()
            ->scalarNode(self::KEY_AWS_REGION)->defaultValue(self::DEFAULT_AWS_REGION)->info("Region that you want to use, like us-east-1")->end()
            ->scalarNode(self::KEY_AWS_ACCOUNT)->isRequired()->info("AWS account number")->end()
            ->scalarNode(self::KEY_PREFIX)->defaultNull()->info("The value to use as a prefix for all your topics and queues. This is great for separating dev/stage/prod environments using %kernel.environment%")->end()
            ->scalarNode(self::KEY_CHANNEL_PREFIX)->defaultNull()
            ->info('"channel_prefix" is deprecated since version v1.0 and will be removed in 2.0. Use "prefix" instead.')
            ->end()
            ->scalarNode(self::KEY_RUN_LOCAL)->defaultFalse()->end()
            ->scalarNode(self::KEY_ENABLE_FORKING)->defaultTrue()->end()
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
