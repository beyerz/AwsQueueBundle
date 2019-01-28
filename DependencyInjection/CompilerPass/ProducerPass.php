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

        if (!$container->has('service_container')) {
            return;
        }
        $serviceDefinition = $container->getDefinition('beyerz_aws_queue.producer_service');
        $producers = $container->findTaggedServiceIds('beyerz_aws_queue.producer');

        foreach ($producers as $id => $tags) {
            //create a producer service
            $topic = $this->getTopic($id, $tags);
            $serviceId = "beyerz_aws_queue.producer_service.$topic";
            $container->setDefinition($serviceId, $this->createDefinition($serviceDefinition, $this->appendPrefix($topic, $container->getParameter('beyerz_aws_queue.prefix'))));
            $producerDefinition = $container->getDefinition($id);
            $producerDefinition->addMethodCall('setProducerService', [new Reference($serviceId)]);
        }
    }

    /**
     * Get channel name by defined tag or from service id
     * @param        $id
     * @param        $tags
     * @param string $prefix
     * @return mixed
     */
    private function getTopic(string $id, array $tags, string $prefix = '')
    {

        if (isset($tags[0]['channel'])) {
            @trigger_error('channel as the key for provider tag is deprecated since version v1.0 and will be removed in 2.0. Use topic instead.', E_USER_DEPRECATED);

            return $tags[0]['channel'];
        }

        if (isset($tags[0]['topic'])) {
            return $tags[0]['topic'];
        }

        return str_replace(".", "_", str_replace('beyerz_aws_queue.', '', $id));
    }

    private function appendPrefix($topic, $prefix = '', $glue = '_')
    {
        return empty($prefix) ? $topic : $prefix.$glue.$topic;
    }

    private function createDefinition(Definition $template, string $topic)
    {
        $definition = new Definition($template->getClass(), $template->getArguments());
        $definition->replaceArgument(1, $topic);

        return $definition;
    }
}