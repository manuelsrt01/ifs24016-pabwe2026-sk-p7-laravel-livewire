<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureRoleMiddlewareTest extends TestCase
{
    private function request(?User $user): Request
    {
        $request = Request::create('/x');
        $request->setUserResolver(fn () => $user);

        return $request;
    }

    public function test_allows_matching_role(): void
    {
        $response = (new EnsureRole)->handle($this->request(User::factory()->make(['role' => 'member'])), fn () => response('ok'), 'member');

        $this->assertSame('ok', $response->getContent());
    }

    public function test_allows_any_of_multiple_roles(): void
    {
        $response = (new EnsureRole)->handle($this->request(User::factory()->admin()->make()), fn () => response('ok'), 'member', 'admin');

        $this->assertSame('ok', $response->getContent());
    }

    public function test_rejects_other_role_and_guest(): void
    {
        $this->expectException(HttpException::class);
        (new EnsureRole)->handle($this->request(User::factory()->make(['role' => 'member'])), fn () => response('ok'), 'admin');
    }

    public function test_rejects_guest(): void
    {
        try {
            (new EnsureRole)->handle($this->request(null), fn () => response('ok'), 'member');
            $this->fail('Guest seharusnya ditolak.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }
}
