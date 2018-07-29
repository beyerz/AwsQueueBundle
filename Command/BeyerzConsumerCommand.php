<?php

namespace Beyerz\AWSQueueBundle\Command;

use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BeyerzConsumerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('beyerz:consumer')
            ->setDescription('Begin consuming messages from queue')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the queue that you want to consume from')
            ->addOption('messages', 'm', InputOption::VALUE_OPTIONAL, 'Optional limit to number of items to process from the queue', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0);
        $consumerKey = "beyerz_aws_queue.consumer_service.%s";
        $name = $input->getArgument('name');
        /** @var ConsumerService $consumer */
        $consumer = $this->getContainer()->get(sprintf($consumerKey, $name));
        $consumer->consume($input->getOption('messages'));
    }

}
