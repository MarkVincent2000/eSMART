<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\StudentAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
    
/**
 * StudentAttendanceController
 * 
 * This controller handles all operations related to viewing and managing
 * student attendance records for specific attendance sessions.
 */
class StudentAttendanceController extends Controller
{
    /**
     * Constructor - Apply authentication middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Time In - Record check-in time for student attendance
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeIn(Request $request)
    {
        try {
            $attendanceId = $request->input('attendance_id');
            $studentAttendanceId = $request->input('student_attendance_id');
            $clientTime = $request->input('client_time'); // ISO 8601 format from client
            
            if (!$attendanceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance ID is required'
                ], 400);
            }
            
            if (!$studentAttendanceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student Attendance ID is required. You must be enrolled in this attendance session.'
                ], 400);
            }
            
            // Find existing student attendance record - must exist
            $studentAttendance = StudentAttendance::with('attendance')->where('id', $studentAttendanceId)
                ->where('attendance_id', $attendanceId)
                ->where('user_id', Auth::id())
                ->first();
            
            // If not found by ID, try to find by attendance_id and user_id
            if (!$studentAttendance) {
                $studentAttendance = StudentAttendance::with('attendance')->where('attendance_id', $attendanceId)
                    ->where('user_id', Auth::id())
                    ->first();
            }
            
            // If still not found, user is not enrolled
            if (!$studentAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not enrolled in this attendance session.'
                ], 404);
            }
            
            // Get attendance date - we need to use this date for the check-in time
            $attendance = $studentAttendance->attendance;
            if (!$attendance || !$attendance->date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance session date not found.'
                ], 400);
            }
            
            $attendanceDate = $attendance->date instanceof \Carbon\Carbon 
                ? $attendance->date->toDateString()
                : $attendance->date;
            
            // Use digital clock time from client - extract time portion only (H:i:s) for TIME data type
            // The client_time is captured from the digital clock display at the moment of confirmation
            // Client sends UTC time, but we need to extract the Manila local time portion
            $checkInTimeStr = null;
            if ($clientTime) {
                try {
                    // Parse the UTC time from digital clock (ISO 8601 format)
                    $clientDateTimeUTC = \Carbon\Carbon::parse($clientTime)->utc();
                    
                    // Convert to Manila timezone to get the local time the user sees
                    $clientDateTimeManila = $clientDateTimeUTC->copy()->setTimezone('Asia/Manila');
                    
                    // Extract time portion (H:i:s) from Manila local time
                    // This is the time the user actually sees on their clock
                    // Store only time portion for TIME data type
                    $checkInTimeStr = $clientDateTimeManila->format('H:i:s');
                    
                    // Log for debugging purposes
                    Log::info('Time In recorded from digital clock', [
                        'attendance_date' => $attendanceDate,
                        'client_time_utc_iso' => $clientTime,
                        'client_time_utc' => $clientDateTimeUTC->format('Y-m-d H:i:s'),
                        'client_time_manila' => $clientDateTimeManila->format('Y-m-d H:i:s'),
                        'recorded_time_str' => $checkInTimeStr,
                        'time_source' => 'digital_clock_manila_time_extracted'
                    ]);
                } catch (\Exception $e) {
                    // If parsing fails, use server time in Manila timezone
                    $serverTimeManila = now()->setTimezone('Asia/Manila');
                    $checkInTimeStr = $serverTimeManila->format('H:i:s');
                    Log::error('Failed to parse digital clock time, using server time', [
                        'digital_clock_time' => $clientTime,
                        'error' => $e->getMessage(),
                        'attendance_date' => $attendanceDate,
                        'server_time_manila' => $serverTimeManila->format('Y-m-d H:i:s'),
                        'recorded_time_str' => $checkInTimeStr,
                    ]);
                }
            } else {
                // No client time provided, use server time in Manila timezone
                $serverTimeManila = now()->setTimezone('Asia/Manila');
                $checkInTimeStr = $serverTimeManila->format('H:i:s');
                Log::warning('Time In recorded without digital clock time, using server time', [
                    'attendance_date' => $attendanceDate,
                    'server_time_manila' => $serverTimeManila->format('Y-m-d H:i:s'),
                    'recorded_time_str' => $checkInTimeStr,
                    'time_source' => 'server_fallback_manila_time_extracted'
                ]);
            }
            
            // Update check-in time and set status to pending
            $studentAttendance->update([
                'check_in_time' => $checkInTimeStr,
                'status' => StudentAttendance::STATUS_PENDING,
                'marked_by' => Auth::id(),
            ]);
            
            // Return combined datetime for API response (combine date + time)
            $checkInDateTime = $studentAttendance->check_in_time; // Accessor combines date + time
            return response()->json([
                'success' => true,
                'message' => 'Time in recorded successfully',
                'data' => [
                    'student_attendance_id' => $studentAttendance->id,
                    'check_in_time' => $checkInDateTime ? $checkInDateTime->utc()->toIso8601String() : null,
                    'status' => $studentAttendance->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record time in: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Time Out - Record check-out time for student attendance
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeOut(Request $request)
    {
        try {
            $attendanceId = $request->input('attendance_id');
            $studentAttendanceId = $request->input('student_attendance_id');
            $clientTime = $request->input('client_time'); // ISO 8601 format from client
            
            if (!$attendanceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance ID is required'
                ], 400);
            }
            
            if (!$studentAttendanceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student Attendance ID is required. You must be enrolled in this attendance session.'
                ], 400);
            }
            
            // Find existing student attendance record - must exist
            $studentAttendance = StudentAttendance::with('attendance')->where('id', $studentAttendanceId)
                ->where('attendance_id', $attendanceId)
                ->where('user_id', Auth::id())
                ->first();
            
            // If not found by ID, try to find by attendance_id and user_id
            if (!$studentAttendance) {
                $studentAttendance = StudentAttendance::with('attendance')->where('attendance_id', $attendanceId)
                    ->where('user_id', Auth::id())
                    ->first();
            }
            
            // If still not found, user is not enrolled
            if (!$studentAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not enrolled in this attendance session.'
                ], 404);
            }
            
            if (!$studentAttendance->check_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please time in first before timing out'
                ], 400);
            }
            
            // Get attendance date - we need to use this date for the check-out time
            $attendance = $studentAttendance->attendance;
            if (!$attendance || !$attendance->date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance session date not found.'
                ], 400);
            }
            
            $attendanceDate = $attendance->date instanceof \Carbon\Carbon 
                ? $attendance->date->toDateString()
                : $attendance->date;
            
            // Use digital clock time from client - extract time portion only (H:i:s) for TIME data type
            // The client_time is captured from the digital clock display at the moment of confirmation
            // Client sends UTC time, but we need to extract the Manila local time portion
            $checkOutTimeStr = null;
            if ($clientTime) {
                try {
                    // Parse the UTC time from digital clock (ISO 8601 format)
                    $clientDateTimeUTC = \Carbon\Carbon::parse($clientTime)->utc();
                    
                    // Convert to Manila timezone to get the local time the user sees
                    $clientDateTimeManila = $clientDateTimeUTC->copy()->setTimezone('Asia/Manila');
                    
                    // Extract time portion (H:i:s) from Manila local time
                    // This is the time the user actually sees on their clock
                    // Store only time portion for TIME data type
                    $checkOutTimeStr = $clientDateTimeManila->format('H:i:s');
                    
                    // Log for debugging purposes
                    Log::info('Time Out recorded from digital clock', [
                        'attendance_date' => $attendanceDate,
                        'client_time_utc_iso' => $clientTime,
                        'client_time_utc' => $clientDateTimeUTC->format('Y-m-d H:i:s'),
                        'client_time_manila' => $clientDateTimeManila->format('Y-m-d H:i:s'),
                        'recorded_time_str' => $checkOutTimeStr,
                        'time_source' => 'digital_clock_manila_time_extracted'
                    ]);
                } catch (\Exception $e) {
                    // If parsing fails, use server time in Manila timezone
                    $serverTimeManila = now()->setTimezone('Asia/Manila');
                    $checkOutTimeStr = $serverTimeManila->format('H:i:s');
                    Log::error('Failed to parse digital clock time, using server time', [
                        'digital_clock_time' => $clientTime,
                        'error' => $e->getMessage(),
                        'attendance_date' => $attendanceDate,
                        'server_time_manila' => $serverTimeManila->format('Y-m-d H:i:s'),
                        'recorded_time_str' => $checkOutTimeStr,
                    ]);
                }
            } else {
                // No client time provided, use server time in Manila timezone
                $serverTimeManila = now()->setTimezone('Asia/Manila');
                $checkOutTimeStr = $serverTimeManila->format('H:i:s');
                Log::warning('Time Out recorded without digital clock time, using server time', [
                    'attendance_date' => $attendanceDate,
                    'server_time_manila' => $serverTimeManila->format('Y-m-d H:i:s'),
                    'recorded_time_str' => $checkOutTimeStr,
                    'time_source' => 'server_fallback_manila_time_extracted'
                ]);
            }
            
            // Calculate duration using combined datetime (date + time)
            $checkInDateTime = $studentAttendance->check_in_time; // Accessor combines date + time
            $checkOutDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $attendanceDate . ' ' . $checkOutTimeStr, 'Asia/Manila');
            $durationMinutes = $checkInDateTime->diffInMinutes($checkOutDateTime);
            
            // Update check-out time, duration, and keep status as pending
            $studentAttendance->update([
                'check_out_time' => $checkOutTimeStr,
                'duration_minutes' => $durationMinutes,
                'status' => StudentAttendance::STATUS_PENDING,
            ]);
            
            // Return combined datetime for API response (combine date + time)
            $checkOutDateTime = $studentAttendance->check_out_time; // Accessor combines date + time
            return response()->json([
                'success' => true,
                'message' => 'Time out recorded successfully',
                'data' => [
                    'check_out_time' => $checkOutDateTime ? $checkOutDateTime->utc()->toIso8601String() : null,
                    'duration_minutes' => $durationMinutes,
                    'status' => $studentAttendance->status,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record time out: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student attendance data for a specific attendance session (API endpoint)
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentAttendances(Request $request)
    {
        try {
            $id = $request->query('id');
            
            if (!$id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance session ID is required'
                ], 400);
            }
            
            $attendance = Attendance::with([
                'semester',
                'sections',
                'category',
                'studentAttendances.user',
                'studentAttendances.user.studentInfo',
                'studentAttendances.markedBy',
                'studentAttendances.approvedBy'
            ])->findOrFail($id);

            $studentAttendances = $attendance->studentAttendances->map(function ($studentAttendance) {
                $user = $studentAttendance->user;
                $studentInfo = $user->studentInfo ?? null;
                $photoPath = $user->photo_path ?? $user->avatar ?? null;
                
                // Build image URL
                $imageUrl = null;
                if ($photoPath) {
                    if (str_starts_with($photoPath, 'http://') || str_starts_with($photoPath, 'https://')) {
                        $imageUrl = $photoPath;
                    } else {
                        $imageUrl = asset('storage/' . $photoPath);
                    }
                }
                
                return [
                    'id' => $studentAttendance->id,
                    'user_id' => $studentAttendance->user_id,
                    'student_name' => $user->name ?? 'N/A',
                    'student_id' => $studentInfo->student_number ?? null,
                    'photo_path' => $photoPath,
                    'image_url' => $imageUrl,
                    'status' => $studentAttendance->status,
                    'check_in_time' => $studentAttendance->check_in_time ? $studentAttendance->check_in_time->utc()->toIso8601String() : null,
                    'check_out_time' => $studentAttendance->check_out_time ? $studentAttendance->check_out_time->utc()->toIso8601String() : null,
                    'duration_minutes' => $studentAttendance->duration_minutes,
                    'remarks' => $studentAttendance->remarks,
                    'notes' => $studentAttendance->notes,
                    'is_approved' => $studentAttendance->isApproved(),
                    'approved_at' => $studentAttendance->approved_at?->format('Y-m-d H:i:s'),
                    'approved_by' => $studentAttendance->approvedBy->name ?? null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'attendance' => $attendance,
                    'student_attendances' => $studentAttendances,
                    'stats' => [
                        'total_students' => $attendance->studentAttendances->count(),
                        'present_count' => $attendance->studentAttendances->where('status', StudentAttendance::STATUS_PRESENT)->count(),
                        'absent_count' => $attendance->studentAttendances->where('status', StudentAttendance::STATUS_ABSENT)->count(),
                        'late_count' => $attendance->studentAttendances->where('status', StudentAttendance::STATUS_LATE)->count(),
                        'excused_count' => $attendance->studentAttendances->where('status', StudentAttendance::STATUS_EXCUSED)->count(),
                        'pending_count' => $attendance->studentAttendances->where('status', StudentAttendance::STATUS_PENDING)->count(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student attendance data: ' . $e->getMessage()
            ], 500);
        }
    }
}
