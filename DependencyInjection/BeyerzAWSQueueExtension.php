<?php

namespace Beyerz\AWSQueueBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $this->prepareProducerService($config, $container);
        $container->setParameter('beyerz_aws_queue.channel_prefix', $config[Configuration::KEY_CHANNEL_PREFIX]);
        $container->setParameter('beyerz_aws_queue.is_local', $config[Configuration::KEY_RUN_LOCAL]);
    }

    private function prepareAwsFabric($config, ContainerBuilder $container)
    {
        $awsFabricDefinition = $container->getDefinition('beyerz_aws_queue.fabric.aws');
        $awsFabricDefinition->addMethodCall('setAccount', [ $config[Configuration::KEY_AWS_ACCOUNT] ])
            ->addMethodCall('setRegion', [ $config[Configuration::KEY_AWS_REGION] ])
            ->setPublic(false);
    }

    private function prepareProducerService($config, ContainerBuilder $container)
    {
        //prepare the Producer service class
        $producerServiceDefinition = $container->getDefinition('beyerz_aws_queue.producer_service');
        $fabricReference = $config[Configuration::KEY_RUN_LOCAL] ? 'beyerz_aws_queue.fabric.local' : 'beyerz_aws_queue.fabric.aws';
        $this->prepareAwsFabric($config, $container);

        $producerServiceDefinition->replaceArgument(0, new Reference($fabricReference));
    }
}
