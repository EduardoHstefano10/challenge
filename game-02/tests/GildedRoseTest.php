<?php

declare(strict_types=1);

namespace Tests;

use GildedRose\GildedRose;
use GildedRose\Item;
use PHPUnit\Framework\TestCase;

class GildedRoseTest extends TestCase
{
    // === Normal Items ===

    public function testNormalItemBeforeSellDate(): void
    {
        $items = [new Item('Normal Item', 10, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(9, $items[0]->sellIn);
        $this->assertSame(19, $items[0]->quality);
    }

    public function testNormalItemOnSellDate(): void
    {
        $items = [new Item('Normal Item', 0, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(-1, $items[0]->sellIn);
        $this->assertSame(18, $items[0]->quality);
    }

    public function testNormalItemAfterSellDate(): void
    {
        $items = [new Item('Normal Item', -1, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(-2, $items[0]->sellIn);
        $this->assertSame(18, $items[0]->quality);
    }

    public function testNormalItemQualityNeverNegative(): void
    {
        $items = [new Item('Normal Item', 10, 0)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(0, $items[0]->quality);
    }

    public function testNormalItemQualityNeverNegativeAfterSellDate(): void
    {
        $items = [new Item('Normal Item', -1, 1)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(0, $items[0]->quality);
    }

    // === Aged Brie ===

    public function testAgedBrieBeforeSellDate(): void
    {
        $items = [new Item('Aged Brie', 10, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(9, $items[0]->sellIn);
        $this->assertSame(21, $items[0]->quality);
    }

    public function testAgedBrieAfterSellDate(): void
    {
        $items = [new Item('Aged Brie', -1, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(-2, $items[0]->sellIn);
        $this->assertSame(22, $items[0]->quality);
    }

    public function testAgedBrieQualityMaxAt50(): void
    {
        $items = [new Item('Aged Brie', 10, 50)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(50, $items[0]->quality);
    }

    public function testAgedBrieQualityMaxAt50AfterSellDate(): void
    {
        $items = [new Item('Aged Brie', -1, 49)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(50, $items[0]->quality);
    }

    // === Sulfuras ===

    public function testSulfurasNeverChanges(): void
    {
        $items = [new Item('Sulfuras, Hand of Ragnaros', 0, 80)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(0, $items[0]->sellIn);
        $this->assertSame(80, $items[0]->quality);
    }

    public function testSulfurasNegativeSellIn(): void
    {
        $items = [new Item('Sulfuras, Hand of Ragnaros', -1, 80)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(-1, $items[0]->sellIn);
        $this->assertSame(80, $items[0]->quality);
    }

    // === Backstage Passes ===

    public function testBackstagePassesMoreThan10Days(): void
    {
        $items = [new Item('Backstage passes to a TAFKAL80ETC concert', 15, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(14, $items[0]->sellIn);
        $this->assertSame(21, $items[0]->quality);
    }

    public function testBackstagePasses10DaysOrLess(): void
    {
        $items = [new Item('Backstage passes to a TAFKAL80ETC concert', 10, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(9, $items[0]->sellIn);
        $this->assertSame(22, $items[0]->quality);
    }

    public function testBackstagePasses5DaysOrLess(): void
    {
        $items = [new Item('Backstage passes to a TAFKAL80ETC concert', 5, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(4, $items[0]->sellIn);
        $this->assertSame(23, $items[0]->quality);
    }

    public function testBackstagePassesAfterConcert(): void
    {
        $items = [new Item('Backstage passes to a TAFKAL80ETC concert', 0, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(-1, $items[0]->sellIn);
        $this->assertSame(0, $items[0]->quality);
    }

    public function testBackstagePassesQualityMaxAt50(): void
    {
        $items = [new Item('Backstage passes to a TAFKAL80ETC concert', 5, 49)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(50, $items[0]->quality);
    }

    public function testBackstagePasses10DaysQualityMaxAt50(): void
    {
        $items = [new Item('Backstage passes to a TAFKAL80ETC concert', 10, 49)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(50, $items[0]->quality);
    }

    // === Conjured Items ===

    public function testConjuredItemBeforeSellDate(): void
    {
        $items = [new Item('Conjured Mana Cake', 10, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(9, $items[0]->sellIn);
        $this->assertSame(18, $items[0]->quality);
    }

    public function testConjuredItemAfterSellDate(): void
    {
        $items = [new Item('Conjured Mana Cake', -1, 20)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(-2, $items[0]->sellIn);
        $this->assertSame(16, $items[0]->quality);
    }

    public function testConjuredItemQualityNeverNegative(): void
    {
        $items = [new Item('Conjured Mana Cake', 10, 0)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(0, $items[0]->quality);
    }

    public function testConjuredItemQualityNeverNegativeAfterSellDate(): void
    {
        $items = [new Item('Conjured Mana Cake', -1, 1)];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(0, $items[0]->quality);
    }

    // === Multiple Items ===

    public function testMultipleItems(): void
    {
        $items = [
            new Item('+5 Dexterity Vest', 10, 20),
            new Item('Aged Brie', 2, 0),
            new Item('Sulfuras, Hand of Ragnaros', 0, 80),
            new Item('Backstage passes to a TAFKAL80ETC concert', 15, 20),
        ];
        $app = new GildedRose($items);
        $app->updateQuality();

        $this->assertSame(19, $items[0]->quality);
        $this->assertSame(1, $items[1]->quality);
        $this->assertSame(80, $items[2]->quality);
        $this->assertSame(21, $items[3]->quality);
    }
}
