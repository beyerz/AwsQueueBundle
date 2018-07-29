<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 24/07/2018
 * Time: 14:41
 */

namespace Beyerz\AWSQueueBundle\Fabric;


class GearmanFabric extends AbstractFabric
{

    /**
     * Fabric should ensure that all notification channels and respective queues exist and subscribers are defined
     * @return mixed
     */
    public function setup()
    {
        // TODO: Implement setup() method.
    }
}