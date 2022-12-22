<?php

namespace Integrated\Bundle\ContentBundle\Services;

final class CombinedFlusher implements Flusher
{
    private array $flushers;

    public function __construct(Flusher ...$flushers)
    {
        $this->flushers = $flushers;
    }

    public function flush(): void
    {
        foreach ($this->flushers as $flusher) {
            $flusher->flush();
        }
    }
}
