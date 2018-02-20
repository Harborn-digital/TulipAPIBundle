<?php

namespace ConnectHolland\TulipAPIBundle\Model;

/**
 * TulipUploadObjectInterface defines an object with file uploads sendable to Tulip.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
interface TulipUploadObjectInterface extends TulipObjectInterface
{
    /**
     * Returns the file pointer resources of files to be sent to Tulip.
     *
     * @return resource[]
     */
    public function getTulipUploads();

    /**
     * Sets the base path for file uploads.
     *
     * @param null|string $path
     */
    public function setFileUploadPath(?string $path);
}
