<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-24
 * Time: 18:41
 */

namespace Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs;


use Aws\Result;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;
use Beyerz\AWSQueueBundle\Interfaces\DestinationInterface;
use Beyerz\AWSQueueBundle\Interfaces\FabricInterface;
use Beyerz\AWSQueueBundle\Service\ConsumerService;

class Fabric implements FabricInterface
{

    /**
     * @var string
     */
    private $region;

    /**
     * @var string
     */
    private $account;

    /**
     * @var SnsClient
     */
    private $sns;

    /**
     * @var SqsClient
     */
    private $sqs;

    /**
     * Fabric constructor.
     * @param string    $region
     * @param string    $account
     * @param SnsClient $sns
     * @param SqsClient $sqs
     */
    public function __construct(string $region, string $account, SnsClient $sns, SqsClient $sqs)
    {
        $this->region = $region;
        $this->account = $account;
        $this->sns = $sns;
        $this->sqs = $sqs;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @return string
     */
    public function getAccount(): string
    {
        return $this->account;
    }

    /**
     * @param DestinationInterface|Topic $destination
     * @param string                     $message
     * @return bool
     */
    public function publish(DestinationInterface $destination, string $message)
    {
        $msg = [
            'data'  => $message,
            'topic' => $destination->getName(),
        ];

        /** @var Result $result */
        $result = $this->sns->publish(
            [
                "TopicArn" => $destination->getArn(),
                "Message"  => serialize($msg),
            ]
        );

        return !empty($result->get('MessageId'));
    }

    /**
     * @param ConsumerService $service
     * @param int             $messageCount
     * @return void
     */
    public function consume(ConsumerService $service, int $messageCount = -1)
    {
        $consumed = 0;
        while ($consumed < $messageCount || $messageCount === -1) {
            $limit = ($messageCount - $consumed > 10 || $messageCount === -1) ? 10 : $messageCount;
            $messages = $this->getQueuedMessages($service, $limit);
            if (is_null($messages)) {
                continue;
            } else {
                foreach ($messages as $message) {
                    $body = json_decode($message['Body'], true);
                    $msg = unserialize($body['Message']);
                    $consumable = [
                        'msg' => $msg['data'],
                        'topic'   => $msg['topic'],
                    ];
                    $result = $service->getConsumer()->consume($consumable);

                    if ($result) {
                        //remove message
                        $this->sqs->deleteMessage(
                            [
                                'QueueUrl'      => $service->getQueue()->getDsn(),
                                'ReceiptHandle' => $message['ReceiptHandle'],
                            ]
                        );
                    }
                    $consumed++;
                }
            }
            sleep(1);
        }
    }

    /**
     * @param ConsumerService $service
     * @param int             $limit
     * @return array
     */
    private function getQueuedMessages(ConsumerService $service, $limit = 10)
    {
        $result = $this->sqs->receiveMessage(
            [
                'AttributeNames'        => ['SentTimestamp'],
                'MaxNumberOfMessages'   => ($limit >= 10 || $limit === -1) ? 10 : $limit,
                'MessageAttributeNames' => ['All'],
                'QueueUrl'              => $service->getQueue()->getDsn(),
                'WaitTimeSeconds'       => 20, // for long polling
                'VisibilityTimeout'     => 600, // for long running processes
            ]
        );

        return $result->get('Messages');
    }

    /**
     * @param string      $queue
     * @param string|null $topic
     * @return DestinationInterface|Queue
     */
    public function createQueue(string $queue, string $topic = null): DestinationInterface
    {
        $queue = new Queue($queue, $this->region, $this->account);
        $topic = $this->createTopic($topic);
        try {
            $this->sqs->getQueueAttributes(
                [
                    'QueueUrl' => $queue->getDsn(),
                ]
            );
        } catch (SqsException $e) {
            if ($e->getStatusCode() !== 400 || $e->getAwsErrorCode() !== 'AWS.SimpleQueueService.NonExistentQueue') {
                throw $e;
            }

            $this->sqs->createQueue(
                [
                    'QueueName'  => $queue->getName(),
                    'Attributes' => [
                        'ReceiveMessageWaitTimeSeconds' => 20,
                    ],
                ]
            );
        }

        $this->subscribeToTopic($queue, $topic);

        return $queue;
    }

    /**
     * @param Queue $queue
     * @param Topic $topic
     *
     * @return void
     */
    private function subscribeToTopic(Queue $queue, Topic $topic)
    {
        //subscription exists create if not
        $isSubscribed = $this->isSubscribedToTopic($queue, $topic);
        if (false === $isSubscribed) {
            $this->sns->subscribe(
                [
                    'Endpoint' => $this->sqs->getQueueArn($queue->getDsn()),
                    'Protocol' => 'sqs',
                    'TopicArn' => $topic->getArn(),
                ]
            );
        }

        $this->permitTopic($queue, $topic);
    }

    /**
     * @param Queue $queue
     * @param Topic $topic
     *
     * @return bool
     */
    private function isSubscribedToTopic(Queue $queue, Topic $topic)
    {
        $subscriptions = $this->sns->listSubscriptionsByTopic(
            [
                "TopicArn" => $topic->getArn(),
            ]
        );
        if (count($subscriptions->get('Subscriptions')) > 0) {
            foreach ($subscriptions->get('Subscriptions') as $subscription) {
                if ($subscription['Protocol'] == 'sqs' && $subscription['Endpoint'] == $this->sqs->getQueueArn($queue->getDsn())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param Queue $queue
     * @param Topic $topic
     *
     * @return void
     */
    private function permitTopic(Queue $queue, Topic $topic)
    {
        if (false === $this->isTopicPermitted($queue, $topic)) {
            $policy = $this->generateQueuePolicy($queue, $topic);

            $this->sqs->setQueueAttributes(
                [
                    "QueueUrl"   => $queue->getDsn(),
                    "Attributes" => [
                        'Policy' => json_encode($policy),
                    ],
                ]
            );
        }
    }

    /**
     * @param Queue $queue
     * @param Topic $topic
     * @return array
     */
    private function generateQueuePolicy(Queue $queue, Topic $topic)
    {
        $permissions = $this->sqs->getQueueAttributes(
            [
                "AttributeNames" => ['Policy'],
                "QueueUrl"       => $queue->getDsn(),
            ]
        );
        $policy = json_decode($permissions->get('Attributes')['Policy'], true);
        $statements = $policy['Statement'];
        $append = true;
        if ($statements !== null) {
            foreach ($statements as $statement) {
                $append = ($statement['Condition']['ArnEquals']['aws:SourceArn'] === $topic->getArn()) ? false : $append;
            }
        }
        $statements[] = $this->generatePolicyStatement($queue, $topic);

        return [
            "Version"   => "2012-10-17",
            "Id"        => 'sns.'.$queue->getName().'.queue',
            "Statement" => $statements,
        ];
    }

    private function generatePolicyStatement(Queue $queue, Topic $topic)
    {
        return [
            "Sid"       => "Allow-SNS-SendMessage",
            "Effect"    => "Allow",
            "Principal" => "*",
            "Action"    => ["SQS:SendMessage"],
            "Resource"  => $this->sqs->getQueueArn($queue->getDsn()),
            "Condition" => [
                "ArnEquals" => [
                    "aws:SourceArn" => $topic->getArn(),
                ],
            ],
        ];
    }

    /**
     * @param Queue $queue
     * @param Topic $topic
     * @return bool
     */
    private function isTopicPermitted(Queue $queue, Topic $topic)
    {
        $permissions = $this->sqs->getQueueAttributes(
            [
                "AttributeNames" => ['Policy'],
                "QueueUrl"       => $queue->getDsn(),
            ]
        );

        $policy = json_decode($permissions->get('Attributes')['Policy'], true);
        $statements = $policy['Statement'];
        $permitted = false;
        if ($statements !== null) {
            foreach ($statements as $statement) {
                $permitted = ($statement['Condition']['ArnEquals']['aws:SourceArn'] === $topic->getArn()) ? true : $permitted;
            }
        }

        return $permitted;
    }

    /**
     * @param string $topic
     * @return Topic
     */
    public function createTopic(string $topic): DestinationInterface
    {
        $destination = new Topic($topic, $this->region, $this->account);
        try {
            $this->sns->getTopicAttributes(
                [
                    "TopicArn" => $destination->getArn(),
                ]
            );
        } catch (SnsException $e) {
            if ($e->getStatusCode() !== 404) {
                throw $e;
            }
            //create topic
            $this->sns->createTopic(
                [
                    'Name' => $destination->getName(),
                ]
            );
            //get the attributes
            $this->sns->getTopicAttributes(
                [
                    "TopicArn" => $destination->getArn(),
                ]
            );
        }

        return $destination;
    }


}