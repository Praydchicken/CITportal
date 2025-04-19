<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Student;
use App\Models\User;
use App\Models\Curriculum;
use App\Models\FacultyLoad;
use App\Models\Section;
use App\Models\StudentGrade;
use Illuminate\Support\Facades\DB;

class ViewStudentInfoController extends Controller
{
    public function index($id)
    {
        $student = Student::with([
            'section',
            'yearLevel',
            'status',
            'semester',
            'user'
        ])->where('id', $id)->firstOrFail();

        $email = $student->user->email;

        // Get ALL curricula the student has ever been enrolled in
        $allCurricula = Curriculum::whereHas('studentGrades', function($query) use ($id) {
                $query->where('student_id', $id);
            })
            ->orWhere(function($query) use ($student) {
                // Include current curriculum even if no grades exist yet
                $query->where('year_level_id', $student->year_level_id)
                    ->where('semester_id', $student->semester_id);
            })
            ->with(['year_level', 'semester'])
            ->get()
            ->sortBy(['year_level_id', 'semester_id']);

        // Get all student grades indexed by curriculum_id
        $grades = StudentGrade::where('student_id', $id)
            ->get()
            ->keyBy('curriculum_id');

        // Organize curricula by semester/year level
        $organizedCurricula = $allCurricula->groupBy(function($item) {
            return 'Year ' . $item->year_level_id . ' - ' . $item->semester->semester_name;
        });

        // Merge grades into the curriculum subjects
        $curriculaWithGrades = $organizedCurricula->map(function($curriculumGroup, $groupName) use ($grades, $student) {
            return [
                'group_name' => $groupName,
                'is_current' => $curriculumGroup->first()->year_level_id == $student->year_level_id && 
                            $curriculumGroup->first()->semester_id == $student->semester_id,
                'subjects' => $curriculumGroup->map(function ($subject) use ($grades) {
                    $grade = $grades->get($subject->id);

                    return [
                        'curriculum_id' => $subject->id,
                        'course_code' => $subject->course_code,
                        'subject_name' => $subject->subject_name,
                        'lecture_units' => $subject->lecture_units,
                        'lab_units' => $subject->lab_units,
                        'total_units' => $subject->total_units,
                        'raw_grade' => $grade->raw_grade ?? null,
                        'gwa_equivalent' => $grade->gwa_equivalent ?? null,
                        'grade_remarks' => $grade->grade_remarks ?? null,
                        'grade_status' => $grade->grade_status ?? 'NOT YET ENCODED'
                    ];
                })
            ];
        });

        $studentInfo = [
            'student_no' => $student->student_number,
            'email' => $email,
            'first_name' => $student->first_name,
            'middle_name' => $student->middle_name,
            'last_name' => $student->last_name,
            'phone_number' => $student->phone_number ?? 'N/A',
            'address' => $student->address ?? 'N/A',
            'section'  => $student->section->section ?? 'N/A',
            'year_level' => $student->yearLevel->year_level ?? 'N/A',
            'semester' => $student->semester->semester_name,
            'status' => $student->status->status_name,
            'curricula' => $curriculaWithGrades, // now includes all curricula with grades!
            'is_final_semester' => $student->year_level_id == 4 && $student->semester_id == 2
        ];

        return Inertia::render('AdminDashboard/ViewStudentInfo', [
            'title' => 'View Student Info',
            'studentInfo' => $studentInfo
        ]);
    }


