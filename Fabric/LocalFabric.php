<?php
namespace Beyerz\AWSQueueBundle\Fabric;

use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Doctrine\Common\Collections\ArrayCollection;

class LocalFabric extends AbstractFabric
{
    /**
     * @var ArrayCollection
     */
    public $messages;

    public function __construct() {
        $this->messages = new ArrayCollection();
    }

    /**
     * Fabric should ensure that all notification channels and respective queues exist and subscribers are defined
     * @param string $channel
     * @param ConsumerService[] $subscribers
     * @return mixed
     */
    public function setup(string $channel, ArrayCollection $subscribers)
    {

    }

    public function publish(string $message, string $channel, ArrayCollection $subscribers)
    {
        $this->messages->add([
            'channel' => $channel,
            'content' => $message
        ]);
    }

    public function consume(ConsumerService $consumer, int $messageCount): int
    {
        $channel = $consumer->getChannel();
        $count = 0;
        foreach($this->messages as $index => $message) {
            if ($message['channel'] == $channel) {
                call_user_func([ $this->container->get($consumer->getConsumer()), 'consume' ], $message['content']);
            }
            $count++;
        }
        
        $this->messages = $this->messages->filter(function($message) use ($channel) {
            return $message['channel'] != $channel;
        });
        
        return $count;
    }
}