<?php

namespace ConnectHolland\TulipAPIBundle\EventListener;

use ConnectHolland\TulipAPIBundle\Model\TulipObjectInterface;
use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

/**
 * TulipAPIQueueSubscriber queues valid Doctrine entities for sending to Tulip.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class TulipAPIQueueSubscriber implements EventSubscriber
{
    /**
     * The QueueManager instance.
     *
     * @var QueueManager
     */
    private $queueManager;

    /**
     * Constructs a new TulipAPIQueueSubscriber instance.
     *
     * @param QueueManager $queueManager
     */
    public function __construct(QueueManager $queueManager)
    {
        $this->queueManager = $queueManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'postUpdate',
            'postPersist',
        );
    }

    /**
     * Adds the object to the Tulip API queue manager for sending to Tulip when the object implements the TulipObjectInterface.
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->postPersist($args);
    }

    /**
     * Adds the object to the Tulip API queue manager for sending to Tulip when the object implements the TulipObjectInterface.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $objectChangeset = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($object);
        if ($object instanceof TulipObjectInterface && (count($objectChangeset) > 1 || isset($objectChangeset['tulipId']) === false)) {
            $this->queueManager->queueObject($object);
        }
    }
}
