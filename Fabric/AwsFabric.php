<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:41
 */

namespace Beyerz\AWSQueueBundle\Fabric;


use Aws\Result;
use Aws\Sns\Exception\SnsException;
use Aws\Sqs\Exception\SqsException;
use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Doctrine\Common\Collections\ArrayCollection;

class AwsFabric extends AbstractFabric
{

    /**
     * AWS Account ID
     * @var string
     */
    private $account;

    /**
     * AWS Region
     * @var string
     */
    private $region;

    /**
     * @param string $account
     * @return $this
     */
    public function setAccount(string $account): AwsFabric
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @param string $region
     * @return $this
     */
    public function setRegion(string $region): AwsFabric
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @param string          $topic
     * @param                 $channel
     * @param string          $subscribers
     * @return string
     */
    public function publish(string $message, string $topic): string
    {
//        $this->setup($channel, $subscribers);
        $topicArn = $this->buildTopicArn($topic);

        $msg = [
            'data'    => $message,
            'channel' => $topic,
        ];

        /** @var Result $response */
        $response = $this->getNotificationService()->publish(
            [
                "TopicArn" => $topicArn,
                "Message"  => serialize($msg),
            ]
        );

        return $response->get('MessageId');
    }

    /**
     * @param ConsumerService $service
     * @param int             $messageCount
     * @return int
     */
    public function consume(ConsumerService $service, int $messageCount): int
    {
        $queueUrl = $this->buildQueueUrl($service->getChannel());
        //get message
        /** @var Result $sqsMessage */
        $sqsMessage = $this->getQueueService()->receiveMessage(
            [
                'AttributeNames'        => ['SentTimestamp'],
                'MaxNumberOfMessages'   => ($messageCount >= 10 || $messageCount === -1) ? 10 : $messageCount,
                'MessageAttributeNames' => ['All'],
                'QueueUrl'              => $queueUrl,
                'WaitTimeSeconds'       => 20, // for long polling
                'VisibilityTimeout'     => 600, // for long running processes
            ]
        );
        $consumed = 0;
        if (count($sqsMessage->get('Messages')) > 0) {
            foreach ($sqsMessage->get('Messages') as $message) {
                $body = json_decode($message['Body'], true);
                $msg = unserialize($body['Message']);
                if (is_array($msg)) {
                    $data = $msg['data'];
                    $channel = $msg['channel'];

                } else {
                    //BC For older messages that did not contain channel data
                    $data = $msg;
                    $channel = null;
                }

                $consumable = [
                    'msg'     => $data,
                    'channel' => $channel,
                ];
                $result = $service->getConsumer()->consume($consumable);

                if ($result) {
                    //remove message
                    $this->getQueueService()->deleteMessage(
                        [
                            'QueueUrl'      => $queueUrl,
                            'ReceiptHandle' => $message['ReceiptHandle'],
                        ]
                    );
                }
                $consumed++;
            }
        }

        return $consumed;
    }

    /**
     * Fabric should ensure that all notification channels and respective queues exist and subscribers are defined
     * @param string $topic
     * @param string $channel
     * @return mixed
     */
    public function setup(string $topic, string $channel = null)
    {
        //Topics must be created before the channel as the channel must subscribe to an existing topic
        $topicArn = $this->setupNotificationServices($channel);
        if (!is_null($channel)) {
            //Create channel and subscribe to existing topic
            $this->setupQueueServices($topic, $topicArn);
        }

        return;
    }

    /**
     * @param string $topic
     * @return mixed
     */
    private function setupNotificationServices(string $topic)
    {
        //ensure notification topic exists
        $topicArn = $this->buildTopicArn($topic);
        try {
            $this->getNotificationService()->getTopicAttributes(
                [
                    "TopicArn" => $topicArn,
                ]
            );
        } catch (SnsException $e) {
            if ($e->getStatusCode() !== 404) {
                throw $e;
            }
            //create topic
            $this->getNotificationService()->createTopic(
                [
                    'Name' => $topic,
                ]
            );
            //get the attributes
            $this->getNotificationService()->getTopicAttributes(
                [
                    "TopicArn" => $topicArn,
                ]
            );
        }

        return $topicArn;
    }

