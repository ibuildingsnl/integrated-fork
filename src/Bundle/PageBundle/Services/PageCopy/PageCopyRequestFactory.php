<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Services\PageCopy;

final class PageCopyRequestFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function createFromFormData(array $data): PageCopyRequest
    {
        $sourceChannelId = trim((string) ($data['sourceChannel'] ?? ''));
        $targetChannelId = trim((string) ($data['targetChannel'] ?? ''));

        if ($sourceChannelId === '' || $targetChannelId === '') {
            throw new \InvalidArgumentException('Source and target channel are required.');
        }

        $pages = \is_array($data['pages'] ?? null) ? $data['pages'] : [];
        $pageInstructions = [];

        foreach ($pages as $pageKey => $pageData) {
            if (!\is_string($pageKey) || !\is_array($pageData) || !$this->isSelected($pageData['selected'] ?? null)) {
                continue;
            }

            $pageId = $this->extractId($pageKey, 'page');
            if ($pageId === '') {
                continue;
            }

            $blockInstructions = [];
            $copyAction = $this->resolveCopyAction($pageData);
            $blocks = \is_array($pageData['blocks'] ?? null) ? $pageData['blocks'] : [];
            foreach ($blocks as $blockKey => $blockData) {
                if (!\is_string($blockKey) || !\is_array($blockData)) {
                    continue;
                }

                $blockId = $this->resolveBlockId($blockKey, $blockData);
                if ($blockId === '') {
                    continue;
                }

                $operation = trim((string) ($blockData['operation'] ?? ''));
                $targetBlockId = trim((string) ($blockData['newBlockId'] ?? ''));

                if ($operation === BlockCopyInstruction::OPERATION_CLONE && $targetBlockId === '') {
                    throw new \InvalidArgumentException(\sprintf('Missing target block id for block "%s".', $blockId));
                }

                $blockInstructions[$blockId] = new BlockCopyInstruction(
                    $blockId,
                    $operation,
                    $targetBlockId !== '' ? $targetBlockId : null
                );
            }

            $pageInstructions[$pageId] = new PageCopyInstruction(
                $pageId,
                $copyAction === PageCopyInstruction::ACTION_OVERWRITE ? PageCopyInstruction::ACTION_OVERWRITE : PageCopyInstruction::ACTION_CREATE,
                $blockInstructions
            );
        }

        return new PageCopyRequest($sourceChannelId, $targetChannelId, $pageInstructions);
    }

    private function extractId(string $key, string $prefix): string
    {
        if (!str_starts_with($key, $prefix)) {
            return '';
        }

        return trim(substr($key, \strlen($prefix)));
    }

    /**
     * @param array<string, mixed> $blockData
     */
    private function resolveBlockId(string $blockKey, array $blockData): string
    {
        if (\is_scalar($blockData['sourceBlockId'] ?? null)) {
            $blockId = trim((string) $blockData['sourceBlockId']);
            if ($blockId !== '') {
                return $blockId;
            }
        }

        return $this->extractId($blockKey, 'block_');
    }

    private function isSelected(mixed $value): bool
    {
        if (\is_bool($value)) {
            return $value;
        }

        if (!\is_scalar($value)) {
            return false;
        }

        return \in_array(strtolower(trim((string) $value)), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * @param array<string, mixed> $pageData
     */
    private function resolveCopyAction(array $pageData): string
    {
        $overwrite = $this->isSelected($pageData['overwrite'] ?? null);
        if ($overwrite) {
            return PageCopyInstruction::ACTION_OVERWRITE;
        }

        $copyAction = trim((string) ($pageData['copyAction'] ?? PageCopyInstruction::ACTION_CREATE));

        return $copyAction === PageCopyInstruction::ACTION_OVERWRITE
            ? PageCopyInstruction::ACTION_OVERWRITE
            : PageCopyInstruction::ACTION_CREATE;
    }
}
