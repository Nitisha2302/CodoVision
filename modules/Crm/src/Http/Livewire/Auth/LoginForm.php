<?php

namespace Codovision\Crm\Http\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Throwable;

class LoginForm extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:180'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ];
    }

    public function login()
    {
        $this->validate();

        $key = 'crm-login:' . strtolower($this->email) . '|' . request()->ip();

        try {
            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                throw ValidationException::withMessages([
                    'email' => "Too many login attempts. Try again in {$seconds} seconds.",
                ]);
            }

            if (!Auth::guard('crm')->attempt(
                ['email' => $this->email, 'password' => $this->password, 'is_active' => true],
                $this->remember
            )) {
                RateLimiter::hit($key, 60);

                $inactive = \Codovision\Crm\Models\CrmUser::query()
                    ->where('email', $this->email)
                    ->where('is_active', false)
                    ->exists();

                throw ValidationException::withMessages([
                    'email' => $inactive
                        ? 'This CRM account is inactive. Contact an administrator.'
                        : 'Invalid email or password.',
                ]);
            }

            RateLimiter::clear($key);
            session()->regenerate();
            Auth::shouldUse('crm');

            return redirect()->route('crm.dashboard');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);
            throw ValidationException::withMessages([
                'email' => 'Unable to sign in right now. Please try again.',
            ]);
        }
    }

    public function render()
    {
        return view('crm::livewire.auth.login-form')
            ->layout('crm::layouts.guest');
    }
}
