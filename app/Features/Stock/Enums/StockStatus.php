<?php

namespace App\Features\Stock\Enums;

enum StockStatus: string
{
    case Available = 'available';
    case LowStock = 'low_stock';
    case OutOfStock = 'out_of_stock';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::LowStock => 'Stok Menipis',
            self::OutOfStock => 'Habis',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Available => 'bg-green-50 text-green-800 ring-1 ring-inset ring-green-600/25',
            self::LowStock => 'bg-yellow-50 text-yellow-800 ring-1 ring-inset ring-yellow-600/30',
            self::OutOfStock => 'bg-red-50 text-red-800 ring-1 ring-inset ring-red-600/25',
        };
    }

    /**
     * Aturan tunggal penentu status: habis (<= 0), menipis (<= min_stock), selain itu tersedia.
     */
    public static function fromQuantity(int $quantity, int $minStock): self
    {
        if ($quantity <= 0) {
            return self::OutOfStock;
        }

        if ($quantity <= $minStock) {
            return self::LowStock;
        }

        return self::Available;
    }
}
