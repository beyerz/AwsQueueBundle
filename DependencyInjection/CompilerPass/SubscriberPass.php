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
use Symfony\Component\DependencyInjection\Reference;

class SubscriberPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {

        if ( !$container->has('service_container') ) {
            return;
        }

        // find all service IDs with the beyerz_aws_queue.consumer tag
        $subscriber = $container->findTaggedServiceIds('beyerz_aws_queue.consumer_service.subscriber');
        foreach ($subscriber as $id => $tags) {
            foreach ($tags as $tag) {
                if ( isset($tag['producer']) ) {
                    $this->addSubscriber($container, $tag['producer'], $id);
                }
            }
        }
    }


    private function addSubscriber(ContainerBuilder $container, $producer, $consumer)
    {
        $channelKey = "beyerz_aws_queue.producer_service.$producer";
        if ( $container->hasDefinition($channelKey) ) {
            $definition = $container->getDefinition($channelKey);
            $definition->addMethodCall('addSubscriber', [ new Reference($consumer) ]);
        }
    }

}