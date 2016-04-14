<?php

namespace ConnectHolland\TulipAPIBundle\EventListener;

use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * TulipAPISendQueueSubscriber calls the QueueManager to send the queue.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class TulipAPISendQueueSubscriber implements EventSubscriberInterface
{
    /**
     * The QueueManager instance.
     *
     * @var QueueManager
     */
    private $queueManager;

    /**
     * The Doctrine object manager instance.
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::TERMINATE => array(
                array('onKernelTerminateSendQueue', 0),
            ),
        );
    }

    /**
     * Constructs a new TulipAPISendQueueSubscriber instance.
     *
     * @param QueueManager    $queueManager
     * @param ManagerRegistry $registry
     */
    public function __construct(QueueManager $queueManager, ManagerRegistry $registry)
    {
        $this->queueManager = $queueManager;
        $this->objectManager = $registry->getManager();
    }

    /**
     * Calls the QueueManager to send the queue.
     */
    public function onKernelTerminateSendQueue(PostResponseEvent $event)
    {
        $this->queueManager->sendQueue($this->objectManager);
    }
}
