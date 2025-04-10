<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Student;
use App\Models\User;
use App\Models\UserType;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $stats = [
            'enrolled' => Student::whereHas('status', function($query) {
                $query->where('status_name', 'Enrolled');
            })->count(),
            'dropped' => Student::whereHas('status', function($query) {
                $query->where('status_name', 'Dropped');
            })->count(),
            'graduated' => Student::whereHas('status', function($query) {
                $query->where('status_name', 'Graduated');
            })->count(),
        ];

        return Inertia::render('AdminDashboard/Dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats
        ]);
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20',
            'gender' => 'required|string|in:Male,Female',
            'address' => 'required|string',
            'password' => 'required|string|min:8'
        ]);

        try {
            DB::beginTransaction();

            // Get or create the admin/faculty user type
            $adminUserType = UserType::where('user_type', 'admin')->first();
            
            if (!$adminUserType) {
                $adminUserType = UserType::create(['user_type' => 'admin']);
            }

            // Create user account
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'user_type_id' => $adminUserType->id
            ]);

            // Create admin/faculty record
            $admin = Admin::create([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'gender' => $validated['gender'],
                'address' => $validated['address']
            ]);

            DB::commit();

            // Load relationships for the response
            $admin->load(['user']);

            return back()->with([
                'success' => 'Faculty added successfully',
                'admin' => $admin
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating faculty: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Admin $admin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Admin $admin)
    {
        // Validate the request data
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'gender' => 'required|string|in:Male,Female',
            'address' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $admin->user_id,
            'password' => 'nullable|string|min:8' // Optional password update
        ]);

        try {
            DB::beginTransaction();

            // Update admin details
            $admin->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
            ]);

            // Update user details
            $userData = [
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
            ];

            // Only update password if provided
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $admin->user->update($userData);

            // Load all necessary relationships
            $admin->load([
                'user',
                'facultyLoads',
                'facultyLoads.curriculum',
                'facultyLoads.section',
                'facultyLoads.yearLevel',
                'facultyLoads.schedule',
                'facultyLoads.room'
            ]);

            DB::commit();

            return back()->with([
                'success' => 'Faculty updated successfully',
                'admin' => $admin
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Faculty Update Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            if (config('app.debug')) {
                return back()->withErrors(['message' => 'Error: ' . $e->getMessage()]);
            }
            
            return back()->withErrors(['message' => 'An error occurred while updating the faculty.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $admin = Admin::with(['user', 'facultyLoads'])->findOrFail($id);
            
            // Delete related faculty loads first
            if ($admin->facultyLoads) {
                foreach ($admin->facultyLoads as $load) {
                    $load->delete();
                }
            }

            // Delete the user account
            if ($admin->user) {
                $admin->user->delete();
            }

            // Finally delete the admin record
            $admin->delete();

            DB::commit();

            return back()->with('success', 'Faculty member deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Faculty Delete Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return back()->withErrors(['message' => 'An error occurred while deleting the faculty member.']);
        }
    }
}
