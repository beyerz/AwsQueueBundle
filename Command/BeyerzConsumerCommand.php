<?php

namespace Beyerz\AWSQueueBundle\Command;

use Beyerz\AWSQueueBundle\Service\ConsumerService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BeyerzConsumerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('beyerz:consumer')
            ->setDescription('Begin consuming messages from queue')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the queue that you want to consume from')
            ->addOption('messages', 'm', InputOption::VALUE_OPTIONAL, 'Optional limit to number of items to process from the queue', -1);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (is_null($input->getArgument('name'))) {
            $this->list($input, $output);
        } else {
            $this->consume($input, $output);
        }
    }

    private function consume(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0);
        $consumerKey = "beyerz_aws_queue.consumer_service.%s";
        $name = $input->getArgument('name');
        /** @var ConsumerService $consumer */
        $consumer = $this->getContainer()->get(sprintf($consumerKey, $name));
        $messages = $input->getOption('messages');
        return $consumer->consume((int)$input->getOption('messages'));
    }

    private function list(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("Consumers List");

        $headers = [
            "Service",
            "Class",
            "Name",
        ];

        $rows = $this->getContainer()->get('beyerz_aws_queue.consumer.list')->toArray();
        $io->table($headers, $rows);
    }

}
