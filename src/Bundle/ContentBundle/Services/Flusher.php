<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Services\Exception\StorageException;

interface Flusher
{
    /**
     * Saves the persisted changes to the data store.
     *
     * @return void
     *
     * @throws StorageException when something goes wrong with saving the content
     */
    public function flush(): void;
}
