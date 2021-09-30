<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Content\Extension;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
final class Events
{
    private function __construct()
    {
    }

    /**
     * @var string
     */
    public const METADATA = 'extension.metadata';

    /**
     * @var string
     */
    public const PRE_READ = 'extension.read.pre';

    /**
     * @var string
     */
    public const POST_READ = 'extension.read.post';

    /**
     * @var string
     */
    public const PRE_CREATE = 'extension.create.pre';

    /**
     * @var string
     */
    public const POST_CREATE = 'extension.create.post';

    /**
     * @var string
     */
    public const PRE_UPDATE = 'extension.update.pre';

    /**
     * @var string
     */
    public const POST_UPDATE = 'extension.update.post';

    /**
     * @var string
     */
    public const PRE_DELETE = 'extension.delete.pre';

    /**
     * @var string
     */
    public const POST_DELETE = 'extension.delete.post';
}
