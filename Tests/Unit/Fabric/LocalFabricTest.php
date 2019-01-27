<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 12:00
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Fabric;


use Beyerz\AWSQueueBundle\Fabric\Local\Fabric;
use Beyerz\AWSQueueBundle\Service\ConsumerService;
use Beyerz\AWSQueueBundle\Service\ProducerService;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class LocalFabricTest extends WebTestCase
{
    /**
     * @var Fabric
     */
    private $fabric;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {
        $this->fabric = new Fabric();
        $this->container = new Container();
        $this->container->setParameter('beyerz_aws_queue.enable_forking', false);
    }

    public function testConsumeMessage()
    {
        $producerService = new ProducerService($this->fabric, 'producer-topic');
        $producerService->publish('bar');

        $consumer = new Consumer();

        $consumerService = new ConsumerService($this->fabric, false, "consumer-channel", ['producer-topic']);
        $consumerService->setContainer($this->container);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(1);

        $this->assertSame($consumer->messages->count(), 1);
        $this->assertSame($consumer->messages->get(0), 'bar');
    }

    public function testNoMessage()
    {
        $consumer = new Consumer();
        $consumerService = new ConsumerService($this->fabric, false, "my-channel", []);
        $consumerService->setContainer($this->container);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(0);

        $this->assertSame($consumer->messages->count(), 0);
    }

    public function testMultipleMessages()
    {
        $producerService = new ProducerService($this->fabric, 'producer-topic');
        $producerService->publish('bar1');
        $producerService->publish('bar2');
        $producerService->publish('bar3');
        $producerService->publish('bar4');
        $producerService->publish('bar5');

        $consumer = new Consumer();
        $consumerService = new ConsumerService($this->fabric, false, "consumer-channel", ['producer-topic']);
        $consumerService->setContainer($this->container);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(5);

        $this->assertSame($consumer->messages->count(), 5);
        $this->assertSame($consumer->messages->get(2), 'bar3');
    }

    public function testMultipleConsumers()
    {
        $producerService1 = new ProducerService($this->fabric, 'bar-topic');
        $producerService1->publish('bar1');

        $producerService2 = new ProducerService($this->fabric, 'foo-topic');
        $producerService2->publish('foo1');
        $producerService2->publish('foo2');

        $consumer1 = new Consumer();
        $consumerService1 = new ConsumerService($this->fabric, false, "bar-channel", ['bar-topic']);
        $consumerService1->setContainer($this->container);
        $consumerService1->setConsumer($consumer1);
        $consumerService1->consume(1);

        $consumer2 = new Consumer();
        $consumerService2 = new ConsumerService($this->fabric, false, "foo-channel", ['foo-topic']);
        $consumerService2->setContainer($this->container);
        $consumerService2->setConsumer($consumer2);
        $consumerService2->consume(2);

        $this->assertSame($consumer1->messages->count(), 1);
        $this->assertSame($consumer1->messages->get(0), 'bar1');

        $this->assertSame($consumer2->messages->count(), 2);
        $this->assertSame($consumer2->messages->get(1), 'foo2');
    }
}