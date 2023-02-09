<?php

namespace Integrated\Common\Services;

use Integrated\Bundle\ContentBundle\Services\Exception\FlushingException;

interface Flusher
{
    /**
     * Saves the persisted changes to the data store.
     *
     * @throws FlushingException when something goes wrong with saving the content
     */
    public function flush(): void;
}
