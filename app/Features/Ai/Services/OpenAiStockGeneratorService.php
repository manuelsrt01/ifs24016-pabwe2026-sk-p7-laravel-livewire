<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedStockItem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiStockGeneratorService implements AiClientInterface
{
    public function generate(string $prompt, int $count = 5): array
    {
        $apiKey = (string) config('openai.api_key');

        if ($apiKey === '') {
            throw new RuntimeException('OPENAI_API_KEY belum dikonfigurasi. Isi di file .env lalu jalankan "php artisan config:clear".');
        }

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout((int) config('openai.timeout'))
                ->post(rtrim((string) config('openai.base_url'), '/').'/chat/completions', [
                    'model' => config('openai.model'),
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt($count)],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Tidak dapat terhubung ke layanan OpenAI. Periksa koneksi internet Anda.', 0, $e);
        }

        if ($response->failed()) {
            throw new RuntimeException('Layanan OpenAI mengembalikan kesalahan (HTTP '.$response->status().').');
        }

        $decoded = json_decode((string) $response->json('choices.0.message.content'), true);
        $rows = is_array($decoded['items'] ?? null) ? $decoded['items'] : [];

        $items = array_values(array_filter(array_map(
            fn ($row) => is_array($row) ? GeneratedStockItem::fromArray($row) : null,
            $rows
        )));

        if ($items === []) {
            throw new RuntimeException('AI tidak menghasilkan data stok yang valid. Coba perjelas deskripsi kebutuhan Anda.');
        }

        return array_slice($items, 0, $count);
    }

    private function systemPrompt(int $count): string
    {
        return 'Anda asisten inventaris. Berdasarkan kebutuhan pengguna, buat maksimal '.$count.' item stok barang. '
            .'Balas HANYA JSON: {"items":[{"name":string,"sku":string,"description":string (Markdown singkat),'
            .'"quantity":integer>=0,"unit":string,"price":number (rupiah),"min_stock":integer>=0}]}.';
    }
}
