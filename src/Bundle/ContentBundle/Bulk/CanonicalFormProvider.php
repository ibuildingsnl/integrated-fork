<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Bulk;

use Integrated\Common\Bulk\Form\Config;
use Integrated\Common\Bulk\Form\ConfigProviderInterface;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionCanonicalType;

class CanonicalFormProvider implements ConfigProviderInterface
{
    /**
     * @var BulkCapabilityResolver
     */
    private $resolver;

    public function __construct(BulkCapabilityResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function getConfig(array $content)
    {
        if (!$this->resolver->supports($content, 'canonical')) {
            return [];
        }

        return [
            new Config(
                CanonicalHandler::class,
                'canonical',
                BulkActionCanonicalType::class,
                [
                    'canonical_handler' => CanonicalHandler::class,
                    'label' => 'Source',
                ],
                new BulkActionOptionMatcher(CanonicalHandler::class, 'sourceUrl')
            ),
        ];
    }
}
