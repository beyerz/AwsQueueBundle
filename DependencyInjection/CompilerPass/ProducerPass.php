<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:01
 */

namespace Beyerz\AWSQueueBundle\DependencyInjection\CompilerPass;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class ProducerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {

        if ( !$container->has('service_container') ) {
            return;
        }
        $producerServiceDefinition = $container->getDefinition('beyerz_aws_queue.producer_service');
        $producers = $container->findTaggedServiceIds('beyerz_aws_queue.producer');

        foreach ($producers as $id => $tags) {
            //create a producer service
            $channel = $this->getProducerChannel($id, $tags);
            //each producer is defined its own "channel" in the form of a producer service, consumers should be able to define the channel name in their config so as to subscribe
            $container->setDefinition(
                "beyerz_aws_queue.producer_service.$channel",
                $this->createProducerServiceDefinition($producerServiceDefinition, $this->appendChannelPrefix($channel, $container->getParameter('beyerz_aws_queue.channel_prefix')))
            );
            $producerDefinition = $container->getDefinition($id);
            $producerDefinition->addMethodCall('setProducerService', [new Reference("beyerz_aws_queue.producer_service.$channel")]);
        }
    }

    /**
     * Get channel name by defined tag or from service id
     * @param $id
     * @param $tags
     * @return mixed
     */
    private function getProducerChannel($id, $tags, $channelPrefix = '')
    {
        if ( isset($tags[0]['channel']) ) {
            return $tags[0]['channel'];
        }

        return str_replace(".", "_", str_replace('beyerz_aws_queue.', '', $id));
    }

    private function appendChannelPrefix($channel, $prefix = '', $glue = '_')
    {
        return empty($prefix) ? $channel : $prefix . $glue . $channel;
    }

    private function createProducerServiceDefinition(Definition $template, string $channel)
    {
        $definition = new Definition($template->getClass(), $template->getArguments());
        $definition->replaceArgument(1, $channel);

        return $definition;
    }
}