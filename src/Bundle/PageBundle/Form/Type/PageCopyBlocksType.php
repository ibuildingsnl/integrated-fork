<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageCopyBlocksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $usedNames = [];

        foreach ($options['blocks'] as $id => $block) {
            $name = $this->createSafeBlockFormName((string) $id, $usedNames);

            $builder->add($name, PageCopyBlockType::class, [
                'block' => $block,
                'channel' => $options['channel'],
                'targetChannel' => $options['targetChannel'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['blocks', 'channel', 'targetChannel']);
        $resolver->setAllowedTypes('blocks', 'array');
        $resolver->setAllowedTypes('channel', 'string');
        $resolver->setAllowedTypes('targetChannel', 'string');
    }

    /**
     * @param array<string, true> $usedNames
     */
    private function createSafeBlockFormName(string $id, array &$usedNames): string
    {
        $name = 'block_'.preg_replace('/[^A-Za-z0-9_:-]/', '_', $id);

        if (!preg_match('/^[A-Za-z0-9_]/', $name)) {
            $name = '_'.$name;
        }

        if (!isset($usedNames[$name])) {
            $usedNames[$name] = true;

            return $name;
        }

        $name .= '_'.substr(sha1($id), 0, 8);
        $usedNames[$name] = true;

        return $name;
    }
}
