<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\PageBuilder\V2\Validation;

final class LayoutPayloadValidator
{
    private string $schemaDirectory;
    /**
     * @var array<string, bool>
     */
    private array $knownComponentTypes = [];

    public function __construct(string $schemaDirectory)
    {
        $this->schemaDirectory = rtrim($schemaDirectory, '/');
        $this->knownComponentTypes = $this->loadKnownComponentTypes();
    }

    /**
     * @return array<int, LayoutValidationError>
     */
    public function validate(array $payload, string $activeTheme): array
    {
        $errors = [];

        if (!isset($payload['root']) || !\is_array($payload['root'])) {
            $errors[] = new LayoutValidationError('/root', 'required', 'Root component is required');

            return $errors;
        }

        $this->validateNode($payload['root'], '/root', true, $errors);

        return $errors;
    }

    /**
     * @param array<string, mixed>              $node
     * @param array<int, LayoutValidationError> $errors
     */
    private function validateNode(array $node, string $path, bool $isRoot, array &$errors): void
    {
        $type = trim((string) ($node['type'] ?? ''));
        if ($type === '') {
            $errors[] = new LayoutValidationError($path.'/type', 'required', 'Component type is required');

            return;
        }

        if ($isRoot && $type !== 'container') {
            $errors[] = new LayoutValidationError($path.'/type', 'invalid_type', 'Root component type must be "container"');
        }

        if (!isset($this->knownComponentTypes[$type])) {
            $errors[] = new LayoutValidationError($path.'/type', 'unsupported_component', sprintf('Unsupported component type "%s"', $type));

            return;
        }

        if ($type === 'container') {
            if (!\array_key_exists('children', $node)) {
                $errors[] = new LayoutValidationError($path.'/children', 'required', 'Container children are required');

                return;
            }

            if (!\is_array($node['children'])) {
                $errors[] = new LayoutValidationError($path.'/children', 'type', 'Container children must be an array');

                return;
            }

            foreach ($node['children'] as $index => $child) {
                $childPath = sprintf('%s/children/%d', $path, $index);
                if (!\is_array($child)) {
                    $errors[] = new LayoutValidationError($childPath, 'type', 'Child component must be an object');

                    continue;
                }

                $this->validateNode($child, $childPath, false, $errors);
            }

            return;
        }

        if ($type === 'block_ref') {
            $props = $node['props'] ?? null;
            if (!\is_array($props)) {
                $errors[] = new LayoutValidationError($path.'/props', 'required', 'Block reference props are required');

                return;
            }

            $blockId = trim((string) ($props['blockId'] ?? ''));
            if ($blockId === '') {
                $errors[] = new LayoutValidationError($path.'/props/blockId', 'required', 'Block reference requires a non-empty blockId');
            }
        }
    }

    /**
     * @return array<string, bool>
     */
    private function loadKnownComponentTypes(): array
    {
        $known = [
            'container' => true,
            'block_ref' => true,
        ];

        $schemaFiles = glob($this->schemaDirectory.'/components/*.schema.json');
        if ($schemaFiles === false) {
            return $known;
        }

        foreach ($schemaFiles as $schemaFile) {
            $decoded = json_decode((string) file_get_contents($schemaFile), true);
            if (!\is_array($decoded)) {
                continue;
            }

            $type = $decoded['properties']['type']['const'] ?? null;
            if (!\is_string($type)) {
                continue;
            }

            $type = trim($type);
            if ($type === '') {
                continue;
            }

            $known[$type] = true;
        }

        return $known;
    }
}
