<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use LdapRecord\Laravel\Auth\ListensForLdapBindFailure;

class LdapAuthController extends Controller
{
    use ListensForLdapBindFailure;

    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));
        } catch (\LdapRecord\Auth\BindException $e) {
            // Handle LDAP bind failures
            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros o el servidor LDAP no está disponible.',
            ]);
        } catch (\Exception $e) {
            // Handle other authentication failures
            return back()->withErrors([
                'email' => 'Error de autenticación. Por favor, inténtelo de nuevo.',
            ]);
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Listen for LDAP bind failures.
     */
    protected function handleLdapBindError($exception, $user, $provider)
    {
        // Log the LDAP bind failure
        \Log::error('LDAP Bind Failed', [
            'user' => $user ? $user->getName() : 'Unknown',
            'error' => $exception->getMessage(),
            'provider' => $provider,
        ]);
    }
}
