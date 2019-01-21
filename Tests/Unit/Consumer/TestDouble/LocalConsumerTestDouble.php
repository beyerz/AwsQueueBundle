<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-21
 * Time: 16:49
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Consumer\TestDouble;


use Beyerz\AWSQueueBundle\Interfaces\ConsumerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class LocalConsumerTestDouble implements ConsumerInterface
{

    public $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    /**
     * @param $message
     * @return boolean
     */
    public function consume($message)
    {
        $this->messages->add($message);
    }
}