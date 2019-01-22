<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 12:12
 */

namespace Beyerz\AWSQueueBundle\Interfaces;


use Beyerz\AWSQueueBundle\Consumer\ConsumerService;

interface FabricInterface
{

    /**
     * Fabric should ensure that all notification channels and respective queues exist and subscribers are defined
     * @param string      $topic
     * @param string|null $channel
     * @return mixed
     */
    public function setup(string $topic, string $channel);

    /**
     * @param string $message
     * @param string $topic
     * @return mixed
     */
    public function publish(string $message, string $topic);

    /**
     * @param ConsumerService $service
     * @param int             $messageCount
     * @return int
     */
    public function consume(ConsumerService $service, int $messageCount): int;
}