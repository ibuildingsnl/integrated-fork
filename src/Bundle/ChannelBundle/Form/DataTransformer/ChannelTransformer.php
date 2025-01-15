<?php

namespace Integrated\Bundle\ChannelBundle\Form\DataTransformer;

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\Form\DataTransformerInterface;

class ChannelTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly ChannelRepository $repository,
        private readonly bool $multiple = false,
    ) {
    }

    public function transform($value): mixed
    {
        if (!$this->multiple) {
            return $this->repository->findOneBy(['id' => $value]);
        }

        if (!\is_array($value)) {
            return [];
        }

        return $this->repository->findByIds($value);
    }

    public function reverseTransform($value): mixed
    {
        if (!$this->multiple) {
            if ($value instanceof ChannelInterface) {
                return $value->getId();
            }

            return null;
        }

        if (!\is_array($value)) {
            return [];
        }

        $values = [];
        foreach ($value as $channel) {
            if (!$channel instanceof ChannelInterface) {
                continue;
            }

            $values[] = $channel->getId();
        }

        return $values;
    }
}
