<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:41
 */

namespace Beyerz\AWSQueueBundle\Fabric;


use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Doctrine\Common\Collections\ArrayCollection;

class GearmanFabric extends AbstractFabric
{
    /**
     * Fabric should ensure that all notification channels and respective queues exist and subscribers are defined
     * @return mixed
     */
    public function setup(string $channel, ArrayCollection $subscribers)
    {
        // TODO: Implement setup() method.
    }

    public function setupNotificationServices(string $channel)
    {
        // TODO: Implement setupNotificationServices() method.
    }

    public function setupQueueServices(string $channel, ArrayCollection $subscribers)
    {
        // TODO: Implement setupQueueServices() method.
    }

    public function publish(string $message, string $channel, ArrayCollection $subscribers)
    {
        // TODO: Implement publish() method.
    }

    public function consume(ConsumerService $consumer, int $messageCount): int
    {
        // TODO: Implement consume() method.
    }
}