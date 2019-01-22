<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 14:58
 */

namespace Beyerz\AWSQueueBundle\Tests\Functional;


use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Beyerz\AWSQueueBundle\Interfaces\ProducerInterface;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class ProducerTest extends WebTestCase
{

    public function testLoadConsumerAsService()
    {
        $producer = $this->getContainer()->get('beyerz_aws_queue.test.producer.local');
        $this->assertInstanceOf(ProducerInterface::class, $producer);
    }
}