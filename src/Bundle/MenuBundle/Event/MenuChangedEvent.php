<?php

declare(strict_types=1);

namespace Integrated\Bundle\MenuBundle\Event;

use Integrated\Bundle\MenuBundle\Document\Menu;
use Symfony\Contracts\EventDispatcher\Event;

final class MenuChangedEvent extends Event
{
    public function __construct(
        private readonly Menu $menu,
    ) {
    }

    public function getMenu(): Menu
    {
        return $this->menu;
    }
}
