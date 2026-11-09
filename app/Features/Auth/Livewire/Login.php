<?php

namespace App\Features\Auth\Livewire;

use App\Features\Auth\Actions\LoginUserAction;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Masuk')]
class Login extends Component
{
    public string $username = '';

    public string $password = '';

    public bool $remember = false;

    public function login(LoginUserAction $action): void
    {
        $this->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! $action->execute($this->username, $this->password, $this->remember)) {
            $this->addError('username', 'Username/email atau password salah.');

            return;
        }

        $this->redirect(Auth::user()->isAdmin() ? route('admin.users') : route('stocks.index'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
