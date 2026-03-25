<?php

namespace App\Livewire\Central\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AdminLogin extends Component
{
    public $email;

    public $password;

    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::guard('admin')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $user = Auth::guard('admin')->user();

            if ($user->is_suspended) {
                Auth::guard('admin')->logout();
                session()->invalidate();
                $this->addError('email', 'Esta cuenta administrativa ha sido suspendida.');

                return;
            }

            session()->regenerate();

            return redirect('/admin/dashboard');
        }

        $this->addError('email', 'Las credenciales proporcionadas no coinciden con nuestros registros.');
    }

    public function render()
    {
        return view('livewire.central.auth.admin-login')
            ->layout('layouts.centralApp');
    }
}
