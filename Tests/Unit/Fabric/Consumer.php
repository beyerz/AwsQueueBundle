<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-21
 * Time: 16:49
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Fabric;


use Beyerz\AWSQueueBundle\Interfaces\ConsumerInterface;
use Doctrine\Common\Collections\ArrayCollection;

class Consumer implements ConsumerInterface
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

        return true;
    }
}