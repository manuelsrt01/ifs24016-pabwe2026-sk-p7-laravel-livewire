<?php

namespace Tests\Unit\Enums;

use App\Features\Stock\Enums\StockStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StockStatusTest extends TestCase
{
    #[DataProvider('cases')]
    public function test_from_quantity(int $qty, int $min, StockStatus $expected): void
    {
        $this->assertSame($expected, StockStatus::fromQuantity($qty, $min));
    }

    public static function cases(): array
    {
        return [
            'nol' => [0, 5, StockStatus::OutOfStock],
            'negatif' => [-2, 5, StockStatus::OutOfStock],
            'sama dengan minimum' => [5, 5, StockStatus::LowStock],
            'di bawah minimum' => [1, 5, StockStatus::LowStock],
            'di atas minimum' => [6, 5, StockStatus::Available],
            'minimum 0 dan qty 1' => [1, 0, StockStatus::Available],
        ];
    }

    public function test_labels_and_badges(): void
    {
        foreach (StockStatus::cases() as $status) {
            $this->assertNotSame('', $status->label());
            $this->assertStringContainsString('bg-', $status->badgeClass());
        }

        $this->assertStringContainsString('green', StockStatus::Available->badgeClass());
        $this->assertStringContainsString('yellow', StockStatus::LowStock->badgeClass());
        $this->assertStringContainsString('red', StockStatus::OutOfStock->badgeClass());
    }
}
