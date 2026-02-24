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

use Integrated\Bundle\ContentBundle\Form\Type\BulkActionPublishWindowType;
use Integrated\Common\Bulk\Form\Config;
use Integrated\Common\Bulk\Form\ConfigProviderInterface;

class PublishWindowFormProvider implements ConfigProviderInterface
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
        if (!$this->resolver->supports($content, 'publishTime')) {
            return [];
        }

        return [
            new Config(
                PublishWindowHandler::class,
                'publishTime',
                BulkActionPublishWindowType::class,
                [
                    'publish_window_handler' => PublishWindowHandler::class,
                    'label' => 'Publication window',
                ],
                new BulkActionOptionMatcher(PublishWindowHandler::class, 'startDate')
            ),
        ];
    }
}
