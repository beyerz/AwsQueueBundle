<?php

namespace Beyerz\AWSQueueBundle\Tests\Unit\Producer;

use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Beyerz\AWSQueueBundle\Interfaces\ConsumerInterface;
use Beyerz\AWSQueueBundle\Fabric\LocalFabric;
use Beyerz\AWSQueueBundle\Producer\ProducerService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PHPUnit\Framework\TestCase;

class LocalConsumer implements ConsumerInterface {
    public $messages;

    public function __construct() {
        $this->messages = new ArrayCollection();
    }

    public function consume($message) {
        $this->messages->add($message);
    }
}

class ConsumerTest extends TestCase {
    /**
     * @var LocalFabric
     */
    private $fabric;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp() {
        $this->fabric = new LocalFabric();
        $this->container = new Container();
        $this->container->setParameter("beyerz_aws_queue.enable_forking", false);
    }

    public function testConsumeMessage() {
        $producerService = new ProducerService($this->fabric, 'my-channel');
        $producerService->publish('bar');

        $consumer = new LocalConsumer();
        $consumerService = new ConsumerService($this->fabric, "my-channel");
        $consumerService->setContainer($this->container);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(1);

        $this->assertSame($consumer->messages->count(), 1);
        $this->assertSame($consumer->messages->get(0), 'bar');
    }

    public function testNoMessage()
    {
        $consumer = new LocalConsumer();
        $consumerService = new ConsumerService($this->fabric, "my-channel");
        $consumerService->setContainer($this->container);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(1);

        $this->assertSame($consumer->messages->count(), 0);
    }

    public function testMultipleMessages()
    {
        $producerService = new ProducerService($this->fabric, 'my-channel');
        $producerService->publish('bar1');
        $producerService->publish('bar2');
        $producerService->publish('bar3');

        $consumer = new LocalConsumer();
        $consumerService = new ConsumerService($this->fabric, "my-channel");
        $consumerService->setContainer($this->container);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(5);

        $this->assertSame($consumer->messages->count(), 3);
        $this->assertSame($consumer->messages->get(2), 'bar3');
    }

    public function testMultipleConsumers()
    {
        $producerService1 = new ProducerService($this->fabric, 'bar-channel');
        $producerService1->publish('bar1');

        $producerService2 = new ProducerService($this->fabric, 'foo-channel');
        $producerService2->publish('foo1');
        $producerService2->publish('foo2');

        $consumer1 = new LocalConsumer();
        $consumerService1 = new ConsumerService($this->fabric, "bar-channel");
        $consumerService1->setContainer($this->container);
        $consumerService1->setConsumer($consumer1);
        $consumerService1->consume(1);

        $consumer2 = new LocalConsumer();
        $consumerService2 = new ConsumerService($this->fabric, "foo-channel");
        $consumerService2->setContainer($this->container);
        $consumerService2->setConsumer($consumer2);
        $consumerService2->consume(3);

        $this->assertSame($consumer1->messages->count(), 1);
        $this->assertSame($consumer1->messages->get(0), 'bar1');

        $this->assertSame($consumer2->messages->count(), 2);
        $this->assertSame($consumer2->messages->get(1), 'foo2');
    }
}