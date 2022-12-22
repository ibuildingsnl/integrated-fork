<?php

namespace Integrated\Bundle\ContentBundle\Services;

use Doctrine\Persistence\ObjectManager;
use Integrated\Bundle\ContentBundle\Services\Exception\FlushingException;

final class DoctrineFlusher implements Flusher
{
    public function __construct(private readonly ObjectManager $doctrine)
    {
    }

    public function flush(): void
    {
        try {
            $this->doctrine->flush();
        } catch (\Exception $exception) {
            throw FlushingException::from($exception);
        }
    }
}
