<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Storage\Database;

use Doctrine\ODM\MongoDB\Iterator\Iterator;
use MongoDB\Driver\CursorInterface;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
interface DatabaseInterface
{
    /**
     * @return CursorInterface
     */
    public function getRows();

    /**
     * @return Iterator
     */
    public function getObjects();

    /**
     * @param object $object
     */
    public function saveObject($object);

    public function saveRow(array $row);
}
