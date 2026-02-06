<?php

declare(strict_types=1);

namespace GildedRose\ItemUpdater;

use GildedRose\Item;

interface ItemUpdaterInterface
{
    public function update(Item $item): void;
}
