<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:26
 */

namespace Beyerz\AWSQueueBundle\Demo;


use Beyerz\AWSQueueBundle\Interfaces\ProducerInterface;
use Beyerz\AWSQueueBundle\Service\ProducerTrait;

class Producer implements ProducerInterface
{
    use ProducerTrait;

    /**
     * @param $message
     * @return bool
     */
    public function publish($message)
    {
        return $this->producer->publish($message);
    }
}