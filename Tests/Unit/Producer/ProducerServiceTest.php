<?php

namespace Tests\Unit\Producer;

use Beyerz\AWSQueueBundle\Consumer\ConsumerService;
use Beyerz\AWSQueueBundle\Fabric\AbstractFabric;
use Doctrine\Common\Collections\ArrayCollection;
use Tests\Unit\Producer\TestDouble\ProducerServiceTestDouble;

class ProducerServiceTest extends \PHPUnit\Framework\TestCase {

    /**
     * @throws \ReflectionException
     */
    public function testPublishMessage() {

        $fabricMock = $this->getMockForAbstractClass(AbstractFabric::class, [], '',false);

        $fabricMock
            ->expects($this->once())
            ->method('publish')
            ->withConsecutive([
                $this->equalTo('bar'),
                $this->equalTo('foo'),
                $this->isInstanceOf(ArrayCollection::class)
            ]);

        $service = new \Beyerz\AWSQueueBundle\Producer\ProducerService(
            $fabricMock,
            'foo'
        );
        
        $service->publish('bar');
    }

    /**
     * @throws \ReflectionException
     */
    public function testWillAddOneAndMultipleSubscribers() {

        $consumerMock = $this->createMock(ConsumerService::class);
        $consumerMockTwo = $this->createMock(ConsumerService::class);

        $fabricMock = $this->getMockForAbstractClass(AbstractFabric::class, [], '',false);

        $service = new ProducerServiceTestDouble(
            $fabricMock,
            'foo'
        );

        $return = $service->addSubscriber($consumerMock);
        $this->assertEquals([$consumerMock], $service->getSubscribers()->toArray());

        $service->addSubscriber($consumerMockTwo);
        $this->assertEquals([$consumerMock, $consumerMockTwo], $service->getSubscribers()->toArray());

        $this->assertSame(
            $service,
            $return
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testWillReturnChannel() {
        $fabricMock = $this->getMockForAbstractClass(AbstractFabric::class, [], '',false);

        $testChannel = 'foo';
        $service = new ProducerServiceTestDouble(
            $fabricMock,
            $testChannel
        );

        $this->assertEquals(
            $testChannel,
            $service->getChannel()
        );
    }
}