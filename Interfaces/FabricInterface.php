<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 12:12
 */

namespace Beyerz\AWSQueueBundle\Interfaces;


use Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs\Destination;
use Beyerz\AWSQueueBundle\Service\ConsumerService;

interface FabricInterface
{

    /**
     * @param Destination $destination
     * @param string      $message
     * @return mixed
     */
    public function publish(Destination $destination, string $message);

    /**
     * @param ConsumerService $service
     * @param int             $messageCount
     * @return int
     */
    public function consume(ConsumerService $service, int $messageCount): int;

    /**
     * @param $topic
     * @return Destination
     */
    public function createTopic(string $topic): Destination;

    /**
     * @param string $queue
     * @return mixed
     */
    public function createQueue(string $queue): Destination;

}