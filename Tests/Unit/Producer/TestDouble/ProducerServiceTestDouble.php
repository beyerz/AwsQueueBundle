<?php

namespace Beyerz\AWSQueueBundle\Tests\Unit\Producer\TestDouble;

use Beyerz\AWSQueueBundle\Producer\ProducerService;

class ProducerServiceTestDouble extends ProducerService
{

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSubscribers()
    {

        return $this->subscribers;
    }
}