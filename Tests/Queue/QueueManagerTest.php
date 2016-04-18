<?php

namespace ConnectHolland\TulipAPIBundle\Tests\Queue;

use ConnectHolland\TulipAPI\Client;
use ConnectHolland\TulipAPIBundle\Model\TulipObjectInterface;
use ConnectHolland\TulipAPIBundle\Model\TulipUploadObjectInterface;
use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use Doctrine\Common\Persistence\ObjectManager;
use GuzzleHttp\Psr7\Response;
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
     * Tests if QueueManager::sendQueue does nothing without a valid Tulip API URL.
     */
    public function testSendQueueWithoutValidTulipAPIUrl()
    {
        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->once())
                ->method('getServiceUrl')
                ->with($this->equalTo(''), $this->equalTo(''))
                ->willReturn('/api//');
        $clientMock->expects($this->never())
                ->method('callService');

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
                ->getMock();
        $objectManagerMock->expects($this->never())
                ->method('persist');
        $objectManagerMock->expects($this->never())
                ->method('flush');

        $queueManager = new QueueManager($clientMock);
        $queueManager->sendQueue($objectManagerMock);
    }

    /**
     * Tests if QueueManager::sendQueue does nothing without any errors when no objects are queued.
     */
    public function testSendQueueWithEmptyQueue()
    {
        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->once())
                ->method('getServiceUrl')
                ->willReturn('https://api.example.com');
        $clientMock->expects($this->never())
                ->method('callService');

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
                ->getMock();
        $objectManagerMock->expects($this->never())
                ->method('persist');
        $objectManagerMock->expects($this->once())
                ->method('flush');

        $queueManager = new QueueManager($clientMock);
        $queueManager->sendQueue($objectManagerMock);
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
        $objectMock->expects($this->once())
                ->method('setTulipId')
                ->with($this->equalTo('1'));

        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->once())
                ->method('getServiceUrl')
                ->willReturn('https://api.example.com');
        $clientMock->expects($this->once())
                ->method('callService')
                ->with($this->equalTo(strtolower(get_class($objectMock))), $this->equalTo('save'), $this->equalTo(array()), $this->equalTo(array()))
                ->willReturn(new Response(200, array(), '<?xml version="1.0" encoding="UTF-8"?><response code="1000"><result offset="0" limit="0" total="0"><object><id>1</id></object></result></response>'));

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
                ->getMock();
        $objectManagerMock->expects($this->once())
                ->method('persist')
                ->with($this->equalTo($objectMock));
        $objectManagerMock->expects($this->once())
                ->method('flush');

        $queueManager = new QueueManager($clientMock);
        $queueManager->queueObject($objectMock);
        $queueManager->sendQueue($objectManagerMock);
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
        $objectMock->expects($this->once())
                ->method('setTulipId')
                ->with($this->equalTo('1'));

        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->once())
                ->method('getServiceUrl')
                ->willReturn('https://api.example.com');
        $clientMock->expects($this->once())
                ->method('callService')
                ->with($this->equalTo(strtolower(get_class($objectMock))), $this->equalTo('save'), $this->equalTo(array()), $this->equalTo(array()))
                ->willReturn(new Response(200, array(), '<?xml version="1.0" encoding="UTF-8"?><response code="1000"><result offset="0" limit="0" total="0"><object><id>1</id></object></result></response>'));

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
                ->getMock();
        $objectManagerMock->expects($this->once())
                ->method('persist')
                ->with($this->equalTo($objectMock));
        $objectManagerMock->expects($this->once())
                ->method('flush');

        $queueManager = new QueueManager($clientMock);
        $queueManager->queueObject($objectMock);
        $queueManager->sendQueue($objectManagerMock);
    }

    /**
     * Tests if QueueManager::sendQueue calls the Tulip API client for the object in the queue.
     */
    public function testSendQueueWithObjectsMap()
    {
        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();
        $objectMock->expects($this->once())
                ->method('getTulipParameters')
                ->willReturn(array());
        $objectMock->expects($this->once())
                ->method('setTulipId')
                ->with($this->equalTo('1'));

        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->once())
                ->method('getServiceUrl')
                ->willReturn('https://api.example.com');
        $clientMock->expects($this->once())
                ->method('callService')
                ->with($this->equalTo('contact'), $this->equalTo('save'), $this->equalTo(array()), $this->equalTo(array()))
                ->willReturn(new Response(200, array(), '<?xml version="1.0" encoding="UTF-8"?><response code="1000"><result offset="0" limit="0" total="0"><object><id>1</id></object></result></response>'));

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
                ->getMock();
        $objectManagerMock->expects($this->once())
                ->method('persist')
                ->with($this->equalTo($objectMock));
        $objectManagerMock->expects($this->once())
                ->method('flush');

        $objectsMap = array(
            get_class($objectMock) => array(
                'service' => 'contact',
                'action' => 'save',
            ),
        );

        $queueManager = new QueueManager($clientMock, $objectsMap);
        $queueManager->queueObject($objectMock);
        $queueManager->sendQueue($objectManagerMock);
    }

    /**
     * Tests if QueueManager::convertArrayParameters
     */
    public function testSendQueueWithParameterArrayConversion()
    {
        $objectMock = $this->getMockBuilder(TulipObjectInterface::class)
                ->getMock();
        $objectMock->expects($this->once())
                ->method('getTulipParameters')
                ->willReturn(array('array' => array(1 => 'foo', 5 => 'bar')));
        $objectMock->expects($this->once())
                ->method('setTulipId')
                ->with($this->equalTo('1'));

        $clientMock = $this->getMockBuilder(Client::class)
                ->disableOriginalConstructor()
                ->getMock();
        $clientMock->expects($this->once())
                ->method('getServiceUrl')
                ->willReturn('https://api.example.com');
        $clientMock->expects($this->once())
                ->method('callService')
                ->with($this->equalTo(strtolower(get_class($objectMock))), $this->equalTo('save'), $this->equalTo(array('array[0]' => 'foo', 'array[1]' => 'bar')), $this->equalTo(array()))
                ->willReturn(new Response(200, array(), '<?xml version="1.0" encoding="UTF-8"?><response code="1000"><result offset="0" limit="0" total="0"><object><id>1</id></object></result></response>'));

        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)
                ->getMock();
        $objectManagerMock->expects($this->once())
                ->method('persist')
                ->with($this->equalTo($objectMock));
        $objectManagerMock->expects($this->once())
                ->method('flush');

        $queueManager = new QueueManager($clientMock);
        $queueManager->queueObject($objectMock);
        $queueManager->sendQueue($objectManagerMock);
    }
}
