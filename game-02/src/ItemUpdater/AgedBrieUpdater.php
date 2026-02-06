<?php

declare(strict_types=1);

namespace GildedRose\ItemUpdater;

use GildedRose\Item;

final class AgedBrieUpdater implements ItemUpdaterInterface
{
    private const MAX_QUALITY = 50;

    public function update(Item $item): void
    {
        $this->increaseQuality($item);
        $item->sellIn--;

        if ($item->sellIn < 0) {
            $this->increaseQuality($item);
        }
    }

    private function increaseQuality(Item $item): void
    {
        if ($item->quality < self::MAX_QUALITY) {
            $item->quality++;
        }
    }
}
