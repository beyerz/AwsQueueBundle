<?php

namespace Beyerz\AWSQueueBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BeyerzTestProducerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('beyerz:demo:publish');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Beyerz Demo Producer");
        $producer1 = $this->getContainer()->get('beyerz_aws_queue.demo.producer.1');
        $producer2 = $this->getContainer()->get('beyerz_aws_queue.demo.producer.2');

        $io->comment("Publishing to producer1: \"Producer 1 message\"");
        $producer1->publish("Producer 1 message");
        $io->comment("Publishing to producer2: \"Producer 2 message\"");
        $producer2->publish("Producer 2 message");
        $io->success("Publish complete");

        return 0;
    }

}
