<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-26
 * Time: 16:55
 */

namespace Beyerz\AWSQueueBundle\Fabric\Local;


use Beyerz\AWSQueueBundle\Interfaces\DestinationInterface;

class Queue implements DestinationInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * Topic constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}