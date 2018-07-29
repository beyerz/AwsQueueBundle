<?php

namespace Beyerz\AWSQueueBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BeyerzTestProducerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('beyerz:producer');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $producer1 = $this->getContainer()->get('beyerz_aws_queue.demo.producer.1');
        $producer2 = $this->getContainer()->get('beyerz_aws_queue.demo.producer.2');
        $producer1->publish("Producer 1 message");
        $producer2->publish("Producer 2 message");
        die;
    }

}
