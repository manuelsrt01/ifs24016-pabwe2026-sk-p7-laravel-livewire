<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedStockItem;
use PHPUnit\Framework\TestCase;

class GeneratedStockItemTest extends TestCase
{
    public function test_from_array_normalizes_values(): void
    {
        $item = GeneratedStockItem::fromArray([
            'name' => '  Kursi  ', 'sku' => ' K-1 ', 'description' => ' enak ', 'quantity' => -4,
            'unit' => ' unit ', 'price' => '1500.456', 'min_stock' => '-1',
        ]);

        $this->assertSame('Kursi', $item->name);
        $this->assertSame('K-1', $item->sku);
        $this->assertSame('enak', $item->description);
        $this->assertSame(0, $item->quantity);
        $this->assertSame('unit', $item->unit);
        $this->assertSame(1500.46, $item->price);
        $this->assertSame(0, $item->minStock);
    }

    public function test_defaults_and_nulls(): void
    {
        $item = GeneratedStockItem::fromArray(['name' => 'Meja', 'price' => 'abc']);

        $this->assertNull($item->sku);
        $this->assertNull($item->description);
        $this->assertNull($item->price);
        $this->assertSame('pcs', $item->unit);
        $this->assertSame(5, $item->minStock);
    }

    public function test_empty_name_returns_null_and_long_name_truncated(): void
    {
        $this->assertNull(GeneratedStockItem::fromArray(['name' => '   ']));
        $this->assertSame(255, mb_strlen(GeneratedStockItem::fromArray(['name' => str_repeat('a', 400)])->name));
    }

    public function test_to_array(): void
    {
        $array = (new GeneratedStockItem('A', 'S', 'D', 3, 'pcs', 10.5, 2))->toArray();

        $this->assertSame(['name' => 'A', 'sku' => 'S', 'description' => 'D', 'quantity' => 3, 'unit' => 'pcs', 'price' => 10.5, 'min_stock' => 2], $array);
    }
}
