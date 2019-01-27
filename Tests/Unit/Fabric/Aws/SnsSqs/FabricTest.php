<?php
/**
 * Created by PhpStorm.
 * User: beyerz
 * Date: 2019-01-26
 * Time: 17:03
 */

namespace Beyerz\AWSQueueBundle\Tests\Unit\Fabric\Aws\SnsSqs;


use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs\Fabric;
use Beyerz\AWSQueueBundle\Fabric\Aws\SnsSqs\Topic;
use Liip\FunctionalTestBundle\Test\WebTestCase;

class FabricTest extends WebTestCase
{

    /**
     * @dataProvider createTopicProvider
     * @param string    $region
     * @param string    $account
     * @param SnsClient $sns
     * @param SqsClient $sqs
     * @param string    $topic
     * @param array     $expected
     */
    public function testCreateTopic(string $region, string $account, SnsClient $sns, SqsClient $sqs, string $topic, array $expected)
    {
        $fabric = new Fabric($region, $account, $sns, $sqs);
        $actual = $fabric->createTopic($topic);
        $this->assertInstanceOf($expected['instance'], $actual);
        $this->assertEquals($expected['arn'], $actual->getArn());
    }

    public function createTopicProvider()
    {
        $sns = $this->getMockBuilder(SnsClient::class)->disableOriginalConstructor()->getMock();
        $sqs = $this->getMockBuilder(SqsClient::class)->disableOriginalConstructor()->getMock();

        return [
            [
                'us-east-1',
                '123456789012',
                $sns,
                $sqs,
                'sample',
                [
                    'instance' => Topic::class,
                    'arn'      => 'arn:aws:sns:us-east-1:123456789012:sample',
                ],
            ],
            [
                'us-east-1',
                '123456789012',
                $sns,
                $sqs,
                'dev_sample',
                [
                    'instance' => Topic::class,
                    'arn'      => 'arn:aws:sns:us-east-1:123456789012:dev_sample',
                ],
            ],
            [
                'us-east-1',
                '123456789012',
                $sns,
                $sqs,
                'prod_sample',
                [
                    'instance' => Topic::class,
                    'arn'      => 'arn:aws:sns:us-east-1:123456789012:prod_sample',
                ],
            ],
        ];
    }
}