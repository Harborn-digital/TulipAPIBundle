<?php

namespace ConnectHolland\TulipAPIBundle\Model;

/**
 * TulipObjectInterface defines an object sendable to Tulip.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
interface TulipObjectInterface
{
    /**
     * Returns the data to be sent to Tulip.
     *
     * @return array
     */
    public function getTulipParameters();

    /**
     * Sets the Tulip ID returned from the API response.
     *
     * @param null|int $tulipId
     */
    public function setTulipId(?int $tulipId);
}
