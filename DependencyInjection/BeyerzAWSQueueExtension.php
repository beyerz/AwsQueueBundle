<?php

namespace Beyerz\AWSQueueBundle\DependencyInjection;

use Beyerz\AWSQueueBundle\Service\ConsumerService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class BeyerzAWSQueueExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $this->prepareProducerDefinition($config, $container);
        $this->prepareConsumerDefinition($config, $container);
        $this->exposeParameters($config, $container);

    }

    /**
     * @param                  $config
     * @param ContainerBuilder $container
     */
    private function prepareProducerDefinition($config, ContainerBuilder $container)
    {
        $service = $container->getDefinition('beyerz_aws_queue.producer_service');
        $fabricRef = $config[Configuration::KEY_RUN_LOCAL] ? 'beyerz_aws_queue.fabric.local' : 'beyerz_aws_queue.fabric.aws.sns_sqs';
        $service->replaceArgument(0, new Reference($fabricRef));
    }

    /**
     * @param                  $config
     * @param ContainerBuilder $container
     */
    private function prepareConsumerDefinition($config, ContainerBuilder $container)
    {
        $fabric = $config[Configuration::KEY_RUN_LOCAL] ? 'beyerz_aws_queue.fabric.local' : 'beyerz_aws_queue.fabric.aws.sns_sqs';
        $service = new Definition(ConsumerService::class, [new Reference($fabric), $config[Configuration::KEY_ENABLE_FORKING], 'default_channel', ['default_topic']]);
        $service->setPublic(false);
        $container->setDefinition('beyerz_aws_queue.consumer_service', $service);
    }

    /**
     * @param                  $config
     * @param ContainerBuilder $container
     */
    private function exposeParameters($config, ContainerBuilder $container)
    {
        $prefix = $config[Configuration::KEY_PREFIX]??$config[Configuration::KEY_CHANNEL_PREFIX];
        $container->setParameter('beyerz_aws_queue.prefix', $prefix);
        $container->setParameter('beyerz_aws_queue.is_local', $config[Configuration::KEY_RUN_LOCAL]);
        $container->setParameter('beyerz_aws_queue.enable_forking', $config[Configuration::KEY_ENABLE_FORKING]);
        $container->setParameter('beyerz_aws_queue.aws.region', $config[Configuration::KEY_AWS_REGION]);
        $container->setParameter('beyerz_aws_queue.aws.account', $config[Configuration::KEY_AWS_ACCOUNT]);
    }
}
