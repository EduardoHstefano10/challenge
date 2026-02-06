<?php

declare(strict_types=1);

namespace GildedRose\ItemUpdater;

use GildedRose\Item;

final class BackstagePassUpdater implements ItemUpdaterInterface
{
    private const MAX_QUALITY = 50;

    public function update(Item $item): void
    {
        $this->increaseQuality($item);

        if ($item->sellIn < 11) {
            $this->increaseQuality($item);
        }

        if ($item->sellIn < 6) {
            $this->increaseQuality($item);
        }

        $item->sellIn--;

        if ($item->sellIn < 0) {
            $item->quality = 0;
        }
    }

    private function increaseQuality(Item $item): void
    {
        if ($item->quality < self::MAX_QUALITY) {
            $item->quality++;
        }
    }
}