    public function promote($studentNo)
    {
        try {
            // Getting the student with section table, year level table, and status table where it match the student_number column name to the $StudentNo value;
            $student = Student::with(['section', 'yearLevel', 'status'])
                        ->where('student_number', $studentNo)
                        ->firstOrFail();

            // Getting the curriculum where it match the year level id to the $student->year_level_id and count the result of it
            $curriculumCount = Curriculum::where('year_level_id', $student->year_level_id)
                                ->where('semester_id', $student->semester_id)
                                ->count();

            // Count the grade that satistifed the where method
            $passedCount = StudentGrade::where('student_id', $student->id)
                                ->where('year_level_id', $student->year_level_id)
                                ->where('semester_id', $student->semester_id)
                                ->where('grade_status', 'APPROVED')
                                ->where('grade_remarks', 'PASSED')
                                ->count();

            // check if the graduating student is 4th yeat and currently in the second semester
            $isGraduating = ($student->year_level_id == 4 && $student->semester_id == 2);
        
            // check if the isGraduating is true and passedcount is equal to the count of curriculum then if its true updaye the student status into graduated
            if ($isGraduating && $passedCount === $curriculumCount) {
                $student->update([
                    'student_status_id' => 2 // Graduated
                ]);

                // 2. Remove student loads (since they are no longer enrolled in subjects)
                DB::table('student_loads')->where('student_id', $student->id)->delete();
                
                return back()->with([
                    'flash' => [
                        'success' => 'Student has successfully graduated!',
                        'data' => ['new_status' => 'Graduated']
                    ]
                ]);
            }

            // Prevent the student for promotion
            // passedCount = 8 and the curriculumCount = 9 meaning it is true because 8 is not equal to 9. Meaning the student did not passed all the subjects
            if ($curriculumCount === 0 || $passedCount !== $curriculumCount) {
                return back()->with([
                    'flash' => [
                        'error' => 'Student did not pass all required subjects',
                        'success' => false
                    ]
                ]);
            }

            $promotionType = request('promotion_type');
            $currentYearLevel = $student->year_level_id;
            $currentSemester = $student->semester_id;

            if ($promotionType === 'year') {
                // checking if the student finished the 2nd semester of the year level
                if ($currentSemester !== 2) {
                    return back()->with([
                        'flash' => [
                            'error' => 'Year promotion only allowed after completing 2nd semester',
                            'success' => false
                        ]
                    ]);
                }

                $newYearLevel = $currentYearLevel + 1;
                $newSemester = 1;
            } else {
                if ($currentSemester === 2) {
                    return back()->with([
                        'flash' => [
                            'error' => 'Use year promotion after completing 2nd semester',
                            'success' => false
                        ]
                    ]);
                }

                $newYearLevel = $currentYearLevel;
                $newSemester = $currentSemester + 1;
            }

            // Getting the section name from the students current section
            $section = $student->section->section;

            // Takes the last character of the section string and make sure it is a Upper case letter
            // 101 a = gets the "a" because it is the last character and with strtoupper convert the "a" to "A"
            $sectionLetter = strtoupper(substr($section, -1));

            // Checks if the last character is a letter, if not character the default is 'A'
            if (!ctype_alpha($sectionLetter)) {
                $sectionLetter = 'A';
            }

            $newSectionCode = ($newYearLevel * 100 + $newSemester) . $sectionLetter;

            $schoolYearId = $student->section->school_year_id;

            $newSection = Section::where([
                'year_level_id' => $newYearLevel,
                'semester_id' => $newSemester,
                'section' => $newSectionCode,
                'school_year_id' => $schoolYearId
            ])->first();

            // dd($newSection);

            if (!$newSection) {
                return back()->with([
                    'flash' => [
                        'error' => "Section {$newSectionCode} not found.",
                        'success' => false
                    ]
                ]);
            }

            $student->update([
                'year_level_id' => $newYearLevel,
                'semester_id' => $newSemester,
                'section_id' => $newSection->id,
                'student_status_id' => 1
            ]);

            // Remove old student loads (from previous section/semester)
            DB::table('student_loads')
                ->where('student_id', $student->id)
                ->delete();

            // ✅ Assign new faculty loads based on new section
            $facultyLoadsToAssign = FacultyLoad::where('section_id', $newSection->id)->pluck('id');

            $newStudentLoadData = [];
            foreach ($facultyLoadsToAssign as $facultyLoadId) {
                $newStudentLoadData[] = [
                    'student_id' => $student->id,
                    'faculty_load_id' => $facultyLoadId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            // Bulk insert the new loads
            if (!empty($newStudentLoadData)) {
                DB::table('student_loads')->insert($newStudentLoadData);
            }

            return back()->with([
                'flash' => [
                    'success' => 'Student promoted successfully!',
                    'data' => [
                        'new_year_level' => $newYearLevel,
                        'new_semester' => $newSemester,
                        'new_section' => $newSectionCode
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            dd($e->getMessage());
            return back()->with([
                'flash' => [
                    'error' => 'Promotion failed: ' . $e->getMessage(),
                    'success' => false
                ]
            ]);
        }
    }
}
