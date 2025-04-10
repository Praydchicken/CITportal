<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'school_year_id' => 'required|exists:school_years,id',
        ]);

        // Generate password based on first name and last name
        $rawPassword = strtolower($request->first_name . $request->last_name); // e.g., juandelacruz

        try {
            DB::beginTransaction();

            // Create User first
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($rawPassword),
                'user_type_id' => UserType::where('user_type', 'Teacher')->first()->id,
            ]);

            // Create Teacher
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'school_year_id' => $validated['school_year_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'] ?? 'N/A',
                'address' => $validated['address'],
            ]);
            DB::commit();

            return back()->with('success', 'Teacher added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Teacher creation failed: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Failed to add teacher.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'email' => 'required|email|unique:users,email,' . $teacher->user_id,
            'password' => 'nullable|string|min:8',
            'school_year_id' => 'required|exists:school_years,id',
        ]);

        try {
            DB::beginTransaction();

            // Update Teacher's info
            $teacher->update([
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'] ?? 'N/A',
                'address' => $validated['address'],
                'school_year_id' => $validated['school_year_id'],
            ]);

            // Update User's info
            $userData = [
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }

            $teacher->user->update($userData);

            DB::commit();

            return back()->with('success', 'Teacher updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Teacher update error: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Error updating teacher.']);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        try {
            DB::beginTransaction();
            
            // Get the user_id before deleting the teacher
            $userId = $teacher->user_id;
            
            // Delete the teacher record
            $teacher->delete();
            
            // Delete the associated user record
            if ($userId) {
                User::where('id', $userId)->delete();
            }
            
            DB::commit();
            
            return back()->with('success', 'Teacher deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Teacher Delete Error: ' . $e->getMessage());

            return back()->withErrors(['message' => 'An error occurred while deleting the teacher.']);
        }
    }

}
