<?php

namespace ConnectHolland\TulipAPIBundle\DataCollector;

use ConnectHolland\TulipAPIBundle\Queue\QueueManager;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * TulipAPIDataCollector.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class TulipAPIDataCollector extends DataCollector
{
    /**
     * The QueueManager instance.
     *
     * @var QueueManager
     */
    private $queueManager;

    /**
     * Constructs a new TulipAPIDataCollector.
     *
     * @param QueueManager $queueManager
     */
    public function __construct(QueueManager $queueManager)
    {
        $this->queueManager = $queueManager;
    }

    /**
     * Collect the processed Tulip API calls for the Symfony Profiler.
     *
     * @param Request   $request
     * @param Response  $response
     * @param Exception $exception
     */
    public function collect(Request $request, Response $response, Exception $exception = null)
    {
        $this->data = array(
            'queue_results' => $this->queueManager->getQueueResults(),
        );
    }

    /**
     * Returns the array with results of the sent queue.
     *
     * @return array
     */
    public function getQueueResults()
    {
        return $this->data['queue_results'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tulip_api';
    }

    /**
     * Reset data.
     */
    public function reset()
    {
       $this->data = null;
    }
}
