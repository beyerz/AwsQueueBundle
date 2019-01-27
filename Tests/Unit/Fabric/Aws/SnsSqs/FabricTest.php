<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-26
 * Time: 17:03
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Fabric\Aws\SnsSqs;


use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs\Fabric;
use Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs\Topic;
use Beyerz\AWSQueueBundle\Service\ConsumerService;
use Beyerz\AWSQueueBundle\Service\ProducerService;
use Beyerz\AWSQueueBundle\Tests\Unit\Fabric\Consumer;
use Doctrine\Common\Collections\ArrayCollection;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class FabricTest extends WebTestCase
{

    /**
     * @dataProvider createTopicProvider
     * @param string    $region
     * @param string    $account
     * @param SnsClient $sns
     * @param SqsClient $sqs
     * @param string    $topic
     * @param array     $expected
     */
    public function testCreateTopic(string $region, string $account, SnsClient $sns, SqsClient $sqs, string $topic, array $expected)
    {
        $fabric = new Fabric($region, $account, $sns, $sqs);
        $actual = $fabric->createTopic($topic);
        $this->assertInstanceOf($expected['instance'], $actual);
        $this->assertEquals($expected['arn'], $actual->getArn());
    }

    /**
     * @dataProvider consumeProvider
     * @param string $region
     * @param string $account
     */
    public function testConsume(string $region, string $account)
    {
        $collector = new ArrayCollection();
        $snsMock = new SnsMockDouble($collector);
        $sqsMock = new SqsMockDouble($collector);
        $fabric = new Fabric($region, $account, $snsMock, $sqsMock);

        $producerService = new ProducerService($fabric, 'producer-topic');
        $producerService->publish('bar');
        $consumer = new Consumer();

        $consumerService = new ConsumerService($fabric, false, "consumer-channel", ['producer-topic']);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(1);

        $this->assertSame($consumer->messages->count(), 1);
        $this->assertSame($consumer->messages->get(0)['msg'], 'bar');
    }

    /**
     * @dataProvider consumeProvider
     * @Todo         : Our consumer runs until the number of requested messages has been fulfilled, how to test this?
     */
    public function testNoMessage(string $region, string $account)
    {
        $collector = new ArrayCollection();
        $snsMock = new SnsMockDouble($collector);
        $sqsMock = new SqsMockDouble($collector);
        $fabric = new Fabric($region, $account, $snsMock, $sqsMock);

        $consumer = new Consumer();
        $consumerService = new ConsumerService($fabric, false, "consumer-channel", ['producer-topic']);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(0);

        $this->assertSame($consumer->messages->count(), 0);
    }

    /**
     * @dataProvider consumeProvider
     * @param string $region
     * @param string $account
     */
    public function testMultipleMessages(string $region, string $account)
    {
        $collector = new ArrayCollection();
        $snsMock = new SnsMockDouble($collector);
        $sqsMock = new SqsMockDouble($collector);
        $fabric = new Fabric($region, $account, $snsMock, $sqsMock);

        $producerService = new ProducerService($fabric, 'producer-topic');
        $producerService->publish('bar1');
        $producerService->publish('bar2');
        $producerService->publish('bar3');
        $producerService->publish('bar4');
        $producerService->publish('bar5');

        $consumer = new Consumer();
        $consumerService = new ConsumerService($fabric, false, "consumer-channel", ['producer-topic']);
        $consumerService->setConsumer($consumer);
        $consumerService->consume(5);

        $this->assertSame($consumer->messages->count(), 5);
        $this->assertSame($consumer->messages->get(2)['msg'], 'bar3');
    }

    public function createTopicProvider()
    {
        $sns = $this->getMockBuilder(SnsClient::class)->disableOriginalConstructor()->getMock();
        $sqs = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();

        return [
            [
                'us-east-1',
                '123456789012',
                $sns,
                $sqs,
                'sample',
                [
                    'instance' => Topic::class,
                    'arn'      => 'arn:aws:sns:us-east-1:123456789012:sample',
                ],
            ],
            [
                'us-east-1',
                '123456789012',
                $sns,
                $sqs,
                'dev_sample',
                [
                    'instance' => Topic::class,
                    'arn'      => 'arn:aws:sns:us-east-1:123456789012:dev_sample',
                ],
            ],
            [
                'us-east-1',
                '123456789012',
                $sns,
                $sqs,
                'prod_sample',
                [
                    'instance' => Topic::class,
                    'arn'      => 'arn:aws:sns:us-east-1:123456789012:prod_sample',
                ],
            ],
        ];
    }

    public function consumeProvider()
    {
        return [
            [
                'us-east-1',
                '123456789012',
            ],
        ];
    }
}