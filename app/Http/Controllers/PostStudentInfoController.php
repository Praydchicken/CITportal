<?php

namespace App\Http\Controllers;

use App\Models\FacultyLoad;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Models\UserType;
use App\Models\YearLevel;
use App\Models\StudentStatus;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PostStudentInfoController extends Controller
{
    public function index(Request $request)
    {
        // Get the active school year, but don't fail if none exists
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();

        // Use the active school year for default view but allow filtering by other school years
        $selectedSchoolYearId = $request->school_year ?? $activeSchoolYear?->id;

        // Fetch students with conditional filters
        $students = Student::with([
            'section',
            'yearLevel',
            'user',
            'status',
            'schoolYear',
            'semester'
        ])
            ->when($selectedSchoolYearId, function ($query, $schoolYearId) {
                return $query->where('school_year_id', $schoolYearId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('student_number', 'like', "%$search%")
                        ->orWhere('first_name', 'like', "%$search%")
                        ->orWhere('middle_name', 'like', "%$search%")
                        ->orWhere('last_name', 'like', "%$search%");
                });
            })
            ->when($request->year_level, function ($query, $yearLevel) {
                return $query->where('year_level_id', $yearLevel);
            })
            ->when($request->semester, function ($query, $semester) {
                return $query->where('semester_id', $semester);
            })
            ->when($request->section, function ($query, $section) {
                return $query->where('section_id', $section);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Fetch supporting data for the filters
        $sections = Section::all();
        $yearLevels = YearLevel::all();
        $studentStatuses = StudentStatus::all();
        $schoolYears = SchoolYear::orderBy('school_year', 'desc')->get();
        $semesters = Semester::all();

        // Return the view with the filtered students and data
        return Inertia::render('AdminDashboard/StudentInformation', [
            'title' => 'Student Information',
            'students' => $students,
            'sections' => $sections,
            'yearLevels' => $yearLevels,
            'studentStatuses' => $studentStatuses,
            'activeSchoolYear' => $activeSchoolYear,
            'schoolYears' => $schoolYears,
            'semesters' => $semesters,
            'filters' => $request->only(['search', 'year_level', 'semester', 'section', 'school_year']),
        ]);
    }

    public function store(Request $request)
    {
        $activeSchoolYear = SchoolYear::where('school_year_status', 'Active')->first();

        if (!$activeSchoolYear) {
            return back()->with('error', 'No active school year found. Please set an active school year before adding students.');
        }

        $validated = $request->validate([
            'student_number' => [
                'required',
                'string',
                'size:9', // Enforces exactly 9 characters
                'unique:students,student_number',
                'regex:/^[0-9]+$/' // Ensures only digits
            ],
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'nullable|string|max:20',
            'gender' => 'required|string|in:Male,Female',
            'address' => 'nullable|string',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'student_status_id' => 'required|exists:student_statuses,id',
            'enrollment_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($activeSchoolYear) {
                    $years = explode('-', $activeSchoolYear->school_year);
                    if (count($years) === 2) {
                        $startYear = (int) $years[0];
                        $endYear = (int) $years[1];
                        $enrollmentYear = (int) date('Y', strtotime($value));
                        if ($enrollmentYear < $startYear || $enrollmentYear > $endYear) {
                            $fail("The enrollment date's year must be within the active school year ({$activeSchoolYear->school_year}).");
                        }
                    } else {
                        $fail("The active school year format is invalid.");
                    }
                },
            ],
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $rawPassword = strtolower($request->first_name . $request->last_name);

        try {
            DB::beginTransaction();

            // Get or create student user type
            $studentUserType = UserType::where('user_type', 'student')->firstOrCreate([
                'user_type' => 'student'
            ]);

            // Create user account
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make($rawPassword),
                'user_type_id' => $studentUserType->id
            ]);

            // Find the requested section
            $requestedSection = Section::findOrFail($validated['section_id']);

            // Check if section belongs to active school year
            if ($requestedSection->school_year_id !== $activeSchoolYear->id) {
                // Find or create matching section in current active school year
                $activeSection = Section::firstOrCreate([
                    'section' => $requestedSection->section,
                    'year_level_id' => $validated['year_level_id'],
                    'semester_id' => $validated['semester_id'],
                    'school_year_id' => $activeSchoolYear->id
                ], [
                    'minimum_number_students' => $requestedSection->minimum_number_students,
                    'maximum_number_students' => $requestedSection->maximum_number_students
                ]);
            } else {
                $activeSection = $requestedSection;
            }

            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'section_id' => $activeSection->id,
                'year_level_id' => $validated['year_level_id'],
                'student_status_id' => $validated['student_status_id'],
                'school_year_id' => $activeSchoolYear->id,
                'semester_id' => $validated['semester_id'],
                'student_number' => $validated['student_number'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'phone_number' => $validated['phone_number'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'enrollment_date' => $validated['enrollment_date'],
            ]);

            // Assign student to faculty loads
            $facultyLoads = FacultyLoad::where('year_level_id', $validated['year_level_id'])
                ->where('semester_id', $validated['semester_id'])
                ->where('school_year_id', $activeSchoolYear->id)
                ->get();

            $studentLoadData = $facultyLoads->map(function ($load) use ($student) {
                return [
                    'student_id' => $student->id,
                    'faculty_load_id' => $load->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            })->toArray();

            if (!empty($studentLoadData)) {
                DB::table('student_loads')->insert($studentLoadData);
            }

            DB::commit();

            $student->load(['section', 'yearLevel', 'user', 'status', 'schoolYear', 'semester']);

            return back()->with([
                'success' => 'Student created successfully',
                'student' => $student
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating student: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'student_number' => 'required|unique:students,student_number,' . $student->id,
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'section_id' => 'required|exists:sections,id',
            'year_level_id' => 'required|exists:year_levels,id',
            'phone_number' => 'nullable|string',
            'gender' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'enrollment_date' => 'required|date',
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'student_status_id' => 'required|exists:student_statuses,id',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        try {
            DB::beginTransaction();

            // Update Student model
            $student->update([
                'student_number' => $validated['student_number'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'],
                'last_name' => $validated['last_name'],
                'section_id' => $validated['section_id'],
                'year_level_id' => $validated['year_level_id'],
                'phone_number' => $validated['phone_number'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
                'enrollment_date' => $validated['enrollment_date'],
                'student_status_id' => $validated['student_status_id'],
                'semester_id' => $validated['semester_id'],
            ]);

            // Update User Email if Changed
            if ($student->user->email !== $validated['email']) {
                $student->user->update([
                    'email' => $validated['email'],
                ]);
            }

            // --- Synchronize Student Loads --- 

            // 1. Remove all existing loads for this student
            DB::table('student_loads')->where('student_id', $student->id)->delete();

            // 2. Check if the student's new status is 'Enrolled'
            $enrolledStatus = StudentStatus::where('status_name', 'Enrolled')->first();
            $isEnrolled = $enrolledStatus && $student->student_status_id == $enrolledStatus->id;

            // 3. If enrolled, find and add new relevant loads
            if ($isEnrolled) {
                $facultyLoadsToAssign = DB::table('faculty_loads')
                    ->where('year_level_id', $student->year_level_id)
                    ->where('semester_id', $student->semester_id)
                    ->where('section_id', $student->section_id)
                    // ->where('school_year_id', $student->school_year_id) // Optional: Add if faculty loads are school year specific
                    ->pluck('id');

                $newStudentLoadData = [];
                foreach ($facultyLoadsToAssign as $facultyLoadId) {
                    $newStudentLoadData[] = [
                        'student_id' => $student->id,
                        'faculty_load_id' => $facultyLoadId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                // Bulk insert new loads if any were found
                if (!empty($newStudentLoadData)) {
                    DB::table('student_loads')->insert($newStudentLoadData);
                }
            }
            // --- End Synchronize Student Loads --- 

            DB::commit();

            return redirect()->back()->with([
                'success' => 'Student updated successfully!',
                'updatedStudent' => $student->fresh(['section', 'yearLevel', 'user', 'status', 'semester'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update student: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $student = Student::find($id);

            if (!$student) {
                return back()->withErrors(['error' => 'Student not found']);
            }

            $student->delete(); // Soft delete (sets deleted_at instead of removing the record)
            return back()->with('success', 'Student deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete student: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified student resource with all related details.
     *
     * @param  int  $id The ID of the student.
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $student = Student::with([
                'user',
                'section.yearLevel',
                'status',
                'schoolYear',
                'studentLoads.facultyLoad.curriculum',
                'studentLoads.facultyLoad.schedule',
                'studentLoads.facultyLoad.teacher',
                'studentLoads.facultyLoad.semester'
            ])->find($id);


            // dd($student);

            return response()->json($student); // or dd($student) for debugging

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Student not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch student details.'], 500);
        }
    }
}
