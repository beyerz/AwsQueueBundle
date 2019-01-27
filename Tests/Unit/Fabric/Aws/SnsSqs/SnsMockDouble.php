<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 13:22
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Fabric\Aws\SnsSqs;


use Aws\Result;
use Aws\Sns\SnsClient;
use Doctrine\Common\Collections\ArrayCollection;

class SnsMockDouble extends SnsClient
{

    /**
     * @var ArrayCollection
     */
    public $messages;

    public function __construct(ArrayCollection $collector)
    {
        $this->messages = $collector;
    }

    public function publish($msg)
    {
        $msg = [
            'Body'          => json_encode($msg),
            'ReceiptHandle' => rand(1, 100),

        ];
        $this->messages->add($msg);
        $result = new Result(['MessageId' => 123]);

        return $result;
    }

    public function getTopicAttributes()
    {
        return true;
    }

    public function listSubscriptionsByTopic()
    {
        return new Result(
            [
                'Subscriptions' => [
                    [
                        'Protocol' => 'sqs',
                        'Endpoint' => 'test-arn',
                    ],
                ],
            ]
        );
    }

    public function subscribe(array $args = [])
    {
        return true;
    }
}