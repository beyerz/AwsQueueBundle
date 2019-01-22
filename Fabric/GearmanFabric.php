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
     * @param string $topic
     * @param string $channel
     * @return mixed
     */
    public function setup(string $topic, string $channel)
    {
        // TODO: Implement setup() method.
    }

    public function publish(string $topic, string $channel)
    {
        // TODO: Implement publish() method.
    }

    public function consume(ConsumerService $service, int $messageCount): int
    {
        // TODO: Implement consume() method.
    }
}