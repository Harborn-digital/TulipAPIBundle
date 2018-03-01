<?php

namespace ConnectHolland\TulipAPIBundle\Tests\EventListener;

use ConnectHolland\TulipAPIBundle\EventListener\TulipAPIQueueSubscriber;
use ConnectHolland\TulipAPIBundle\Model\TulipObjectInterface;
use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use PHPUnit_Framework_TestCase;

/**
 * TulipAPIQueueSubscriberTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class TulipAPIQueueSubscriberTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a new TulipAPIQueueSubscriber sets the instance properties.
     */
    public function testConstruct()
    {
        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();

        $subscriber = new TulipAPIQueueSubscriber($queueManagerMock, false);

        $this->assertAttributeSame($queueManagerMock, 'queueManager', $subscriber);
        $this->assertAttributeSame(false, 'debug', $subscriber);
    }

    /**
     * Tests if TulipAPIQueueSubscriber::getSubscribedEvents returns the expected array with Doctrine events.
     */
    public function testGetSubscribedEvents()
    {
        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();

        $subscriber = new TulipAPIQueueSubscriber($queueManagerMock, false);

        $this->assertSame(array(Events::postPersist, Events::postUpdate), $subscriber->getSubscribedEvents());
    }

    /**
     * Tests if TulipAPIQueueSubscriber:postPersist queues an object in the QueueManager.
     */
    public function testPostPersist()
    {
        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();

        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();
        $queueManagerMock->expects($this->once())
                ->method('queueObject')
                ->with($this->equalTo($objectMock));

        $unitOfWorkMock = $this->getMockBuilder(UnitOfWork::class)
                ->disableOriginalConstructor()
                ->getMock();
        $unitOfWorkMock->expects($this->once())
                ->method('getEntityChangeSet')
                ->with($this->equalTo($objectMock))
                ->willReturn(array());

        $objectManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
                ->getMock();
        $objectManagerMock->expects($this->once())
                ->method('getUnitOfWork')
                ->willReturn($unitOfWorkMock);

        $lifecycleEventArgsMock = $this->getMockBuilder(LifecycleEventArgs::class)
                ->disableOriginalConstructor()
                ->getMock();
        $lifecycleEventArgsMock->expects($this->once())
                ->method('getObject')
                ->willReturn($objectMock);
        $lifecycleEventArgsMock->expects($this->once())
                ->method('getObjectManager')
                ->willReturn($objectManagerMock);

        $subscriber = new TulipAPIQueueSubscriber($queueManagerMock, false);
        $subscriber->postPersist($lifecycleEventArgsMock);
    }

    /**
     * Tests if TulipAPIQueueSubscriber:postPersist does not queue an object in the QueueManager when only tulipId has changed.
     */
    public function testPostPersistDoesNotQueueObjectWhenOnlyTulipIdChanged()
    {
        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();

        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();
        $queueManagerMock->expects($this->never())
                ->method('queueObject');

        $unitOfWorkMock = $this->getMockBuilder(UnitOfWork::class)
                ->disableOriginalConstructor()
                ->getMock();
        $unitOfWorkMock->expects($this->once())
                ->method('getEntityChangeSet')
                ->with($this->equalTo($objectMock))
                ->willReturn(array('tulipId' => 1));

        $objectManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
                ->getMock();
        $objectManagerMock->expects($this->once())
                ->method('getUnitOfWork')
                ->willReturn($unitOfWorkMock);

        $lifecycleEventArgsMock = $this->getMockBuilder(LifecycleEventArgs::class)
                ->disableOriginalConstructor()
                ->getMock();
        $lifecycleEventArgsMock->expects($this->once())
                ->method('getObject')
                ->willReturn($objectMock);
        $lifecycleEventArgsMock->expects($this->once())
                ->method('getObjectManager')
                ->willReturn($objectManagerMock);

        $subscriber = new TulipAPIQueueSubscriber($queueManagerMock, false);
        $subscriber->postPersist($lifecycleEventArgsMock);
    }

    /**
     * Tests if TulipAPIQueueSubscriber:postPersist queues an object in the QueueManager when more than just the tulipId has changed.
     */
    public function testPostPersistQueuesObjectWhenMoreThanTulipIdChanged()
    {
        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();

        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();
        $queueManagerMock->expects($this->once())
                ->method('queueObject')
                ->with($this->equalTo($objectMock));

        $unitOfWorkMock = $this->getMockBuilder(UnitOfWork::class)
                ->disableOriginalConstructor()
                ->getMock();
        $unitOfWorkMock->expects($this->once())
                ->method('getEntityChangeSet')
                ->with($this->equalTo($objectMock))
                ->willReturn(array('someOtherValue' => 'has changed', 'tulipId' => 1));

        $objectManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
                ->getMock();
        $objectManagerMock->expects($this->once())
                ->method('getUnitOfWork')
                ->willReturn($unitOfWorkMock);

        $lifecycleEventArgsMock = $this->getMockBuilder(LifecycleEventArgs::class)
                ->disableOriginalConstructor()
                ->getMock();
        $lifecycleEventArgsMock->expects($this->once())
                ->method('getObject')
                ->willReturn($objectMock);
        $lifecycleEventArgsMock->expects($this->once())
                ->method('getObjectManager')
                ->willReturn($objectManagerMock);

        $subscriber = new TulipAPIQueueSubscriber($queueManagerMock, false);
        $subscriber->postPersist($lifecycleEventArgsMock);
    }

    /**
     * Tests if TulipAPIQueueSubscriber:postPersist queues an object in the QueueManager and sends the object directly when in debug mode.
     */
    public function testPostPersistSendsQueueInDebugMode()
    {
        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();

        $unitOfWorkMock = $this->getMockBuilder(UnitOfWork::class)
                ->disableOriginalConstructor()
                ->getMock();
        $unitOfWorkMock->expects($this->once())
                ->method('getEntityChangeSet')
                ->with($this->equalTo($objectMock))
                ->willReturn(array());

        $objectManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
                ->getMock();
        $objectManagerMock->expects($this->once())
                ->method('getUnitOfWork')
                ->willReturn($unitOfWorkMock);

        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();
        $queueManagerMock->expects($this->once())
                ->method('queueObject')
                ->with($this->equalTo($objectMock));
        $queueManagerMock->expects($this->once())
                ->method('sendQueue')
                ->with($this->equalTo($objectManagerMock));

        $lifecycleEventArgsMock = $this->getMockBuilder(LifecycleEventArgs::class)
                ->disableOriginalConstructor()
                ->getMock();
        $lifecycleEventArgsMock->expects($this->once())
                ->method('getObject')
                ->willReturn($objectMock);
        $lifecycleEventArgsMock->expects($this->exactly(2))
                ->method('getObjectManager')
                ->willReturn($objectManagerMock);

        $subscriber = new TulipAPIQueueSubscriber($queueManagerMock, true);
        $subscriber->postPersist($lifecycleEventArgsMock);
    }

    /**
     * Tests if TulipAPIQueueSubscriber:postUpdate queues an object in the QueueManager.
     */
    public function testPostUpdate()
    {
        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();

        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
                ->disableOriginalConstructor()
                ->getMock();
        $queueManagerMock->expects($this->once())
                ->method('queueObject')
                ->with($this->equalTo($objectMock));

        $unitOfWorkMock = $this->getMockBuilder(UnitOfWork::class)
                ->disableOriginalConstructor()
                ->getMock();
        $unitOfWorkMock->expects($this->once())
                ->method('getEntityChangeSet')
                ->with($this->equalTo($objectMock))
                ->willReturn(array());

        $objectManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
                ->getMock();
        $objectManagerMock->expects($this->once())
                ->method('getUnitOfWork')
                ->willReturn($unitOfWorkMock);

        $lifecycleEventArgsMock = $this->getMockBuilder(LifecycleEventArgs::class)
                ->disableOriginalConstructor()
                ->getMock();
        $lifecycleEventArgsMock->expects($this->once())
                ->method('getObject')
                ->willReturn($objectMock);
        $lifecycleEventArgsMock->expects($this->once())
                ->method('getObjectManager')
                ->willReturn($objectManagerMock);

        $subscriber = new TulipAPIQueueSubscriber($queueManagerMock, false);
        $subscriber->postUpdate($lifecycleEventArgsMock);
    }
}
