<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\PageBuilder\V2\Validation;

final class LayoutPayloadValidator
{
    private string $schemaDirectory;

    public function __construct(string $schemaDirectory)
    {
        $this->schemaDirectory = rtrim($schemaDirectory, '/');
    }

    /**
     * @return array<int, LayoutValidationError>
     */
    public function validate(array $payload, string $activeTheme): array
    {
        $errors = [];

        if (!isset($payload['root']) || !\is_array($payload['root'])) {
            $errors[] = new LayoutValidationError('/root', 'required', 'Root component is required');
        }

        return $errors;
    }
}

