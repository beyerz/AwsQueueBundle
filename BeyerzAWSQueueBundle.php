<?php

namespace Beyerz\AWSQueueBundle;

use Beyerz\AWSQueueBundle\DependencyInjection\CompilerPass\ConsumerPass;
use Beyerz\AWSQueueBundle\DependencyInjection\CompilerPass\ProducerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BeyerzAWSQueueBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ProducerPass());
        $container->addCompilerPass(new ConsumerPass());
    }
}
