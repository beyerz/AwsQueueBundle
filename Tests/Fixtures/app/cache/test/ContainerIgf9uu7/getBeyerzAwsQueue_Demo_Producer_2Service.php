<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'beyerz_aws_queue.demo.producer.2' shared service.

$this->services['beyerz_aws_queue.demo.producer.2'] = $instance = new \Beyerz\AWSQueueBundle\Demo\Producer();

$instance->setProducerService(${($_ = isset($this->services['beyerz_aws_queue.producer_service.demo_producer_2']) ? $this->services['beyerz_aws_queue.producer_service.demo_producer_2'] : $this->load('getBeyerzAwsQueue_ProducerService_DemoProducer2Service.php')) && false ?: '_'});

return $instance;
