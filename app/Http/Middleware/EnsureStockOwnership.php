<?php

namespace App\Http\Middleware;

use App\Features\Stock\Models\Stock;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStockOwnership
{
    public function handle(Request $request, Closure $next): Response
    {
        $stock = $request->route('stock');

        if ($stock !== null && ! $stock instanceof Stock) {
            $stock = Stock::findOrFail($stock);
        }

        abort_if($stock !== null && $request->user()?->id !== $stock->user_id, 403, 'Stok ini bukan milik Anda.');

        return $next($request);
    }
}
