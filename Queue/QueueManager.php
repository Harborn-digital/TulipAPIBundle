<?php

namespace ConnectHolland\TulipAPIBundle\Queue;

use ConnectHolland\TulipAPI\Client;
use ConnectHolland\TulipAPI\ResponseParser;
use ConnectHolland\TulipAPIBundle\Model\TulipObjectInterface;
use ConnectHolland\TulipAPIBundle\Model\TulipUploadObjectInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
     * The file upload path.
     *
     * @var string|null
     */
    private $fileUploadPath;

    /**
     * The objects queued for sending to Tulip.
     *
     * @var TulipObjectInterface[]
     */
    private $queuedObjects = array();

    /**
     * The array with results of the sent queue.
     *
     * @var array
     */
    private $queueResults = array();

    /**
     * Constructs a new QueueManager instance.
     *
     * @param Client      $client
     * @param array       $objectsMap
     * @param string|null $fileUploadPath
     */
    public function __construct(Client $client, array $objectsMap = array(), $fileUploadPath = null)
    {
        $this->client = $client;
        $this->fileUploadPath = $fileUploadPath;
        $this->objectsMap = $objectsMap;
    }

    /**
     * Queues an object.
     *
     * @param TulipObjectInterface $object
     */
    public function queueObject(TulipObjectInterface $object)
    {
        if ($object instanceof TulipUploadObjectInterface) {
            $object->setFileUploadPath($this->fileUploadPath);
        }

        $this->queuedObjects[] = $object;
    }

    /**
     * Sends the objects in the queue.
     *
     * @param ObjectManager $objectManager
     */
    public function sendQueue(ObjectManager $objectManager)
    {
        if (parse_url($this->client->getServiceUrl('', ''), PHP_URL_HOST) === null) {
            return;
        }

        while ($queuedObject = array_shift($this->queuedObjects)) {
            $objectSettings = $this->getObjectSettings(get_class($queuedObject));

            $parameters = $queuedObject->getTulipParameters();
            $this->convertArrayParameters($parameters);

            $files = array();
            if ($queuedObject instanceof TulipUploadObjectInterface) {
                $files = $queuedObject->getTulipUploads();
            }

            $exception = null;
            try {
                $response = $this->client->callService($objectSettings['service'], $objectSettings['action'], $parameters, $files);

                $responseParser = new ResponseParser($response);
                if (($tulipId = $responseParser->getDOMDocument()->documentElement->getElementsByTagName('id')->item(0)->nodeValue) && !empty($tulipId)) {
                    $queuedObject->setTulipId($tulipId);

                    $objectManager->persist($queuedObject);
                }
            } catch (RequestException $exception) {
                $response = $exception->getResponse();
            }

            $this->queueResults[] = array(
                'url' => $this->client->getServiceUrl($objectSettings['service'], $objectSettings['action']),
                'parameters' => $parameters,
                'response' => $response,
                'response_body' => strval($response->getBody()),
                'exception' => $exception,
            );
        }

        $objectManager->flush();
    }

    /**
     * Returns the array with results of the sent queue.
     *
     * @return array
     */
    public function getQueueResults()
    {
        return $this->queueResults;
    }

    /**
     * Returns the object settings based on the class name.
     *
     * @param string $className
     *
     * @return array
     */
    private function getObjectSettings($className)
    {
        foreach ($this->objectsMap as $mapClass => $parameters) {
            if ($mapClass === $className || is_subclass_of($className, $mapClass)) {
                return $parameters;
            }
        }

        $reflectionClass = new ReflectionClass($className);

        return array(
            'service' => strtolower($reflectionClass->getShortName()),
            'action' => 'save',
        );
    }

    /**
     * Converts arrays in the parameters to be sendable by the Tulip API client.
     *
     * @param array $parameters
     */
    private function convertArrayParameters(array &$parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $values = array_values($value);
                foreach ($values as $i => $value) {
                    $parameters[$key.'['.$i.']'] = $value;
                }

                unset($parameters[$key]);
            }
        }
    }
}
