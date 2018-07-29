<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:25
 */

namespace Beyerz\AWSQueueBundle\Demo;


use Beyerz\AWSQueueBundle\Interfaces\ConsumerInterface;

class Consumer implements ConsumerInterface
{

    /**
     * @param $message
     * @return boolean
     */
    public function consume($message)
    {
        dump("Got the message:", $message);
        return true;
    }
}