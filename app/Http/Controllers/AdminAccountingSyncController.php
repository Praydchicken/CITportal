<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Student;
use App\Models\FinancialRecord;

class AdminAccountingSyncController extends Controller
{
    public function syncFinancialDataFromAPI()
    {
        $logs = [];
        $logs[] = 'Starting sync process...';

        // Fetch students with current year and semester
        $students = Student::select('student_number', 'year_level_id', 'semester_id')->get();
        $logs[] = 'Fetched ' . $students->count() . ' student records from local DB.';

        foreach ($students as $student) {
            $studentNumber = $student->student_number;
            $logs[] = "Fetching records for student ID: $studentNumber";

            $response = Http::get("http://127.0.0.1:8001/api/student/{$studentNumber}/financial-records");

            if ($response->successful() && $response->json()) {
                $financialRecords = $response->json();
                $recordCount = 0;

                // Calculate current semester index (e.g., 3rd year 1st sem = 5th sem = index 4)
                $currentSemesterIndex = (($student->year_level_id - 1) * 2) + ($student->semester_id - 1);

                foreach ($financialRecords as $record) {
                    $recordSchoolYear = $record['school_year']; // e.g. "2022-2023"
                    $recordSemester = (int) $record['semester']; // 1 or 2

                    $recordYearStart = (int) explode('-', $recordSchoolYear)[0];

                    // Calculate semester index from 1st year 1st sem (index 0)
                    $recordSemesterIndex = (($recordYearStart - 2020) * 2) + ($recordSemester - 1);

                    if ($recordSemesterIndex <= $currentSemesterIndex) {
                        FinancialRecord::updateOrCreate(
                            [
                                'student_number' => $studentNumber,
                                'school_year' => $record['school_year'],
                                'semester' => $record['semester'],
                            ],
                            [
                                'tuition_fee' => $record['tuition_fee'],
                                'discount' => $record['discount'],
                                'adjustment' => $record['adjustment'],
                                'amount_paid' => $record['amount_paid'],
                                'balance' => $record['balance'],
                            ]
                        );
                        $recordCount++;
                    } else {
                        $logs[] = "Skipped future record: {$record['school_year']} Sem {$record['semester']} (beyond current sem)";
                    }
                }

                $logs[] = "Updated $recordCount records for student ID: $studentNumber";
            } else {
                $logs[] = "Failed to fetch records for student ID: $studentNumber. Response: " . $response->body();
                \Log::error("Failed to fetch financial records for student: {$studentNumber}", [
                    'response' => $response->body(),
                ]);
            }
        }

        $logs[] = 'Sync process completed.';

        return response()->json([
            'message' => 'Financial records synced successfully.',
            'logs' => $logs
        ]);
    }
}
