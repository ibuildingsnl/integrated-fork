<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Security;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
final class Permissions
{
    /**
     * @var string
     */
    public const VIEW = 'view';

    /**
     * @var string
     */
    public const CREATE = 'create';

    /**
     * @var string
     */
    public const EDIT = 'edit';

    /**
     * @var string
     */
    public const DELETE = 'delete';
}
