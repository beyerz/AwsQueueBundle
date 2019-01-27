<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 14:58
 */

namespace Beyerz\AWSQueueBundle\Tests\Functional;


use Beyerz\AWSQueueBundle\Service\ConsumerService;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class ConsumerTest extends WebTestCase
{

    public function testLoadConsumerAsService()
    {
        $consumer = $this->getContainer()->get('beyerz_aws_queue.consumer_service.beyerz_test_channel');
        $this->assertInstanceOf(ConsumerService::class, $consumer);
    }
}