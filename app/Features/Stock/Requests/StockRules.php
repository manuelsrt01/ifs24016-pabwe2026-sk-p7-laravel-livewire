<?php

namespace App\Features\Stock\Requests;

final class StockRules
{
    /** Aturan form create/edit (Livewire). */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:5000'],
            'quantity' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'unit' => ['required', 'string', 'max:20'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'min_stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /** Aturan baris Excel: unit & min_stock boleh kosong (diberi default). */
    public static function importRules(): array
    {
        $rules = self::rules();
        unset($rules['cover']);
        $rules['unit'] = ['nullable', 'string', 'max:20'];
        $rules['min_stock'] = ['nullable', 'integer', 'min:0', 'max:2147483647'];

        return $rules;
    }
}
