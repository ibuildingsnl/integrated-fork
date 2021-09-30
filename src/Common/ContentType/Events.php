<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\ContentType;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
final class Events
{
    /**
     * @var string
     */
    public const CONTENT_TYPE_CREATED = 'integrated.content_type.created';

    /**
     * @var string
     */
    public const CONTENT_TYPE_UPDATED = 'integrated.content_type.updated';

    /**
     * @var string
     */
    public const CONTENT_TYPE_DELETED = 'integrated.content_type.deleted';
}
