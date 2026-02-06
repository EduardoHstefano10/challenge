<?php

declare(strict_types=1);

namespace GildedRose;

use GildedRose\ItemUpdater\AgedBrieUpdater;
use GildedRose\ItemUpdater\BackstagePassUpdater;
use GildedRose\ItemUpdater\ConjuredItemUpdater;
use GildedRose\ItemUpdater\ItemUpdaterInterface;
use GildedRose\ItemUpdater\NormalItemUpdater;
use GildedRose\ItemUpdater\SulfurasUpdater;

final class GildedRose
{
    /**
     * @var array<string, class-string<ItemUpdaterInterface>>
     */
    private const ITEM_UPDATERS = [
        'Aged Brie' => AgedBrieUpdater::class,
        'Backstage passes to a TAFKAL80ETC concert' => BackstagePassUpdater::class,
        'Sulfuras, Hand of Ragnaros' => SulfurasUpdater::class,
    ];

    /**
     * @param Item[] $items
     */
    public function __construct(
        private array $items
    ) {
    }

    public function updateQuality(): void
    {
        foreach ($this->items as $item) {
            $updater = $this->resolveUpdater($item);
            $updater->update($item);
        }
    }

    private function resolveUpdater(Item $item): ItemUpdaterInterface
    {
        if (str_starts_with($item->name, 'Conjured')) {
            return new ConjuredItemUpdater();
        }

        $updaterClass = self::ITEM_UPDATERS[$item->name] ?? null;

        if ($updaterClass !== null) {
            return new $updaterClass();
        }

        return new NormalItemUpdater();
    }
}
