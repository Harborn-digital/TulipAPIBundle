<?php

namespace ConnectHolland\TulipAPIBundle\EventListener;

use ConnectHolland\TulipAPIBundle\Model\TulipObjectInterface;
use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

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
     * The boolean indicating that the kernel is in debug mode and queued objects are sent directly.
     *
     * @var bool
     */
    private $debug;

    /**
     * Constructs a new TulipAPIQueueSubscriber instance.
     *
     * @param QueueManager $queueManager
     * @param bool         $debug
     */
    public function __construct(QueueManager $queueManager, $debug = false)
    {
        $this->queueManager = $queueManager;
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::postPersist,
            Events::postUpdate,
        );
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

        unset($objectChangeset['createdAt'], $objectChangeset['updatedAt']);

        if ($object instanceof TulipObjectInterface && (count($objectChangeset) > 1 || isset($objectChangeset['tulipId']) === false)) {
            $this->queueManager->queueObject($object);

            if ($this->debug === true) {
                $this->queueManager->sendQueue($args->getObjectManager());
            }
        }
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
}
