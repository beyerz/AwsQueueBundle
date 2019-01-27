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
     * @param Destination|Topic $destination
     * @param string            $message
     * @return bool
     */
    public function publish(Destination $destination, string $message)
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
     * @param string $queue
     * @return Destination|Queue
     */
    public function createQueue(string $queue): Destination
    {
        $destination = new Queue($queue, $this->region, $this->account);
        try {
            $this->sqs->getQueueAttributes(
                [
                    'QueueUrl' => $destination->getDsn(),
                ]
            );
        } catch (SqsException $e) {
            if (!$e->getStatusCode() == 400) {
                throw $e;
            }
            $this->sqs->createQueue(
                [
                    'QueueName'  => $destination->getName(),
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

        return $destination;
    }

    /**
     * @param string $topic
     * @return Topic
     */
    public function createTopic(string $topic): Destination
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

    public function subscribeToTopic()
    {
    }

}