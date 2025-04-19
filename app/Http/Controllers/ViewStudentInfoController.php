<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Student;
use App\Models\User;
use App\Models\Curriculum;
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
            $student = Student::with(['section', 'yearLevel', 'status'])
                        ->where('student_number', $studentNo)
                        ->firstOrFail();
            
            $curriculumCount = Curriculum::where('year_level_id', $student->year_level_id)
                                ->where('semester_id', $student->semester_id)
                                ->count();

            $passedCount = StudentGrade::where('student_id', $student->id)
                                ->where('year_level_id', $student->year_level_id)
                                ->where('semester_id', $student->semester_id)
                                ->where('grade_status', 'APPROVED')
                                ->where('grade_remarks', 'PASSED')
                                ->count();

            // Check if student has completed all requirements for graduation
            $isGraduating = ($student->year_level_id == 4 && $student->semester_id == 2);
            
            if ($isGraduating && $passedCount === $curriculumCount) {
                // Update student status to Graduated (ID 2)
                $student->update([
                    'student_status_id' => 2 // Graduated
                ]);

                return back()->with([
                    'flash' => [
                        'success' => 'Student has successfully graduated!',
                        'data' => [
                            'new_status' => 'Graduated'
                        ]
                    ]
                ]);
            }

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

            // Generate new section code
            $newSectionCode = $this->generateNewSectionCode(
                $student->section->section,
                $currentYearLevel,
                $currentSemester,
                $newYearLevel,
                $newSemester
            );

            // Find or create the new section
            $newSection = Section::firstOrCreate(
                [
                    'year_level_id' => $newYearLevel,
                    'semester_id' => $newSemester,
                    'section' => $newSectionCode
                ],
                [
                    'section_description' => 'Year ' . $newYearLevel . ' Semester ' . $newSemester . ' - ' . $newSectionCode
                ]
            );

            // Update student record
            $student->update([
                'year_level_id' => $newYearLevel,
                'semester_id' => $newSemester,
                'section_id' => $newSection->id,
                'student_status_id' => 1 // Ensure status remains Enrolled if not graduating
            ]);

            return back()->with([
                'flash' => [
                    'success' => 'Student promoted successfully',
                    'data' => [
                        'new_year_level' => $newYearLevel,
                        'new_semester' => $newSemester,
                        'new_section' => $newSectionCode
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return back()->with([
                'flash' => [
                    'error' => 'Promotion failed: ' . $e->getMessage(),
                    'success' => false
                ]
            ]);
        }
    }

    protected function generateNewSectionCode($currentSection, $currentYearLevel, $currentSemester, $newYearLevel, $newSemester)
    {
        // Extract the base section name (e.g., "101 A" → "A")
        $sectionParts = explode(' ', $currentSection);
        $sectionLetter = end($sectionParts);
        
        // Handle cases where section might not follow standard format
        if (!ctype_alpha($sectionLetter)) {
            $sectionLetter = 'A'; // Default to 'A' if no letter found
        }

        // Generate new section code based on promotion type
        if ($newYearLevel > $currentYearLevel) {
            // Year promotion (e.g., "101 A" → "201 A")
            return $newYearLevel . '01 ' . $sectionLetter;
        } else {
            // Semester promotion (e.g., "101 A" → "102 A")
            return $currentYearLevel . '0' . $newSemester . ' ' . $sectionLetter;
        }
    }
}
