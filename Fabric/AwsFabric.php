<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:41
 */

namespace Beyerz\AWSQueueBundle\Fabric;


use Aws\Sns\Exception\SnsException;
use Aws\Sqs\Exception\SqsException;
use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class AwsFabric extends AbstractFabric
{
    use ContainerAwareTrait;

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

    public function publish($message, $channel, ArrayCollection $subscribers)
    {
        $this->setup($channel, $subscribers);
        $topicArn = $this->buildTopicArn($channel);

        return $this->getNotificationService()->publish(
            [
                "TopicArn" => $topicArn,
                "Message"  => $message,
            ]
        );
    }

    public function consume($toProcess = true, $channel, ConsumerService $consumer)
    {
        $this->setup($channel, new ArrayCollection([ $consumer ]));
        $queueUrl = $this->buildQueueUrl($consumer->getChannel());
        $processed = 0;
        while ($processed<$toProcess || $toProcess === true) {
            $processed ++;

            $pid = pcntl_fork();
            if ( $pid == - 1 ) {
                //error
                var_dump("error");
            } elseif ( $pid ) {
                pcntl_waitpid($pid, $status);
            } else {
                //get message
                $sqsMessage = $this->getQueueService()->receiveMessage(
                    [
                        'AttributeNames'        => [ 'SentTimestamp' ],
                        'MaxNumberOfMessages'   => 1,
                        'MessageAttributeNames' => [ 'All' ],
                        'QueueUrl'              => $queueUrl,
                        'WaitTimeSeconds'       => 20, // for long polling
                    ]
                );
                if ( count($sqsMessage->get('Messages'))>0 ) {
                    foreach ($sqsMessage->get('Messages') as $message) {
                        $body = json_decode($message['Body'], true);
                        $msg = unserialize($body['Message']);
                        $data = [
                            'msg'     => $msg,
                            'channel' => $channel,
                        ];
                        $result = call_user_func([ $this->container->get($consumer->getConsumer()), 'consume' ], $data);

                        if ( $result ) {
                            //remove message
                            $this->getQueueService()->deleteMessage(
                                [
                                    'QueueUrl'      => $this->queueUrl,
                                    'ReceiptHandle' => $message['ReceiptHandle'],
                                ]
                            );
                        } else {
                        }
                    }
                }
                exit(0);
            }
        }
    }

    /**
     * Fabric should ensure that all notification channels and respective queues exist and subscribers are defined
     * @param string          $channel
     * @param ArrayCollection $subscribers
     * @return mixed
     */
    public function setup(string $channel, ArrayCollection $subscribers)
    {
        $this->setupNotificationServices($channel);
        $this->setupQueueServices($channel, $subscribers);

        return;
    }

    /**
     * @param string $channel
     * @return mixed
     */
    public function setupNotificationServices(string $channel)
    {
        //ensure notification topic exists
        $topicArn = $this->buildTopicArn($channel);
        try {
            $topicAttributes = $this->getNotificationService()->getTopicAttributes(
                [
                    "TopicArn" => $topicArn,
                ]
            );
        } catch (SnsException $e) {
            if ( $e->getStatusCode() !== 404 ) {
                throw $e;
            }
            //create topic
            $topic = $this->getNotificationService()->createTopic(
                [
                    'Name' => $channel,
                ]
            );
            //get the attributes
            $topicAttributes = $this->getNotificationService()->getTopicAttributes(
                [
                    "TopicArn" => $topicArn,
                ]
            );
        }

        return $topicAttributes;
    }

    /**
     * @param string                            $channel
     * @param ArrayCollection|ConsumerService[] $subscribers
     */
    public function setupQueueServices(string $channel, ArrayCollection $subscribers)
    {
        $topicArn = $this->buildTopicArn($channel);
        foreach ($subscribers as $subscriber) {
            $subscription = null;
            $queueUrl = $this->buildQueueUrl($subscriber->getChannel());
            //queue exists create if not
            try {
                $this->getQueueService()->getQueueAttributes(
                    [
                        'QueueUrl' => $queueUrl,
                    ]
                );
            } catch (SqsException $e) {
                if ( !$e->getStatusCode() == 400 ) {
                    throw $e;
                }
                $this->getQueueService()->createQueue(
                    [
                        'QueueName'  => $subscriber->getChannel(),
                        'Attributes' => [
                            'ReceiveMessageWaitTimeSeconds' => 20,
                        ],
                    ]
                );
            }

            //subscription exists create if not
            if ( false === $this->isConsumerSubscribedToProducer($queueUrl, $topicArn) ) {
                $this->subscribeConsumerToProducer($queueUrl, $topicArn);
            }

            //policy exists create if not
            if ( false === $this->isTopicPermitted($queueUrl, $topicArn, $subscriber->getChannel()) ) {
                $this->addQueuePermission($queueUrl, $topicArn, $subscriber->getChannel());
            }
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

        if ( count($subscriptions->get('Subscriptions')>0) ) {
            foreach ($subscriptions->get('Subscriptions') as $subscription) {
                if ( $subscription['Protocol'] == 'sqs' && $subscription['Endpoint'] == $this->getQueueService()->getQueueArn($queueUrl) ) {
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
                "AttributeNames" => [ 'Policy' ],
                "QueueUrl"       => $queueUrl,
            ]
        );
        if ( !is_null($permissions->get("Attributes")) ) {
            $policy = json_decode($permissions->get("Attributes")['Policy'], true);
            $expectedPolicy = $this->buildPolicy($queueUrl, $topicArn, $channelName);
            $expectedStatement = $expectedPolicy['Statement'];
            foreach ($policy['Statement'] as $statement) {
                if ( !is_array($statement['Action']) ) {
                    $statement['Action'] = [ $statement['Action'] ];
                }
                if ( $statement == $expectedStatement ) {
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
            "Id"        => 'sns.' . $channelName . '.queue',
            "Statement" => [
                "Sid"       => "Allow-SNS-SendMessage",
                "Effect"    => "Allow",
                "Principal" => "*",
                "Action"    => [ "SQS:SendMessage" ],
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