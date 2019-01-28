<?php

namespace Beyerz\AWSQueueBundle\Fabric\Local;

use Beyerz\AWSQueueBundle\Interfaces\DestinationInterface;
use Beyerz\AWSQueueBundle\Interfaces\FabricInterface;
use Beyerz\AWSQueueBundle\Service\ConsumerService;
use Doctrine\Common\Collections\ArrayCollection;

class Fabric implements FabricInterface
{

    /**
     * @var ArrayCollection
     */
    public $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    /**
     * Fabric should ensure that all notification channels and respective queues exist and subscribers are defined
     * @param string            $topic
     * @param ConsumerService[] $channel
     * @return mixed
     */
    public function setup(string $topic, string $channel)
    {

    }

    /**
     * @param DestinationInterface|Topic $destination
     * @param string                     $message
     * @return mixed|void
     */
    public function publish(DestinationInterface $destination, string $message)
    {
        $this->messages->add(
            [
                'topic'   => $destination->getName(),
                'content' => $message,
            ]
        );
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
            $topics = $service->getTopics();
            $topics->toArray();
            foreach ($this->messages as $index => $message) {
                if (in_array($message['topic'], $topics->toArray())) {
                    $service->getConsumer()->consume($message['content']);
                }
                $consumed++;
            }

            $this->messages = $this->messages->filter(
                function ($message) use ($topics) {
                    return !(in_array($message['topic'], $topics->toArray()));
                }
            );
        }
    }

    /**
     * @param $topic
     * @return DestinationInterface
     */
    public function createTopic(string $topic): DestinationInterface
    {
        return new Topic($topic);
    }

    /**
     * @param string      $queue
     * @param string|null $topic
     * @return DestinationInterface
     */
    public function createQueue(string $queue, string $topic = null): DestinationInterface
    {
        return new Queue($queue);
    }
}