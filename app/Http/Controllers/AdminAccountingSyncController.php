<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Student;
use App\Models\FinancialRecord;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

                // Calculate the starting school year of the student based on their current year level
                $currentYear = now()->year; // Get the current year
                $studentStartYear = $currentYear - ($student->year_level_id - 1);

                foreach ($financialRecords as $record) {
                    $recordSchoolYear = $record['school_year']; // e.g. "2022-2023"
                    $recordSemester = (int) $record['semester']; // 1 or 2
                    $recordYearStart = (int) explode('-', $recordSchoolYear)[0];

                    // Check if the record's school year is within the student's academic history
                    if ($recordYearStart >= $studentStartYear) {
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
                        $logs[] = "Skipped older record: {$record['school_year']} Sem {$record['semester']} (before student's assumed start)";
                    }
                }

                $logs[] = "Updated $recordCount records for student ID: $studentNumber";
            } else {
                $logs[] = "Failed to fetch records for student ID: $studentNumber. Response: " . $response->body();
            }
        }

        $logs[] = 'Sync process completed.';

        return response()->json([
            'message' => 'Financial records synced successfully.',
            'logs' => $logs
        ]);
    }
    public function searchRecord(Request $request)
    {
        $request->input('search');

        $financialRecords = FinancialRecord::with([
            'student_number',
            'school_year',
            'semester',
            'tuition_fee',
            'discount',
            'adjustment',
            'amount_paid',
            'balance',

        ])
            ->when($request->search, function ($query, $search) {
                return $query->where('student_number', 'like', '%' . $search . '%');
            })
            ->orderBy('school_year', 'desc')
            ->orderBy('semester', 'desc')
            ->get(); // Use get() instead of paginate()

        return Inertia::render('AdminDashboard/AdminAccountingSync', [
            'financialRecords' => $financialRecords,
            'search' => $request->only('search'),
        ]);
    }
}
