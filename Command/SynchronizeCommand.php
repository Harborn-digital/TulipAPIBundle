<?php

namespace ConnectHolland\TulipAPIBundle\Command;

use ConnectHolland\TulipAPIBundle\Model\TulipObjectInterface;
use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Synchronizes objects to Tulip.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class SynchronizeCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('tulip:synchronize')
            ->setDescription('Synchronizes objects to Tulip.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $url = $this->getContainer()
            ->getParameter('tulip_api.url');

        $io->section('Preparing synchronization of all objects to: '.$url);

        $objectClassNames = array_keys($this->getContainer()->getParameter('tulip_api.objects'));
        foreach ($objectClassNames as $objectClassName) {
            if (is_subclass_of($objectClassName, TulipObjectInterface::class)) {
                $io->title(sprintf('Synchronizing "%s" objects.', $objectClassName));

                $queueManager = $this->getContainer()
                    ->get('tulip_api.queue_manager');
                /* @var $queueManager QueueManager */

                $entityManager = $this->getContainer()
                    ->get('doctrine')
                    ->getManager();
                /* @var $entityManager EntityManager */
                $count = $entityManager->createQueryBuilder()
                    ->select('count(o.id)')
                    ->from($objectClassName, 'o')
                    ->getQuery()
                    ->getSingleScalarResult();

                $query = $entityManager->createQueryBuilder()
                    ->select('o')
                    ->from($objectClassName, 'o')
                    ->getQuery();

                $io->progressStart($count);

                $result = $query->iterate();
                foreach ($result as $row) {
                    $queueManager->queueObject($row[0]);
                    $queueManager->sendQueue($entityManager);

                    $io->progressAdvance();
                }

                $io->progressFinish();
            }
        }

        $io->success('Synchronized all objects.');
    }
}
