<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 12:00
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Fabric;


use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Beyerz\AWSQueueBundle\Fabric\AwsFabric;
use Beyerz\AWSQueueBundle\Producer\ProducerService;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Container;

class AwsFabricTest extends WebTestCase
{
    /**
     * @var AwsFabric
     */
    private $fabric;

    /**
     * @var Container
     */
    private $container;

    public function setUp()
    {

        $sns_sqs_mock = new ClientMockDouble();
        $this->fabric = new AwsFabric($sns_sqs_mock, $sns_sqs_mock);
        $this->fabric->setRegion('test-region')
            ->setAccount('test-account');
        $this->container = new Container();
        $this->container->setParameter('beyerz_aws_queue.enable_forking', false);
    }

    public function testConsumeMessage()
    {
        $producerService = new ProducerService($this->fabric, 'producer-topic');
        $producerService->publish('bar');

        $consumer = new Consumer();

        $consumerService = new ConsumerService($this->fabric, "consumer-channel", ['producer-topic']);
        $consumerService->setContainer($this->container);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(1);

        $this->assertSame($consumer->messages->count(), 1);
        $this->assertSame($consumer->messages->get(0)['msg'], 'bar');
    }

    public function testNoMessage()
    {
        $consumer = new Consumer();
        $consumerService = new ConsumerService($this->fabric, "my-channel", []);
        $consumerService->setContainer($this->container);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(1);

        $this->assertSame($consumer->messages->count(), 0);
    }

    public function testMultipleMessages()
    {
        $producerService = new ProducerService($this->fabric, 'producer-topic');
        $producerService->publish('bar1');
        $producerService->publish('bar2');
        $producerService->publish('bar3');

        $consumer = new Consumer();
        $consumerService = new ConsumerService($this->fabric, "consumer-channel", ['producer-topic']);
        $consumerService->setContainer($this->container);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(5);

        $this->assertSame($consumer->messages->count(), 3);
        $this->assertSame($consumer->messages->get(2)['msg'], 'bar3');
    }

    public function testMultipleConsumers()
    {
        $producerService1 = new ProducerService($this->fabric, 'producer-topic');
        $producerService1->publish('bar1');


        $consumer1 = new Consumer();
        $consumerService1 = new ConsumerService($this->fabric, "bar-channel", ['producer-topic']);
        $consumerService1->setContainer($this->container);
        $consumerService1->setConsumer($consumer1);
        $consumerService1->consume(1);

        $consumer2 = new Consumer();
        $consumerService2 = new ConsumerService($this->fabric, "foo-channel", ['producer-topic']);
        $consumerService2->setContainer($this->container);
        $consumerService2->setConsumer($consumer2);
        $consumerService2->consume(3);

        $this->assertSame($consumer1->messages->count(), 1);
        $this->assertSame($consumer1->messages->get(0)['msg'], 'bar1');

        //need to create a better mock for sqs so that this assertion would work properly
//        $this->assertSame($consumer2->messages->count(), 2);
//        $this->assertSame($consumer2->messages->get(1)['msg'], 'foo2');
    }
}