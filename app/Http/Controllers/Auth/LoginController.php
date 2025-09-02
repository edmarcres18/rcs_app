<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Services\UserActivityService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        try {
            UserActivityService::logLogin($user);
        } catch (Exception $e) {
            Log::error('Failed to log user login activity', [
                'user_id' => $user->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't fail the login process if logging fails
        }
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        try {
            $login = request()->input('login');
            $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'nickname';
            request()->merge([$field => $login]);
            return $field;
        } catch (Exception $e) {
            Log::error('Error determining login field type', [
                'login_input' => request()->input('login'),
                'error' => $e->getMessage()
            ]);
            // Default to email if there's an error
            return 'email';
        }
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required|string',
                'password' => 'required|string',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Login validation failed', [
                'login_input' => $request->input('login'),
                'errors' => $e->errors()
            ]);
            throw $e;
        } catch (Exception $e) {
            Log::error('Unexpected error during login validation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw ValidationException::withMessages([
                'login' => ['An unexpected error occurred during validation.']
            ]);
        }
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        try {
            UserActivityService::logFailedLogin($request->input('login'));
        } catch (Exception $e) {
            Log::error('Failed to log failed login attempt', [
                'login_input' => $request->input('login'),
                'error' => $e->getMessage()
            ]);
            // Don't fail the failed login response if logging fails
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        try {
            $field = $this->username();

            $credentials = [
                $field => $request->input($field),
                'password' => $request->input('password')
            ];

            $remember = $request->filled('remember');

            $result = $this->guard()->attempt($credentials, $remember);

            if (!$result) {
                Log::info('Failed login attempt', [
                    'field' => $field,
                    'login_value' => $request->input($field),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('Error during login attempt', [
                'field' => $field ?? 'unknown',
                'login_value' => $request->input($field ?? 'login'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return false to indicate login failure
            return false;
        }
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        try {
            $request->session()->regenerate();
            $this->clearLoginAttempts($request);

            if ($response = $this->authenticated($request, $this->guard()->user())) {
                return $response;
            }

            return $request->wantsJson()
                        ? new JsonResponse([], 204)
                        : redirect()->intended($this->redirectPath())->with('success', 'Logged in successfully!');

        } catch (Exception $e) {
            Log::error('Error sending login response', [
                'user_id' => Auth::id() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Attempt to redirect to login with error message
            try {
                return redirect('/login')->with('error', 'An error occurred during login. Please try again.');
            } catch (Exception $redirectError) {
                Log::critical('Failed to redirect after login error', [
                    'original_error' => $e->getMessage(),
                    'redirect_error' => $redirectError->getMessage()
                ]);

                // Return a basic response as last resort
                return response('Login error occurred', 500);
            }
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            $user = Auth::user();

            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($user) {
                try {
                    UserActivityService::logLogout($user);
                } catch (Exception $e) {
                    Log::error('Failed to log user logout activity', [
                        'user_id' => $user->id ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    // Don't fail the logout process if logging fails
                }
            }

            return $this->loggedOut($request) ?: redirect('/login')->with('success', 'Logged out successfully!');

        } catch (Exception $e) {
            Log::error('Error during logout process', [
                'user_id' => Auth::id() ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Attempt to force logout and redirect
            try {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('error', 'An error occurred during logout. You have been logged out.');
            } catch (Exception $forceLogoutError) {
                Log::critical('Failed to force logout after error', [
                    'original_error' => $e->getMessage(),
                    'force_logout_error' => $forceLogoutError->getMessage()
                ]);

                // Return a basic response as last resort
                return response('Logout error occurred', 500);
            }
        }
    }
}
