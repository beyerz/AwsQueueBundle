<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:40
 */

namespace Beyerz\AWSQueueBundle\Producer;


use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Beyerz\AWSQueueBundle\Fabric\AbstractFabric;
use Doctrine\Common\Collections\ArrayCollection;

class ProducerService
{

    /**
     * @var AbstractFabric
     */
    private $fabric;

    /**
     * @var string
     */
    private $channel;

    /**
     * Consumers that would listen to the producer
     * @var array
     */
    private $subscribers;

    /**
     * ProducerService constructor.
     * @param AbstractFabric $fabric
     * @param string         $channel
     */
    public function __construct(AbstractFabric $fabric, string $channel)
    {
        $this->fabric = $fabric;
        $this->channel = $channel;
        $this->subscribers = new ArrayCollection();
    }

    public function publish($message)
    {
        $this->fabric->publish(serialize($message), $this->channel, $this->subscribers);
    }

    public function addSubscriber(ConsumerService $consumer)
    {
        $this->subscribers->add($consumer);

        return $this;
    }

    public function getChannel()
    {
        return $this->channel;
    }

}