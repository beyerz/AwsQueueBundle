<?php

namespace Beyerz\AWSQueueBundle\Command;

use Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs\Fabric;
use Beyerz\AWSQueueBundle\Service\ProducerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AwsTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('aws:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $producer = $this->getContainer()->get('beyerz_aws_queue.demo.producer.1');
        dump($producer);
        die;
//        $sns = $this->getContainer()->get('aws.sns');
//        $sqs = $this->getContainer()->get('aws.sqs');
//        $fabric = new Fabric($sns->getRegion(), '568859729746', $sns, $sqs);
//        $service = new ProducerService($fabric, 'lance-test');
//        dump($service->publish("sample message"));
    }

}
