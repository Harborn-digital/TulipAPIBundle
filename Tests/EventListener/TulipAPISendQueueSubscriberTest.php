<?php

namespace ConnectHolland\TulipAPIBundle\Tests\EventListener;

use ConnectHolland\TulipAPIBundle\EventListener\TulipAPISendQueueSubscriber;
use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * TulipAPISendQueueSubscriberTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class TulipAPISendQueueSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if TulipAPISendQueueSubscriber::getSubscribedEvents returns an array with a KernelEvents::TERMINATE key.
     */
    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', TulipAPISendQueueSubscriber::getSubscribedEvents());
        $this->assertArrayHasKey(KernelEvents::TERMINATE, TulipAPISendQueueSubscriber::getSubscribedEvents());
    }

    /**
     * Tests if constructing a new TulipAPISendQueueSubscriber sets the instance properties.
     */
    public function testConstruct()
    {
        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();

        $objectManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
                ->getMock();

        $managerRegistryMock = $this->getMockBuilder(ManagerRegistry::class)
                ->getMock();
        $managerRegistryMock->expects($this->once())
                ->method('getManager')
                ->willReturn($objectManagerMock);

        $subscriber = new TulipAPISendQueueSubscriber($queueManagerMock, $managerRegistryMock);

        $this->assertAttributeSame($queueManagerMock, 'queueManager', $subscriber);
        $this->assertAttributeSame($objectManagerMock, 'objectManager', $subscriber);
    }

    /**
     * Tests if TulipAPISendQueueSubscriber::onKernelTerminateSendQueue calls the QueueManager to send the queue.
     */
    public function testOnKernelTerminateSendQueue()
    {
        $objectManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
                ->getMock();

        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();
        $queueManagerMock->expects($this->once())
                ->method('sendQueue')
                ->with($this->equalTo($objectManagerMock));

        $managerRegistryMock = $this->getMockBuilder(ManagerRegistry::class)
                ->getMock();
        $managerRegistryMock->expects($this->once())
                ->method('getManager')
                ->willReturn($objectManagerMock);

        $postResponseEventMock = $this->getMockBuilder(PostResponseEvent::class)
                ->disableOriginalConstructor()
                ->getMock();

        $subscriber = new TulipAPISendQueueSubscriber($queueManagerMock, $managerRegistryMock);
        $subscriber->onKernelTerminateSendQueue($postResponseEventMock);
    }
}
