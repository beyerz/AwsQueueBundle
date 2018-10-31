<?php

namespace Beyerz\AWSQueueBundle\Tests;


use Beyerz\AWSQueueBundle\Tests\Fixtures\app\AppKernel;

class Kernel
{

    /**
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    private static $instance;

    public static function boot()
    {
        if ( null === static::$instance ) {
            static::$instance = new AppKernel('test', true);
            static::$instance->boot();
        }

        return static::$instance;
    }
}