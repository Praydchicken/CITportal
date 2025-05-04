<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\StudentLoad;
use App\Notifications\UpcomingClassNotification;

class SendUpcomingClassNotifications extends Command
{
    protected $signature = 'notify:upcoming-classes';
    protected $description = 'Send email notifications to students 15 minutes before class.';

    public function handle()
    {
        // Get today's day in the full textual format (e.g., "Monday", "Tuesday")
        $today = Carbon::now()->format('l'); 

        // Calculate the target time (15 minutes before class)
        $targetTime = Carbon::now()->addMinutes(15)->format('H:i:s');

        // Get the student loads where the class matches today's day and the class starts at the target time
        $loads = StudentLoad::with(['student.user', 'facultyLoad.classSchedule', 'facultyLoad.section'])
            ->whereHas('facultyLoad.classSchedule', function ($query) use ($today, $targetTime) {
                $query->where('day', $today) // Match the class day to today's day
                      ->whereTime('start_time', '=', $targetTime); // Match the start time to 15 minutes before class
            })
            ->get();

        // Send notifications to each student whose class matches the criteria
        foreach ($loads as $load) {
            $student = $load->student; // Get the student object

            // Get class details for notification
            $classDetails = [
                'subject' => $load->facultyLoad->curriculum->subject_name ?? 'N/A',
                'section' => $load->facultyLoad->section->name ?? 'N/A',
                'time' => Carbon::parse($load->facultyLoad->classSchedule->start_time)->format('h:i A'), // Format the time (e.g., 3:00 PM)
            ];

            // Send notification
            $student->notify(new UpcomingClassNotification($classDetails));
        }

        $this->info('Upcoming class notifications sent.');
    }
}
