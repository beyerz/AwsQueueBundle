<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:40
 */

namespace Beyerz\AWSQueueBundle\Service;


use Beyerz\AWSQueueBundle\Fabric\AbstractFabric;
use Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs\Destination;
use Beyerz\AWSQueueBundle\Interfaces\FabricInterface;

class ProducerService
{

    /**
     * @var AbstractFabric
     */
    private $fabric;

    /**
     * @var Destination
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