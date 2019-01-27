<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 26/07/2018
 * Time: 17:22
 */

namespace Beyerz\AWSQueueBundle\Service;


use Beyerz\AWSQueueBundle\Fabric\AbstractFabric;
use Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs\Queue;
use Beyerz\AWSQueueBundle\Interfaces\ConsumerInterface;
use Beyerz\AWSQueueBundle\Interfaces\FabricInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ConsumerService
{
    use ContainerAwareTrait;

    /**
     * @var AbstractFabric
     */
    private $fabric;

    /**
     * @var bool
     */
    private $isForked;

    /**
     * @var string
     */
    private $channel;

    /**
     * @var ArrayCollection|string[]
     */
    private $topics;

    /**
     * Service id of the consumer to load
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * ProducerService constructor.
     * @param FabricInterface $fabric
     * @param bool            $isForked
     * @param string          $channel
     * @param array|null      $topics
     */
    public function __construct(FabricInterface $fabric, bool $isForked, string $channel, array $topics)
    {
        $this->fabric = $fabric;
        $this->isForked = $isForked;
        $this->channel = $channel;
        $this->topics = new ArrayCollection($topics);
        foreach ($this->topics as $topic) {
            $this->queue = $this->fabric->createQueue($this->channel, $topic);
        }
    }

    /**
     * @param ConsumerInterface $consumer
     */
    public function setConsumer(ConsumerInterface $consumer)
    {
        $this->consumer = $consumer;
    }

    /**
     * @return ConsumerInterface
     */
    public function getConsumer()
    {
        return $this->consumer;
    }

    /**
     * @return Queue
     */
    public function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * @return ArrayCollection|string[]
     */
    public function getTopics(): ArrayCollection
    {
        return $this->topics;
    }

    /**
     * The topic name of a producer that the consumer would be subscribed to
     * @param ArrayCollection $topic
     * @return $this
     */
    public function addTopic(ArrayCollection $topic)
    {
        $this->topics->add($topic);

        return $this;
    }

    /**
     * @param int $toProcess
     */
    public function consume($toProcess = -1)
    {
        if (true === $this->isForked) {
            $this->runForkedConsumer($toProcess);
        } else {
            $this->runSynchronousConsumer($toProcess);
        }
    }

    private function runForkedConsumer($toProcess = -1)
    {
        foreach ($this->topics as $topic) {
            $processed = 0;
            while ($processed < $toProcess || $toProcess === -1) {
                $processed++;

                $pid = pcntl_fork();
                if ($pid == -1) {
                    //error
                    throw new \RuntimeException("Could not fork process");
                } elseif ($pid) {
                    pcntl_waitpid($pid, $status);
                    $this->resetDoctrine();
                } else {
                    $messageCount = (-1 === $toProcess) ? -1 : ($toProcess - $processed);
                    $this->fabric->consume($this, $messageCount);
                    exit(0);
                }
            }
        }
    }

    /**
     * @param int $toProcess
     */
    private function runSynchronousConsumer($toProcess = -1)
    {
        //set msg count to -1 for infinite run
        $this->fabric->consume($this, $toProcess);
    }

    private function resetDoctrine()
    {
        $this->container->get('doctrine.orm.entity_manager')->getConnection()->close();
        $this->container->get('doctrine.orm.entity_manager')->getConnection()->connect();
    }
}