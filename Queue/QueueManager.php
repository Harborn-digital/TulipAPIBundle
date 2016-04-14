<?php

namespace ConnectHolland\TulipAPIBundle\Queue;

use ConnectHolland\TulipAPI\Client;
use ConnectHolland\TulipAPIBundle\Model\TulipObjectInterface;
use ConnectHolland\TulipAPIBundle\Model\TulipUploadObjectInterface;
use GuzzleHttp\Exception\RequestException;
use ReflectionClass;

/**
 * QueueManager manages queuing and sending of objects to Tulip with the Tulip API.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class QueueManager
{
    /**
     * The Tulip API client.
     *
     * @var Client
     */
    private $client;

    /**
     * The object name to Tulip API service name mapping.
     *
     * @var array
     */
    private $objectsMap;

    /**
     * The objects queued for sending to Tulip.
     *
     * @var TulipObjectInterface[]
     */
    private $queuedObjects = array();

    /**
     * Constructs a new QueueManager instance.
     *
     * @param Client $client
     * @param array  $objectsMap
     */
    public function __construct(Client $client, array $objectsMap = array())
    {
        $this->client = $client;
        $this->objectsMap = $objectsMap;
    }

    /**
     * Queues an object.
     *
     * @param TulipObjectInterface $object
     */
    public function queueObject(TulipObjectInterface $object)
    {
        $this->queuedObjects[] = $object;
    }

    /**
     * Sends the objects in the queue.
     */
    public function sendQueue()
    {
        while ($queuedObject = array_shift($this->queuedObjects)) {
            $objectSettings = $this->getObjectSettings(get_class($queuedObject));

            $parameters = $queuedObject->getTulipParameters();
            $files = array();
            if ($queuedObject instanceof TulipUploadObjectInterface) {
                $files = $queuedObject->getTulipUploads();
            }

            try {
                $this->client->callService($objectSettings['service'], $objectSettings['action'], $parameters, $files);
            } catch (RequestException $exception) {
            }
        }
    }

    /**
     * Returns the object settings based on the class name.
     *
     * @return array
     */
    private function getObjectSettings($className)
    {
        if (isset($this->objectsMap[$className]) === false) {
            $reflectionClass = new ReflectionClass($className);

            $this->objectsMap[$className] = array(
                'service' => strtolower($reflectionClass->getShortName()),
                'action' => 'save',
            );
        }

        return $this->objectsMap[$className];
    }
}
