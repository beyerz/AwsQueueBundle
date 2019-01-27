<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-26
 * Time: 17:26
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Service;


use Aws\Result;
use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs\Fabric;
use Beyerz\AWSQueueBundle\Service\ProducerService;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class ProducerServiceTest extends WebTestCase
{

    public function testPublish()
    {
        $fabric = $this->createFabric();
        $service = new ProducerService($fabric, 'sample');
        $actual = $service->publish('sample message');
        $this->assertTrue($actual);
    }

    public function createFabric()
    {
        $sns = $this->getMockBuilder(SnsClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['publish', 'getTopicAttributes'])
            ->getMock();
        $sns->expects($this->any())
            ->method('publish')
            ->willReturn(new Result(['MessageId' => 123]));
        $sns->expects($this->any())
            ->method('getTopicAttributes')
            ->willReturn(new Result(['MessageId' => 'TopicArn']));
        $sqs = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();

        return new Fabric('us-east-1', '123456789012', $sns, $sqs);
    }
}