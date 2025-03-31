<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthRequest;
use App\Http\Requests\UpdateAuthRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status')
        ]);
    }

    public function store(Request $request)
    {

        /**
         *  This function handles the login process.
         *  It validates the request data, attempts to log in the user,
         *  and redirects them to the intended page or back with an error message.
         */
        // Validate the request data
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user(); // Get the authenticated user
 
            $roleName = $user->userType->user_type; 

            // Check the user's role and redirect accordingly
            if ($roleName === 'Admin') {
                return redirect()->intended(route('admin.dashboard')); // Redirect admin to admin dashboard
            }
            return redirect()->intended(route('student.dashboard')); // Redirect student to student dashboard

        }
        // If authentication fails, redirect back with an error message
        return back()->withErrors([
            'email' => 'Invalid Password or Email.',
        ])->onlyInput('email');
    }
    public function destroy(Request $request)
    {
        /**
         *  Removes the login info from the session, so the user is no longer logged in.
         */
        Auth::logout();

        // It clears all temporary data stored for the user to fully log them out.
        $request->session()->invalidate();

        // generate new token for security purposes
        $request->session()->regenerateToken();

        // Redirect to home page
        return Inertia::location('/');
    }
}
