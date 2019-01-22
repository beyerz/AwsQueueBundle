<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:40
 */

namespace Beyerz\AWSQueueBundle\Producer;


use Beyerz\AWSQueueBundle\Fabric\AbstractFabric;
use Beyerz\AWSQueueBundle\Interfaces\FabricInterface;

class ProducerService
{

    /**
     * @var AbstractFabric
     */
    private $fabric;

    /**
     * @var string
     */
    private $topic;

    /**
     * ProducerService constructor.
     * @param FabricInterface $fabric
     * @param string          $topic
     */
    public function __construct(FabricInterface $fabric, string $topic)
    {
        $this->fabric = $fabric;
        $this->topic = $topic;
    }

    /**
     * @param mixed $message
     */
    public function publish(string $message)
    {
        $this->fabric->publish($message, $this->topic);
    }

    public function getTopic()
    {
        return $this->topic;
    }

}