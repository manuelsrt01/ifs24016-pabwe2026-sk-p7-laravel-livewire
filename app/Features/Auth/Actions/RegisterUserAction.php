<?php

namespace App\Features\Auth\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterUserAction
{
    /** Pendaftaran publik selalu berperan "member" (role tidak bisa dipilih dari input). */
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_MEMBER,
        ]);

        Auth::login($user);
        session()->regenerate();

        return $user;
    }
}
