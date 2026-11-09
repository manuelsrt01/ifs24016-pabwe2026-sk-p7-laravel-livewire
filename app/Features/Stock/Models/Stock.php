<?php

namespace App\Features\Stock\Models;

use App\Features\Stock\Enums\StockStatus;
use App\Models\User;
use Database\Factories\StockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'sku', 'description', 'quantity', 'unit',
        'price', 'min_stock', 'status', 'cover_image_path',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'min_stock' => 'integer',
            'price' => 'decimal:2',
            'status' => StockStatus::class,
        ];
    }

    protected static function booted(): void
    {
        // Status selalu konsisten dengan quantity & min_stock (create, edit, adjust, import, AI).
        static::saving(function (Stock $stock): void {
            $stock->status = StockStatus::fromQuantity((int) $stock->quantity, (int) $stock->min_stock);
        });
    }

    protected static function newFactory(): StockFactory
    {
        return StockFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image_path ? Storage::disk('public')->url($this->cover_image_path) : null;
    }

    public function descriptionHtml(): string
    {
        return (string) Str::markdown((string) $this->description, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