    /**
     * @param string $channel
     * @param string $topicArn
     */
    private function setupQueueServices(string $channel, string $topicArn)
    {
        $queueUrl = $this->buildQueueUrl($channel);
        try {
            $this->getQueueService()->getQueueAttributes(
                [
                    'QueueUrl' => $queueUrl,
                ]
            );
        } catch (SqsException $e) {
            if (!$e->getStatusCode() == 400) {
                throw $e;
            }
            $this->getQueueService()->createQueue(
                [
                    'QueueName'  => $channel,
                    'Attributes' => [
                        'ReceiveMessageWaitTimeSeconds' => 20,
                    ],
                ]
            );
        }

        //subscription exists create if not
        if (false === $this->isConsumerSubscribedToProducer($queueUrl, $topicArn)) {
            $this->subscribeConsumerToProducer($queueUrl, $topicArn);
        }

        //policy exists create if not
        if (false === $this->isTopicPermitted($queueUrl, $topicArn, $channel)) {
            $this->addQueuePermission($queueUrl, $topicArn, $channel);
        }
    }

    protected function buildTopicArn($name)
    {
        return sprintf("arn:aws:sns:%s:%s:%s", $this->region, $this->account, $name);
    }

    protected function buildQueueUrl($name)
    {
        return sprintf("https://sqs.%s.amazonaws.com/%s/%s", $this->region, $this->account, $name);
    }


    private function isConsumerSubscribedToProducer($queueUrl, $topicArn)
    {
        $subscriptions = $this->getNotificationService()->listSubscriptionsByTopic(
            [
                "TopicArn" => $topicArn,
            ]
        );

        if (count($subscriptions->get('Subscriptions') > 0)) {
            foreach ($subscriptions->get('Subscriptions') as $subscription) {
                if ($subscription['Protocol'] == 'sqs' && $subscription['Endpoint'] == $this->getQueueService()->getQueueArn($queueUrl)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isTopicPermitted($queueUrl, $topicArn, $channelName)
    {

        $permissions = $this->getQueueService()->getQueueAttributes(
            [
                "AttributeNames" => ['Policy'],
                "QueueUrl"       => $queueUrl,
            ]
        );
        if (!is_null($permissions->get("Attributes"))) {
            $policy = json_decode($permissions->get("Attributes")['Policy'], true);
            $expectedPolicy = $this->buildPolicy($queueUrl, $topicArn, $channelName);
            $expectedStatement = $expectedPolicy['Statement'];
            foreach ($policy['Statement'] as $statement) {
                if (!is_array($statement['Action'])) {
                    $statement['Action'] = [$statement['Action']];
                }
                if ($statement == $expectedStatement) {
                    return true;
                }
            }
        }

        return false;
    }

    private function subscribeConsumerToProducer($queueUrl, $topicArn)
    {
        return $this->getNotificationService()->subscribe(
            [
                'Endpoint' => $this->getQueueService()->getQueueArn($queueUrl),
                'Protocol' => 'sqs',
                'TopicArn' => $topicArn,
            ]
        );
    }

    private function addQueuePermission($queueUrl, $topicArn, $channelName)
    {
        $policy = $this->buildPolicy($queueUrl, $topicArn, $channelName);

        return $this->getQueueService()->setQueueAttributes(
            [
                "QueueUrl"   => $queueUrl,
                "Attributes" => [
                    'Policy' => json_encode($policy),
                ],
            ]
        );
    }

    private function buildPolicy($queueUrl, $topicArn, $channelName)
    {
        return [
            "Version"   => "2012-10-17",
            "Id"        => 'sns.'.$channelName.'.queue',
            "Statement" => [
                "Sid"       => "Allow-SNS-SendMessage",
                "Effect"    => "Allow",
                "Principal" => "*",
                "Action"    => ["SQS:SendMessage"],
                "Resource"  => $this->getQueueService()->getQueueArn($queueUrl),
                "Condition" => [
                    "ArnEquals" => [
                        "aws:SourceArn" => $topicArn,
                    ],
                ],
            ],
        ];

    }
}