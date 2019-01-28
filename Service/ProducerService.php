<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:40
 */

namespace Beyerz\AWSQueueBundle\Service;


use Beyerz\AWSQueueBundle\Interfaces\DestinationInterface;
use Beyerz\AWSQueueBundle\Interfaces\FabricInterface;

class ProducerService
{

    /**
     * @var FabricInterface
     */
    private $fabric;

    /**
     * @var DestinationInterface
     */
    private $destination;

    /**
     * ProducerService constructor.
     * @param FabricInterface $fabric
     * @param string          $topic
     */
    public function __construct(FabricInterface $fabric, string $topic)
    {
        $this->fabric = $fabric;
        $this->destination = $fabric->createTopic($topic);
    }

    /**
     * @param string $message
     * @return bool
     */
    public function publish(string $message)
    {
        return $this->fabric->publish($this->destination, $message);
    }
}