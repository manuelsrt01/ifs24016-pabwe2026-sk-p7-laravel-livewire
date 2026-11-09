<?php

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedStockItem;

interface AiClientInterface
{
    /**
     * @return array<int, GeneratedStockItem>
     *
     * @throws \RuntimeException jika konfigurasi/API gagal (pesan aman ditampilkan ke pengguna)
     */
    public function generate(string $prompt, int $count = 5): array;
}
