<?php

namespace Beyerz\AWSQueueBundle\Tests\Fixtures\app;

use Liip\FunctionalTestBundle\LiipFunctionalTestBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
        (new Filesystem())->remove($this->getCacheDir());
    }

    public function registerBundles()
    {
        $bundles = [];
        if (in_array($this->getEnvironment(), ['test'], true)) {
            $bundles = array_merge(
                $bundles,
                [
                    new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
                    new \Symfony\Bundle\DebugBundle\DebugBundle(),
                    new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
                    new \Beyerz\AWSQueueBundle\BeyerzAWSQueueBundle(),
                    new LiipFunctionalTestBundle(),
                ]
            );
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}