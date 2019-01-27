<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-22
 * Time: 12:12
 */

namespace Beyerz\AWSQueueBundle\Interfaces;


use Beyerz\AWSQueueBundle\Service\ConsumerService;

interface FabricInterface
{

    /**
     * @param DestinationInterface $destination
     * @param string               $message
     * @return mixed
     */
    public function publish(DestinationInterface $destination, string $message);

    /**
     * @param ConsumerService $service
     * @param int             $messageCount
     * @return void
     */
    public function consume(ConsumerService $service, int $messageCount);

    /**
     * @param $topic
     * @return DestinationInterface
     */
    public function createTopic(string $topic): DestinationInterface;

    /**
     * @param string      $queue
     * @param string|null $topic
     * @return DestinationInterface
     */
    public function createQueue(string $queue, string $topic = null): DestinationInterface;

}