<?php

namespace App\Listeners;

use App\Events\AttendanceRecorded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAttendanceNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AttendanceRecorded $event): void
    {
        // Log attendance notification
        \Log::info('Attendance recorded', [
            'student_id' => $event->attendance->student_id,
            'date' => $event->attendance->date,
            'status' => $event->attendance->status,
            'recorded_by' => $event->attendance->recorded_by,
        ]);
        
        // Here you can implement actual notification logic:
        // - Send email to parents
        // - Send SMS
        // - Push notification
        // - Slack/Discord webhook
        // For now, it's just logging
    }
}
