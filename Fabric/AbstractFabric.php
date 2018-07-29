<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:43
 */

namespace Beyerz\AWSQueueBundle\Fabric;

use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractFabric
{

    protected $queueService;

    protected $notificationService;

    /**
     * AbstractFabric constructor.
     * @param $queueService
     * @param $notificationService
     */
    public function __construct($queueService, $notificationService)
    {
        $this->queueService = $queueService;
        $this->notificationService = $notificationService;
    }

    /**
     * @return mixed
     */
    public function getQueueService()
    {
        return $this->queueService;
    }

    /**
     * @return mixed
     */
    public function getNotificationService()
    {
        return $this->notificationService;
    }

    /**
     * Fabric should ensure that all notification channels and respective queues exist and subscribers are defined
     * @param string          $channel
     * @param ArrayCollection $subscribers
     * @return mixed
     */
    abstract public function setup(string $channel, ArrayCollection $subscribers);

    /**
     * @param string $channel
     * @return mixed
     */
    abstract public function setupNotificationServices(string $channel);

    /**
     * @param string          $channel
     * @param ArrayCollection $subscribers
     * @return mixed
     */
    abstract public function setupQueueServices(string $channel, ArrayCollection $subscribers);

    /**
     * @param                 $message
     * @param                 $channel
     * @param ArrayCollection $subscribers
     * @return mixed
     */
    abstract public function publish($message, $channel, ArrayCollection $subscribers);

    abstract public function consume($toProcess = true, $channel, ConsumerService $consumer);
}