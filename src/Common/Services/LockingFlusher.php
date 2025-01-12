<?php

namespace Integrated\Common\Services;

use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\ContentBundle\Services\Exception\FlushingException;
use Symfony\Component\Lock\LockFactory;

final class LockingFlusher implements Flusher
{
    public function __construct(
        private readonly LockFactory $lockFactory,
        private readonly Flusher $next,
    ) {
    }

    public function flush(): void
    {
        $lock = $this->lockFactory->createLock(ContentController::class);
        try {
            $lock->acquire(true);
            $this->next->flush();
        } catch (\Exception $exception) {
            throw FlushingException::from($exception);
        } finally {
            $lock->release();
        }
    }
}
