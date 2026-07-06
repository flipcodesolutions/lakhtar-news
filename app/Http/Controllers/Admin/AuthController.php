<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    //
    public function login()
    {
        return view('admin.login.login');
    }

    public function forgotPassword()
    {
        return view('admin.login.forgotPassword');
    }

    public function forgotPasswordPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'Email not found.']);
        }

        $token = Password::broker()->createToken($user);
        $resetUrl = route('admin.password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::send('emails.admin-password-reset', [
            'user' => $user,
            'resetUrl' => $resetUrl,
        ], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('Lakhtar News Admin Password Reset');
        });

        return back()->with('success', 'Password reset link sent successfully.');
    }

    public function resetPassword(Request $request, string $token)
    {
        return view('admin.login.resetPassword', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPasswordPost(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        return redirect()
            ->route('admin.login')
            ->with('success', 'Password reset successfully. Please login with your new password.');
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user->role !== 'admin') {
            return redirect()->back()->withInput()->with('error', 'You are not authorized to access this application');
        }
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Email not found');
        }
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->withInput()->with('error', 'Invalid password');
        }
        Auth::login($user);
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
