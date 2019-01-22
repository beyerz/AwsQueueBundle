<?php

namespace Beyerz\AWSQueueBundle\Fabric;

use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Beyerz\AWSQueueBundle\Interfaces\FabricInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LocalFabric implements FabricInterface
{
    use ContainerAwareTrait;

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

    public function publish(string $message, string $topic)
    {
        $this->messages->add(
            [
                'topic'   => $topic,
                'content' => $message,
            ]
        );
    }

    public function consume(ConsumerService $service, int $messageCount): int
    {
        $topics = $service->getTopics();
        $topics->toArray();
        $count = 0;
        foreach ($this->messages as $index => $message) {
            if (in_array($message['topic'], $topics->toArray())) {
                $service->getConsumer()->consume($message['content']);
            }
            $count++;
        }

        $this->messages = $this->messages->filter(
            function ($message) use ($topics) {
                return !(in_array($message['topic'], $topics->toArray()));
            }
        );

        return $count;
    }
}