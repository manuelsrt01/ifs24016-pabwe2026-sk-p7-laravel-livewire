<?php

namespace Tests\Unit\Middleware;

use App\Features\Stock\Models\Stock;
use App\Http\Middleware\EnsureStockOwnership;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureStockOwnershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function request(string $uri, ?User $user, mixed $bound = null): Request
    {
        $request = Request::create($uri);
        $route = new Route('GET', '/stocks/{stock}', fn () => null);
        $route->bind($request);

        if ($bound !== null) {
            $route->setParameter('stock', $bound);
        }

        $request->setRouteResolver(fn () => $route);
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_owner_passes_with_bound_model(): void
    {
        $stock = Stock::factory()->create();
        $request = $this->request("/stocks/{$stock->id}", $stock->user, $stock);

        $this->assertSame('ok', (new EnsureStockOwnership)->handle($request, fn () => response('ok'))->getContent());
    }

    public function test_owner_passes_with_raw_id(): void
    {
        $stock = Stock::factory()->create();
        $request = $this->request("/stocks/{$stock->id}", $stock->user);

        $this->assertSame('ok', (new EnsureStockOwnership)->handle($request, fn () => response('ok'))->getContent());
    }

    public function test_non_owner_is_forbidden(): void
    {
        $stock = Stock::factory()->create();
        $request = $this->request("/stocks/{$stock->id}", User::factory()->create(), $stock);

        $this->expectException(HttpException::class);
        (new EnsureStockOwnership)->handle($request, fn () => response('ok'));
    }

    public function test_unknown_id_is_not_found(): void
    {
        $request = $this->request('/stocks/9999', User::factory()->create());

        $this->expectException(ModelNotFoundException::class);
        (new EnsureStockOwnership)->handle($request, fn () => response('ok'));
    }

    public function test_route_without_stock_parameter_passes(): void
    {
        $request = Request::create('/stocks');
        $route = new Route('GET', '/stocks', fn () => null);
        $route->bind($request);
        $request->setRouteResolver(fn () => $route);

        $this->assertSame('ok', (new EnsureStockOwnership)->handle($request, fn () => response('ok'))->getContent());
    }
}
