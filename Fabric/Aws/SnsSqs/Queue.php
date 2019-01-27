<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-26
 * Time: 16:55
 */

namespace Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs;


use Beyerz\AWSQueueBundle\Interfaces\DestinationInterface;

class Queue implements DestinationInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $dsn;

    /**
     * Topic constructor.
     * @param string $name
     * @param string $region
     * @param string $account
     */
    public function __construct(string $name, string $region, string $account)
    {
        $this->name = $name;
        $this->dsn = $this->generateQueueUrl($name, $region, $account);
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDsn(): string
    {
        return $this->dsn;
    }

    /**
     * @param string $name
     * @param string $region
     * @param string $account
     * @return string
     */
    private function generateQueueUrl(string $name, string $region, string $account)
    {
        return sprintf("https://sqs.%s.amazonaws.com/%s/%s", $region, $account, $name);
    }
}