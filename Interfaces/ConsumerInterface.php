<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:13
 */

namespace Beyerz\AWSQueueBundle\Interfaces;


interface ConsumerInterface
{

    /**
     * @param $message
     * @return boolean
     */
    public function consume($message);
}