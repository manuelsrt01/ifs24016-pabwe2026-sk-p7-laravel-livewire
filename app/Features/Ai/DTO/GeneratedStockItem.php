<?php

namespace App\Features\Ai\DTO;

final readonly class GeneratedStockItem
{
    public function __construct(
        public string $name,
        public ?string $sku,
        public ?string $description,
        public int $quantity,
        public string $unit,
        public ?float $price,
        public int $minStock,
    ) {}

    /** Normalisasi keluaran AI; mengembalikan null jika nama kosong. */
    public static function fromArray(array $data): ?self
    {
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            return null;
        }

        $sku = trim((string) ($data['sku'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $unit = trim((string) ($data['unit'] ?? ''));
        $price = $data['price'] ?? null;

        return new self(
            name: mb_substr($name, 0, 255),
            sku: $sku === '' ? null : mb_substr($sku, 0, 100),
            description: $description === '' ? null : mb_substr($description, 0, 5000),
            quantity: max(0, (int) ($data['quantity'] ?? 0)),
            unit: $unit === '' ? 'pcs' : mb_substr($unit, 0, 20),
            price: is_numeric($price) ? max(0.0, round((float) $price, 2)) : null,
            minStock: max(0, (int) ($data['min_stock'] ?? 5)),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'price' => $this->price,
            'min_stock' => $this->minStock,
        ];
    }
}
