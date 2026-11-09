<?php

namespace Tests\Unit\Services;

use App\Features\Ai\DTO\GeneratedStockItem;
use App\Features\Ai\Services\OpenAiStockGeneratorService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OpenAiStockGeneratorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['openai.api_key' => 'sk-test', 'openai.base_url' => 'https://api.openai.com/v1', 'openai.timeout' => 5]);
    }

    private function reply(array $payload): array
    {
        return ['choices' => [['message' => ['content' => json_encode($payload)]]]];
    }

    public function test_returns_dto_items_and_sends_auth(): void
    {
        Http::fake(['api.openai.com/*' => Http::response($this->reply(['items' => [
            ['name' => 'Kursi', 'sku' => 'K-1', 'quantity' => 4, 'unit' => 'unit', 'price' => 1000, 'min_stock' => 2],
            ['name' => '', 'quantity' => 1],
            'bukan-array',
            ['name' => 'Meja'],
        ]]))]);

        $items = (new OpenAiStockGeneratorService)->generate('perabot', 5);

        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(GeneratedStockItem::class, $items);
        Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer sk-test')
            && str_ends_with($r->url(), '/chat/completions')
            && $r['response_format']['type'] === 'json_object');
    }

    public function test_limits_to_requested_count(): void
    {
        Http::fake(['*' => Http::response($this->reply(['items' => [['name' => 'A'], ['name' => 'B'], ['name' => 'C']]]))]);

        $this->assertCount(2, (new OpenAiStockGeneratorService)->generate('x', 2));
    }

    public function test_missing_api_key_gives_configuration_message(): void
    {
        config(['openai.api_key' => null]);
        Http::fake();

        try {
            (new OpenAiStockGeneratorService)->generate('x');
            $this->fail('Harus melempar RuntimeException');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('OPENAI_API_KEY belum dikonfigurasi', $e->getMessage());
        }

        Http::assertNothingSent();
    }

    public function test_http_error_is_wrapped(): void
    {
        Http::fake(['*' => Http::response(['error' => 'x'], 500)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('HTTP 500');
        (new OpenAiStockGeneratorService)->generate('x');
    }

    public function test_connection_failure_is_wrapped(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tidak dapat terhubung');
        (new OpenAiStockGeneratorService)->generate('x');
    }

    public function test_invalid_or_empty_content_is_rejected(): void
    {
        Http::fake(['*' => Http::response(['choices' => [['message' => ['content' => 'bukan json']]]])]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tidak menghasilkan data stok');
        (new OpenAiStockGeneratorService)->generate('x');
    }
}
