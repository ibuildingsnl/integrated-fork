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

use Integrated\Common\Bulk\BulkActionInterface;
use Integrated\Common\Bulk\Form\ActionMatcherInterface;

class BulkActionOptionMatcher implements ActionMatcherInterface
{
    /**
     * @var string
     */
    private $handler;

    /**
     * @var string
     */
    private $option;

    public function __construct($handler, $option)
    {
        $this->handler = (string) $handler;
        $this->option = (string) $option;
    }

    public function match(BulkActionInterface $action)
    {
        if ($action->getHandler() !== $this->handler) {
            return false;
        }

        return \array_key_exists($this->option, $action->getOptions());
    }
}

