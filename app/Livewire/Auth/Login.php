<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class Login extends Component
{
    public string $email    = '';
    public string $password = '';
    public bool   $remember = false;

    public function render(): View
    {
        return view('livewire.auth.login')
            ->layout('layouts.guest', ['title' => 'Login — BengkelOS']);
    }

    public function login(): void
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required|min:1',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Rate limiting
        $key = 'login:' . Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('email', "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.");
            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::clear($key);
            session()->regenerate();

            $intended = session()->pull('url.intended', route('dashboard'));
            $this->redirect($intended);
            return;
        }

        RateLimiter::hit($key, 60);

        $this->addError('email', 'Email atau password salah.');
        $this->password = '';
    }
}
