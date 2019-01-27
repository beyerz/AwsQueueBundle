<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:12
 */

namespace Beyerz\AWSQueueBundle\Interfaces;


use Beyerz\AWSQueueBundle\Service\ProducerService;

interface ProducerInterface
{
    /**
     * ProducerInterface constructor.
     * @param ProducerService $producer
     */
    public function setProducerService(ProducerService $producer);

    /**
     * @param string $message
     * @return mixed
     */
    public function publish($message);
}