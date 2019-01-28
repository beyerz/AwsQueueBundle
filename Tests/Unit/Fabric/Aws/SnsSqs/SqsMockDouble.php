<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 13:22
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Fabric\Aws\SnsSqs;


use Aws\Result;
use Aws\Sqs\SqsClient;
use Doctrine\Common\Collections\ArrayCollection;

class SqsMockDouble extends SqsClient
{

    /**
     * @var ArrayCollection
     */
    public $messages;

    public function __construct(ArrayCollection $collector)
    {
        $this->messages = $collector;
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

    public function setQueueAttributes(array $args = [])
    {
        return true;
    }

}