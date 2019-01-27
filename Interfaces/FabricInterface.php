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
     * @return void
     */
    public function consume(ConsumerService $service, int $messageCount);

    /**
     * @param $topic
     * @return Destination
     */
    public function createTopic(string $topic): Destination;

    /**
     * @param string      $queue
     * @param string|null $topic
     * @return Destination
     */
    public function createQueue(string $queue, string $topic = null): Destination;

}