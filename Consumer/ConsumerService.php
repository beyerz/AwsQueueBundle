<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 26/07/2018
 * Time: 17:22
 */

namespace Beyerz\AWSQueueBundle\Consumer;


use Beyerz\AWSQueueBundle\Fabric\AbstractFabric;
use Beyerz\AWSQueueBundle\Producer\ProducerService;
use Doctrine\Common\Collections\ArrayCollection;

class ConsumerService
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
     * @var ArrayCollection|ProducerService[]
     */
    private $subscribedChannels;

    /**
     * Service id of the consumer to load
     * @var string
     */
    private $consumer;

    /**
     * ProducerService constructor.
     * @param AbstractFabric $fabric
     * @param string         $channel
     */
    public function __construct(AbstractFabric $fabric, string $channel)
    {
        $this->fabric = $fabric;
        $this->channel = $channel;
        $this->subscribedChannels = new ArrayCollection();
    }

    public function setConsumer($consumer)
    {
        $this->consumer = $consumer;
    }

    public function getConsumer(){
        return $this->consumer;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    public function addSubscribedChannel(ProducerService $channel)
    {
        $this->subscribedChannels->add($channel);

        return $this;
    }

    public function consume($messageCount)
    {
        foreach ($this->subscribedChannels as $subscribedChannel) {
            $this->fabric->consume($messageCount, $subscribedChannel->getChannel(), $this);
        }
    }
}