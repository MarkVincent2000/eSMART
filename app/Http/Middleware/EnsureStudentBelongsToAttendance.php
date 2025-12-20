<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\StudentAttendance;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentBelongsToAttendance
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ensures that users with "user" role can only access
     * attendance sessions where they have a StudentAttendance record.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // If user is not authenticated, let auth middleware handle it
        if (!$user) {
            return $next($request);
        }
        
        // If user doesn't have "user" role, allow access (for admins, etc.)
        if (!$user->hasRole('user')) {
            return $next($request);
        }
        
        // Get attendance ID from route parameter or query parameter
        $attendanceId = $request->route('id') ?? $request->query('id');
        
        // If no attendance ID, allow the request (might be listing page)
        if (!$attendanceId) {
            return $next($request);
        }
        
        // Convert to integer for consistency
        $attendanceId = (int) $attendanceId;
        
        // Check if attendance exists
        $attendance = Attendance::find($attendanceId);
        if (!$attendance) {
            abort(404, 'Attendance session not found.');
        }
        
        // Check if user has a StudentAttendance record for this attendance
        $studentAttendance = StudentAttendance::where('attendance_id', $attendanceId)
            ->where('user_id', $user->id)
            ->first();
        
        // If user doesn't have a StudentAttendance record, deny access
        if (!$studentAttendance) {
            abort(403, 'You do not have access to this attendance session.');
        }
        
        return $next($request);
    }
}
