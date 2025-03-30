<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthRequest;
use App\Http\Requests\UpdateAuthRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\Auth; 


class AuthController extends Controller
{   
    
    public function login(Request $request) {
        // For checking purposes only
        // dd("Login successful");

       $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'min:8'], // Enforces 8 characters
        ]);

        //  If validation passes, try logging in
        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'Invalid email or password.', // Shown only when authentication fails
            ])->onlyInput('email');
        }

        $user = Auth::user();

        // Redirect based on user type
        if ($user->userType->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('student.dashboard');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Login');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuthRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Auth $auth)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Auth $auth)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAuthRequest $request, Auth $auth)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
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
