<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-26
 * Time: 16:55
 */

namespace Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs;


class Topic implements Destination
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $arn;

    /**
     * Topic constructor.
     * @param string $name
     * @param string $region
     * @param string $account
     */
    public function __construct(string $name, string $region, string $account)
    {
        $this->name = $name;
        $this->arn = $this->generateArn($name, $region, $account);
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
    public function getArn(): string
    {
        return $this->arn;
    }

    /**
     * @param string $name
     * @param string $region
     * @param string $account
     * @return string
     */
    private function generateArn(string $name, string $region, string $account)
    {
        return sprintf("arn:aws:sns:%s:%s:%s", $region, $account, $name);
    }
}