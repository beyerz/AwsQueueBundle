<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'beyerz_aws_queue.producer_service.demo' shared service.

return $this->services['beyerz_aws_queue.producer_service.demo'] = new \Beyerz\AWSQueueBundle\Producer\ProducerService(${($_ = isset($this->services['beyerz_aws_queue.fabric.local']) ? $this->services['beyerz_aws_queue.fabric.local'] : $this->load('getBeyerzAwsQueue_Fabric_LocalService.php')) && false ?: '_'}, 'test_demo');
