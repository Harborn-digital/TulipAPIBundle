<?php

namespace ConnectHolland\TulipAPIBundle\Tests\Command;

use ConnectHolland\TulipAPIBundle\Command\SynchronizeCommand;
use ConnectHolland\TulipAPIBundle\Model\TulipObjectInterface;
use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * SynchronizeCommandTest.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class SynchronizeCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests if the SynchronizeCommand successfully executes.
     */
    public function testExecute()
    {
        $this->assertSame(0, $this->createCommandTester()->execute(array()));
    }

    /**
     * Returns a new CommandTester instance.
     *
     * @return CommandTester
     */
    private function createCommandTester()
    {
        $application = new Application();

        $command = new SynchronizeCommand();
        $command->setContainer($this->createContainerMock());

        $application->add($command);

        return new CommandTester($application->find('tulip:synchronize'));
    }

    /**
     * Returns a new mocked service container.
     *
     * @return ContainerInterface
     */
    private function createContainerMock()
    {
        $tulipObjectMock = $this->getMockBuilder(TulipObjectInterface::class)
            ->getMock();

        $queryMock = $this->getMockBuilder(AbstractQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queryMock->expects($this->once())
            ->method('getSingleScalarResult')
            ->willReturn(1);
        $queryMock->expects($this->once())
            ->method('iterate')
            ->willReturn(array(array($tulipObjectMock)));

        $queryBuilderMock = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilderMock->expects($this->exactly(2))
            ->method('select')
            ->willReturnSelf();
        $queryBuilderMock->expects($this->exactly(2))
            ->method('from')
            ->willReturnSelf();
        $queryBuilderMock->expects($this->exactly(2))
            ->method('getQuery')
            ->willReturn($queryMock);

        $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->getMock();
        $entityManagerMock->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        $doctrineMock = $this->getMockBuilder(ManagerRegistry::class)
            ->getMock();
        $doctrineMock->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManagerMock);

        $queueManagerMock = $this->getMockBuilder(QueueManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $queueManagerMock->expects($this->once())
            ->method('queueObject')
            ->with($this->equalTo($tulipObjectMock));
        $queueManagerMock->expects($this->once())
            ->method('sendQueue')
            ->with($this->equalTo($entityManagerMock));

        $containerMock = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();

        $containerMock->expects($this->exactly(2))
            ->method('getParameter')
            ->withConsecutive(
                array('tulip_api.url'),
                array('tulip_api.objects')
            )
            ->willReturnOnConsecutiveCalls(
                'https://example.com',
                array(
                    get_class($tulipObjectMock) => array(),
                )
            );

        $containerMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                array('tulip_api.queue_manager'),
                array('doctrine')
            )
            ->willReturnOnConsecutiveCalls(
                $queueManagerMock,
                $doctrineMock
            );

        return $containerMock;
    }
}
