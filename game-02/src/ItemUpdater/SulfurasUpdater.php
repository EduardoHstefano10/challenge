<?php

declare(strict_types=1);

namespace GildedRose\ItemUpdater;

use GildedRose\Item;

final class SulfurasUpdater implements ItemUpdaterInterface
{
    public function update(Item $item): void
    {
        // Sulfuras is legendary: never changes quality or sellIn
    }
}
