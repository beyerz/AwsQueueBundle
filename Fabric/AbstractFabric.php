<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:43
 */

namespace Beyerz\AWSQueueBundle\Fabric;

use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Beyerz\AWSQueueBundle\Interfaces\FabricInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractFabric implements FabricInterface
{
    use ContainerAwareTrait;

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
     * @param string $topic
     * @param string $channel
     * @return mixed
     */
    abstract public function setup(string $topic, string $channel);

    /**
     * @param string $topic
     * @param string $channel
     * @param string $subscribers
     * @return mixed
     */
    abstract public function publish(string $topic, string $channel);

    /**
     * @param ConsumerService $service
     * @param int             $messageCount
     * @return int
     */
    abstract public function consume(ConsumerService $service, int $messageCount): int;
}