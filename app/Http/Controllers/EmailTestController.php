<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentLoad;
use App\Notifications\UpcomingClassNotification;
use Carbon\Carbon;

class EmailTestController extends Controller
{
    public function ClassNotificationTest()
    {
        // Simulate a target time (e.g., class starting at 3:00 PM, regardless of current time)
        $targetTime = Carbon::createFromTime(15, 0, 0, 'Asia/Manila'); // Set time to 3:00 PM

        // Get the student loads with class schedules starting at the target time (simulated)
        $loads = StudentLoad::with(['student.user', 'facultyLoad.classSchedule', 'facultyLoad.section'])
        ->get();
        // ->whereHas('facultyLoad.classSchedule', function ($query) use ($targetTime) {
        //     $query->whereTime('start_time', '=', $targetTime->format('H:i:s'));
        // })

        // Send notifications to each student
        foreach ($loads as $load) {
            $student = $load->student;
            $classDetails = [
                'subject' => $load->facultyLoad->curriculum->subject_name ?? 'N/A',
                'section' => $load->facultyLoad->section->name ?? 'N/A',
                'time' => Carbon::parse($load->facultyLoad->classSchedule->start_time)->format('h:i A')
            ];
            $student->notify(new UpcomingClassNotification($classDetails));  // Send notification
        }

        return 'Test notifications sent successfully.';
    }
}

