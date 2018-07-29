<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:26
 */

namespace Beyerz\AWSQueueBundle\Demo;


use Beyerz\AWSQueueBundle\Interfaces\ProducerInterface;
use Beyerz\AWSQueueBundle\Producer\ProducerTrait;

class Producer implements ProducerInterface
{
    use ProducerTrait;

    public function publish($message)
    {
        $this->producer->publish($message);
    }
}