<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\PageBuilder\V2\Validation;

final class LayoutValidationError
{
    public string $path;
    public string $code;
    public string $message;
    public string $severity;

    public function __construct(string $path, string $code, string $message, string $severity = 'error')
    {
        $this->path = $path;
        $this->code = $code;
        $this->message = $message;
        $this->severity = $severity;
    }

    /**
     * @return array{path: string, code: string, message: string, severity: string}
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'code' => $this->code,
            'message' => $this->message,
            'severity' => $this->severity,
        ];
    }
}

