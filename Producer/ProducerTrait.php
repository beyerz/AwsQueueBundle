<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 26/07/2018
 * Time: 12:13
 */

namespace Beyerz\AWSQueueBundle\Producer;

use Beyerz\AWSQueueBundle\Service\ProducerService;

/**
 * Trait ProducerTrait
 * @package    Beyerz\AWSQueueBundle\Producer
 * @deprecated since v1.0 and will be removed in v2.0 use Beyerz\AWSQueueBundle\Service\ProducerTrait instead
 */
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