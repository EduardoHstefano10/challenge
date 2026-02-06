<?php

declare(strict_types=1);

namespace GildedRose\ItemUpdater;

use GildedRose\Item;

final class NormalItemUpdater implements ItemUpdaterInterface
{
    private const MIN_QUALITY = 0;

    public function update(Item $item): void
    {
        $this->decreaseQuality($item);
        $item->sellIn--;

        if ($item->sellIn < 0) {
            $this->decreaseQuality($item);
        }
    }

    private function decreaseQuality(Item $item, int $amount = 1): void
    {
        $item->quality = max(self::MIN_QUALITY, $item->quality - $amount);
    }
}
