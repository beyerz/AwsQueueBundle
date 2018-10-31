<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 29/10/2018
 * Time: 18:40
 */

namespace Beyerz\AWSQueueBundle\Tests;


use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setUp()
    {
        $this->container = Kernel::boot()->getContainer();
    }
}