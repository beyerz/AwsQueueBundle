<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:01
 */

namespace Beyerz\AWSQueueBundle\DependencyInjection\CompilerPass;


use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Doctrine\Common\Collections\ArrayCollection;
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

        // find all service IDs with the beyerz_aws_queue.consumer tag
        $consumers = $container->findTaggedServiceIds('beyerz_aws_queue.consumer');
        $this->createConsumerListDefinition($container, $consumers);

        foreach ($consumers as $id => $tags) {
            foreach ($tags as $tag) {
                $channel = $this->getChannel($id, $tag);
                $definition = $this->createDefinition(
                    $container,
                    $this->appendPrefix($channel, $container->getParameter('beyerz_aws_queue.prefix')),
                    $id
                );
                $container->setDefinition("beyerz_aws_queue.consumer_service.$channel", $definition);
            }
        }
    }

    /**
     * Get channel name by defined tag or from service id
     * @param $id
     * @param $tag
     * @return mixed
     */
    private function getChannel(string $id, array $tag)
    {
        if (isset($tag['channel'])) {
            return $tag['channel'];
        }

        return str_replace(".", "_", str_replace('beyerz_aws_queue.', '', $id));
    }

    private function appendPrefix($name, $prefix = '', $glue = '_')
    {
        return empty($prefix) ? $name : $prefix.$glue.$name;
    }

    private function createDefinition(ContainerBuilder $container, string $channel, string $consumerKey)
    {
        $consumer = $container->getDefinition($consumerKey);
        $consumerSubscriberTags = $consumer->getTag('beyerz_aws_queue.subscriber');
        $topics = array_map(
            function ($tag) use ($container) {
                return $this->appendPrefix($tag['producer'], $container->getParameter('beyerz_aws_queue.prefix'));
            },
            $consumerSubscriberTags
        );

        return $this->createServiceDefinition($container, $consumer, $channel, $topics);
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition       $consumer
     * @param string           $channel
     * @param array            $topics
     * @return Definition
     */
    private function createServiceDefinition(ContainerBuilder $container, Definition $consumer, string $channel, array $topics)
    {
        $template = $container->getDefinition('beyerz_aws_queue.consumer_service');
        $definition = new Definition($template->getClass(), $template->getArguments());
        $definition->replaceArgument(2, $channel);
        $definition->replaceArgument(3, $topics);
        $definition->addMethodCall('setConsumer', [$consumer]);
        $definition->addMethodCall('setContainer', [new Reference('service_container')]);

        return $definition;
    }

    private function createConsumerListDefinition(ContainerBuilder $container, array $consumers = [])
    {
        $items = [];
        foreach ($consumers as $key => $tag) {
            $def = $container->getDefinition($key);
            $pattern = '/(%)([a-zA-Z0-9\._-]*)(%)/';
            $matches = [];
            $class = $def->getClass();
            if (preg_match($pattern, $class, $matches) > 0) {
                $class = $container->getParameter($matches[2]);
            }
            $items[] = [
                'service' => $key,
                'class'   => $class,
                'name'    => $tag[0]['channel'],
            ];
        }
        $consumersList = new Definition(ArrayCollection::class, [$items]);
        $container->setDefinition('beyerz_aws_queue.consumer.list', $consumersList);
    }
}