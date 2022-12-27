<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Mapping\Registry;

use Integrated\Common\Form\Mapping\DriverInterface;

class DriverRegistry
{
    private array $drivers = [];

    public function addDriver(DriverInterface $driver): void
    {
        $this->drivers[] = $driver;
    }

    /**
     * @return DriverInterface[]
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }
}
