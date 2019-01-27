<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:01
 */

namespace Beyerz\AWSQueueBundle\DependencyInjection\CompilerPass;


use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ConsumerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {

        if (!$container->has('service_container')) {
            return;
        }
        $fabricReference = $container->getParameter('beyerz_aws_queue.is_local') ? 'beyerz_aws_queue.fabric.local' : 'beyerz_aws_queue.fabric.aws';

        // find all service IDs with the beyerz_aws_queue.consumer tag
        $consumers = $container->findTaggedServiceIds('beyerz_aws_queue.consumer');

        foreach ($consumers as $id => $tags) {
            foreach ($tags as $tag) {
                $channel = $this->getChannel($id, $tag);
                $consumerDefinition = $this->createConsumerServiceDefinition(
                    $container,
                    $fabricReference,
                    $this->appendChannelPrefix($channel, $container->getParameter('beyerz_aws_queue.prefix')),
                    $id
                );

                $container->setDefinition("beyerz_aws_queue.consumer_service.$channel", $consumerDefinition);
            }
        }
    }

    /**
     * Get channel name by defined tag or from service id
     * @param $id
     * @param $tags
     * @return mixed
     */
    private function getChannel($id, $tag)
    {
        if (isset($tag['channel'])) {
            return $tag['channel'];
        }

        return str_replace(".", "_", str_replace('beyerz_aws_queue.', '', $id));
    }

    private function appendChannelPrefix($channel, $prefix = '', $glue = '_')
    {
        return empty($prefix) ? $channel : $prefix.$glue.$channel;
    }

    private function createConsumerServiceDefinition(ContainerBuilder $container, string $fabric, string $channel, string $consumer)
    {
        $consumerDefinition = $container->getDefinition($consumer);
        $consumerSubscriberTags = $consumerDefinition->getTag('beyerz_aws_queue.subscriber');
        $topics = [];
        foreach ($consumerSubscriberTags as $tag) {
            $topics[] = $tag['producer'];
        }
        $definition = new Definition(ConsumerService::class, [new Reference($fabric), $channel, $topics]);
        $definition->addMethodCall('setConsumer', [$consumerDefinition]);
        $definition->addMethodCall('setContainer', [new Reference('service_container')]);
        $definition->addTag('beyerz_aws_queue.consumer_service');

        return $definition;
    }
}