<?php

namespace ConnectHolland\TulipAPIBundle\Tests\Queue;

use ConnectHolland\TulipAPI\Client;
use ConnectHolland\TulipAPIBundle\Model\TulipObjectInterface;
use ConnectHolland\TulipAPIBundle\Model\TulipUploadObjectInterface;
use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use PHPUnit_Framework_TestCase;

/**
 * QueueManagerTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class QueueManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if constructing a QueueManager instance sets the properties.
     */
    public function testConstruct()
    {
        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();

        $queueManager = new QueueManager($clientMock);

        $this->assertAttributeSame($clientMock, 'client', $queueManager);
        $this->assertAttributeSame(array(), 'objectsMap', $queueManager);
    }

    /**
     * Tests if QueueManager::queueObject adds the object to the queuedObjects property.
     */
    public function testQueueObject()
    {
        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();

        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();

        $queueManager = new QueueManager($clientMock);
        $queueManager->queueObject($objectMock);

        $this->assertAttributeSame(array($objectMock), 'queuedObjects', $queueManager);
    }

    /**
     * Tests if QueueManager::sendQueue does nothing without any errors when no objects are queued.
     */
    public function testSendQueueWithEmptyQueue()
    {
        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->never())
                ->method('callService');

        $queueManager = new QueueManager($clientMock);
        $queueManager->sendQueue();
    }

    /**
     * Tests if QueueManager::sendQueue calls the Tulip API client for the object in the queue.
     */
    public function testSendQueueWithoutObjectsMap()
    {
        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();
        $objectMock->expects($this->once())
                ->method('getTulipParameters')
                ->willReturn(array());

        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->once())
                ->method('callService')
                ->with($this->equalTo(strtolower(get_class($objectMock))), $this->equalTo('save'), $this->equalTo(array()), $this->equalTo(array()));

        $queueManager = new QueueManager($clientMock);
        $queueManager->queueObject($objectMock);
        $queueManager->sendQueue();
    }

    /**
     * Tests if QueueManager::sendQueue calls the Tulip API client for the object in the queue.
     */
    public function testSendQueueUploadsWithoutObjectsMap()
    {
        $objectMock = $this->getMockBuilder(TulipUploadObjectInterface::class)
                ->getMock();
        $objectMock->expects($this->once())
                ->method('getTulipParameters')
                ->willReturn(array());
        $objectMock->expects($this->once())
                ->method('getTulipUploads')
                ->willReturn(array());

        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->once())
                ->method('callService')
                ->with($this->equalTo(strtolower(get_class($objectMock))), $this->equalTo('save'), $this->equalTo(array()), $this->equalTo(array()));

        $queueManager = new QueueManager($clientMock);
        $queueManager->queueObject($objectMock);
        $queueManager->sendQueue();
    }

    /**
     * Tests if QueueManager::sendQueue calls the Tulip API client for the object in the queue.
     */
    public function testSendQueueWithObjectsMap()
    {
        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->once())
                ->method('callService')
                ->with($this->equalTo('contact'), $this->equalTo('save'), $this->equalTo(array()), $this->equalTo(array()));

        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();
        $objectMock->expects($this->once())
                ->method('getTulipParameters')
                ->willReturn(array());

        $objectsMap = array(
            get_class($objectMock) => array(
                'service' => 'contact',
                'action' => 'save',
            ),
        );

        $queueManager = new QueueManager($clientMock, $objectsMap);
        $queueManager->queueObject($objectMock);
        $queueManager->sendQueue();
    }
}
