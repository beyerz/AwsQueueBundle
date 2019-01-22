<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 13:22
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Fabric;


use Aws\Result;
use Doctrine\Common\Collections\ArrayCollection;

class ClientMockDouble
{

    /**
     * @var ArrayCollection
     */
    public $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function publish($msg)
    {
        $msg = [
            'Body'          => json_encode($msg),
            'ReceiptHandle' => rand(1,100),

        ];
        $this->messages->add($msg);
        $result = new Result(['MessageId' => 123]);

        return $result;
    }

    public function receiveMessage($options)
    {
        $result = new Result(['Messages' => $this->messages->toArray()]);
        $this->messages->clear();

        return $result;
    }

    public function deleteMessage()
    {
        return true;
    }

    public function getTopicAttributes()
    {
        return true;
    }

    public function getQueueAttributes()
    {
        return new Result(
            [
                'Attributes' => [
                    'Policy' => json_encode(
                        [
                            'Statement' => [
                                [
                                    'Sid'       => 'Allow-SNS-SendMessage',
                                    'Effect'    => 'Allow',
                                    'Principal' => '*',
                                    'Action'    => ['SQS:SendMessage'],
                                    'Resource'  => 'test-arn',
                                    'Condition' => [
                                        'ArnEquals' => [
                                            'aws:SourceArn' => 'arn:aws:sns:test-region:test-account:producer-topic',
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ),
                ],
            ]
        );
    }

    public function getQueueArn()
    {
        return 'test-arn';
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
}