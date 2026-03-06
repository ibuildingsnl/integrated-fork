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

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Bulk\Action\HandlerInterface;
use Integrated\Common\Content\ContentInterface;

class CanonicalHandler implements HandlerInterface
{
    /** @var string|null */
    private $source;

    /** @var string|null */
    private $sourceUrl;

    public function __construct(?string $source = null, ?string $sourceUrl = null)
    {
        $this->source = $source;
        $this->sourceUrl = $sourceUrl;
    }

    public function execute(ContentInterface $content): void
    {
        if (!$content instanceof Content) {
            return;
        }

        if (method_exists($content, 'setSource')) {
            $content->setSource($this->source);
        }

        if (method_exists($content, 'setSourceUrl')) {
            $content->setSourceUrl($this->sourceUrl);
        }
    }
}
