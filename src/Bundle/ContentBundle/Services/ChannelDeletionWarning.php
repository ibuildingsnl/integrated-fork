<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Services;

final class ChannelDeletionWarning
{
    public function __construct(
        private readonly string $step,
        private readonly string $documentClass,
        private readonly string $documentId,
        private readonly string $message,
        private readonly ?string $exceptionClass = null,
    ) {
    }

    public function getStep(): string
    {
        return $this->step;
    }

    public function getDocumentClass(): string
    {
        return $this->documentClass;
    }

    public function getDocumentId(): string
    {
        return $this->documentId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getExceptionClass(): ?string
    {
        return $this->exceptionClass;
    }
}
