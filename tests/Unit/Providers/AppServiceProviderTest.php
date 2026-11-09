<?php

namespace Tests\Unit\Providers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\Services\OpenAiStockGeneratorService;
use App\Features\Stock\Contracts\StockRepositoryInterface;
use App\Features\Stock\Repositories\StockRepository;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_bindings(): void
    {
        $this->assertInstanceOf(StockRepository::class, app(StockRepositoryInterface::class));
        $this->assertInstanceOf(OpenAiStockGeneratorService::class, app(AiClientInterface::class));
    }

    public function test_rate_limiter_is_1000_per_second_for_guest_and_user(): void
    {
        $limiter = RateLimiter::limiter('1000-per-second');
        $this->assertNotNull($limiter);

        $guest = $limiter(Request::create('/', server: ['REMOTE_ADDR' => '10.0.0.1']));
        $this->assertSame(1000, $guest->maxAttempts);
        $this->assertSame(1, $guest->decaySeconds);

        $request = Request::create('/');
        $request->setUserResolver(fn () => User::factory()->make(['id' => 7]));
        $this->assertSame('7', (string) $limiter($request)->key);
    }

    public function test_force_https_enabled(): void
    {
        config(['app.force_https' => true]);
        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', url('/stocks'));
    }

    public function test_force_https_disabled_by_default(): void
    {
        config(['app.force_https' => false]);
        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('http://', url('/stocks'));
    }
}
