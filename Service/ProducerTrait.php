<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 26/07/2018
 * Time: 12:13
 */

namespace Beyerz\AWSQueueBundle\Service;

trait ProducerTrait
{
    /**
     * @var ProducerService
     */
    private $producer;

    /**
     * ProducerInterface constructor.
     * @param ProducerService $producer
     */
    public function setProducerService(ProducerService $producer)
    {
        $this->producer = $producer;
    }
}