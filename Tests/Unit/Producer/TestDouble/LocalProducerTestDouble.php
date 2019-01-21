<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-21
 * Time: 17:37
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Producer\TestDouble;


use Beyerz\AWSQueueBundle\Interfaces\ProducerInterface;
use Beyerz\AWSQueueBundle\Producer\ProducerTrait;

class LocalProducerTestDouble implements ProducerInterface
{

    use ProducerTrait;

    public function publish($message)
    {
        $this->producer->publish($message);
    }
}