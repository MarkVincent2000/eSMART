<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\StudentAttendance;
use App\Models\Notification;
use App\Models\Comment;
use App\Models\CommentAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
    
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

            // --- NEW: NOTIFY THE CREATOR ---
            try {
                $creatorId = $attendance->created_by;
                $studentName = $studentAttendance->user->name ?? 'A student';
                $formattedTime = \Carbon\Carbon::createFromFormat('H:i:s', $checkInTimeStr)->format('g:i A');

                if ($creatorId) {
                    Notification::create([
                        'user_id' => $creatorId,
                        'type' => 'student_timed_in',
                        'title' => "⏰ Student Timed In: {$attendance->title}",
                        'body' => "{$studentName} has timed in at {$formattedTime} for your session: {$attendance->title}.",
                        'url' => "/attendance?id={$attendance->id}", // Link to the specific attendance management page
                        'data' => [
                            'attendance_id' => $attendance->id,
                            'student_id' => Auth::id(),
                            'student_name' => $studentName,
                            'check_in_time' => $checkInTimeStr
                        ],
                        'notifiable_id' => $studentAttendance->id,
                        'notifiable_type' => StudentAttendance::class,
                        'read_at' => null,
                    ]);
                }
            } catch (\Exception $notifyError) {
                // We log the error but don't stop the Time In process from succeeding
                Log::error('Failed to notify creator of time-in: ' . $notifyError->getMessage());
            }
            
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

            // --- NEW: NOTIFY THE CREATOR ---
            try {
                $creatorId = $attendance->created_by;
                $studentName = $studentAttendance->user->name ?? 'A student';
                $formattedTime = \Carbon\Carbon::createFromFormat('H:i:s', $checkOutTimeStr)->format('g:i A');

                if ($creatorId) {
                    Notification::create([
                        'user_id' => $creatorId,
                        'type' => 'student_timed_out',
                        'title' => "⏰ Student Timed Out: {$attendance->title}",
                        'body' => "{$studentName} has timed out at {$formattedTime} for your session: {$attendance->title}.",
                        'url' => "/attendance?id={$attendance->id}", // Link to the specific attendance management page
                        'data' => [
                            'attendance_id' => $attendance->id,
                            'student_id' => Auth::id(),
                            'student_name' => $studentName,
                            'check_out_time' => $checkOutTimeStr
                        ],
                        'notifiable_id' => $studentAttendance->id,
                        'notifiable_type' => StudentAttendance::class,
                        'read_at' => null,
                    ]);
                }
            } catch (\Exception $notifyError) {
                // We log the error but don't stop the Time Out process from succeeding
                Log::error('Failed to notify creator of time-out: ' . $notifyError->getMessage());
            }
            
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

    /**
     * Get comments for a student attendance
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getComments(Request $request)
    {
        try {
            $studentAttendanceId = $request->input('student_attendance_id');
            $attendanceId = $request->input('attendance_id');
            
            // If attendance_id is provided, check if user is the creator
            if ($attendanceId && !$studentAttendanceId) {
                $attendance = Attendance::findOrFail($attendanceId);
                
                // Check if user is the creator
                if ($attendance->created_by !== Auth::id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to view comments for this attendance session'
                    ], 403);
                }
                
                // Get all comments from all student attendances in this attendance session
                $studentAttendances = StudentAttendance::where('attendance_id', $attendanceId)
                    ->with('attendance')
                    ->get();
                $allComments = collect();
                
                foreach ($studentAttendances as $studentAttendance) {
                    $comments = $studentAttendance->comments()
                        ->with(['user', 'replies.user', 'replies.attachments', 'attachments'])
                        ->get();
                    // Ensure each comment has the studentAttendance relationship loaded
                    foreach ($comments as $comment) {
                        $comment->setRelation('commentable', $studentAttendance);
                    }
                    $allComments = $allComments->merge($comments);
                }
                
                // Sort by created_at descending
                $comments = $allComments->sortByDesc('created_at')->values();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'comments' => $comments->map(function ($comment) {
                            return $this->formatComment($comment);
                        })
                    ]
                ]);
            }
            
            // Original logic: get comments for a specific student attendance
            $request->validate([
                'student_attendance_id' => 'required|exists:student_attendances,id',
            ]);

            $studentAttendance = StudentAttendance::with(['comments.user', 'comments.attachments'])
                ->findOrFail($studentAttendanceId);

            // Get top-level comments with replies and attachments
            $comments = $studentAttendance->comments()
                ->with(['user', 'replies.user', 'replies.attachments', 'attachments'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'comments' => $comments->map(function ($comment) {
                        return $this->formatComment($comment);
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch comments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new comment
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeComment(Request $request)
    {
        try {
            $studentAttendanceId = $request->input('student_attendance_id');
            $attendanceId = $request->input('attendance_id');
            
            // Validate based on which parameter is provided
            if ($attendanceId && !$studentAttendanceId) {
                // Creator posting via attendance_id
                $validator = Validator::make($request->all(), [
                    'attendance_id' => 'required|exists:attendances,id',
                    'body' => 'required|string|max:5000',
                    'parent_id' => 'nullable|exists:comments,id',
                    'attachments' => 'nullable|array|max:5',
                    'attachments.*' => 'file|max:5120|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt',
                ]);
                
                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                // Verify user is the creator
                $attendance = Attendance::findOrFail($attendanceId);
                if ($attendance->created_by !== Auth::id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You are not authorized to post comments for this attendance session'
                    ], 403);
                }
                
                // Find the first StudentAttendance in this attendance session to attach the comment to
                $studentAttendance = StudentAttendance::where('attendance_id', $attendanceId)->first();
                if (!$studentAttendance) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No student attendance records found for this session'
                    ], 404);
                }
            } else {
                // Regular user posting via student_attendance_id
                $validator = Validator::make($request->all(), [
                    'student_attendance_id' => 'required|exists:student_attendances,id',
                    'body' => 'required|string|max:5000',
                    'parent_id' => 'nullable|exists:comments,id',
                    'attachments' => 'nullable|array|max:5',
                    'attachments.*' => 'file|max:5120|mimes:jpeg,jpg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }

                $studentAttendance = StudentAttendance::findOrFail($request->student_attendance_id);
            }

            // Create comment
            $comment = Comment::create([
                'commentable_type' => StudentAttendance::class,
                'commentable_id' => $studentAttendance->id,
                'user_id' => Auth::id(),
                'body' => $request->body,
                'parent_id' => $request->parent_id,
                'status' => Comment::STATUS_PUBLISHED,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Handle file uploads
            if ($request->hasFile('attachments')) {
                $displayOrder = 0;
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('comment-attachments', 'public');
                    
                    CommentAttachment::create([
                        'comment_id' => $comment->id,
                        'file_name' => basename($path),
                        'file_path' => $path,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'display_order' => $displayOrder++,
                    ]);
                }
            }

            // Load relationships
            $comment->load(['user', 'attachments', 'parent.user']);
            
            // Load attendance for notifications
            $attendance = $studentAttendance->attendance ?? null;
            if (!$attendance && $attendanceId) {
                $attendance = Attendance::find($attendanceId);
            }
            if (!$attendance) {
                $studentAttendance->load('attendance');
                $attendance = $studentAttendance->attendance;
            }

            // Send notifications
            try {
                $commentAuthor = $comment->user;
                $commentAuthorName = $commentAuthor->name ?? 'Someone';
                $attendanceTitle = $attendance->title ?? 'Attendance Session';
                
                if ($comment->parent_id) {
                    // This is a reply - notify the parent comment author (if not replying to self)
                    $parentComment = $comment->parent;
                    if ($parentComment && $parentComment->user_id !== Auth::id()) {
                        $parentAuthor = $parentComment->user;
                        $replyPreview = substr($comment->body, 0, 100) . (strlen($comment->body) > 100 ? '...' : '');
                        Notification::create([
                            'user_id' => $parentAuthor->id,
                            'type' => 'comment_reply',
                            'title' => "💬 New Reply: {$attendanceTitle}",
                            'body' => "{$commentAuthorName} replied to your comment: {$replyPreview}",
                            'url' => "/attendance?id={$attendance->id}",
                            'data' => [
                                'attendance_id' => $attendance->id,
                                'comment_id' => $comment->id,
                                'parent_comment_id' => $parentComment->id,
                                'comment_author_id' => Auth::id(),
                                'comment_author_name' => $commentAuthorName,
                            ],
                            'notifiable_id' => $comment->id,
                            'notifiable_type' => Comment::class,
                            'read_at' => null,
                        ]);
                    }
                } else {
                    // This is a top-level comment - notify all students and creator (except comment author)
                    $attendance->load('studentAttendances.user');
                    
                    // Get all unique user IDs from student attendances
                    $userIdsToNotify = $attendance->studentAttendances
                        ->pluck('user_id')
                        ->filter()
                        ->unique()
                        ->toArray();
                    
                    // Add creator if exists and not already in the list
                    if ($attendance->created_by && !in_array($attendance->created_by, $userIdsToNotify)) {
                        $userIdsToNotify[] = $attendance->created_by;
                    }
                    
                    // Remove comment author from notification list
                    $userIdsToNotify = array_filter($userIdsToNotify, function($userId) use ($comment) {
                        return (int)$userId !== (int)$comment->user_id;
                    });
                    
                    // Create notifications for each user
                    $commentPreview = substr($comment->body, 0, 100) . (strlen($comment->body) > 100 ? '...' : '');
                    foreach ($userIdsToNotify as $userId) {
                        Notification::create([
                            'user_id' => $userId,
                            'type' => 'new_comment',
                            'title' => "💬 New Comment: {$attendanceTitle}",
                            'body' => "{$commentAuthorName} commented: {$commentPreview}",
                            'url' => "/attendance?id={$attendance->id}",
                            'data' => [
                                'attendance_id' => $attendance->id,
                                'comment_id' => $comment->id,
                                'comment_author_id' => Auth::id(),
                                'comment_author_name' => $commentAuthorName,
                            ],
                            'notifiable_id' => $comment->id,
                            'notifiable_type' => Comment::class,
                            'read_at' => null,
                        ]);
                    }
                }
            } catch (\Exception $notifyError) {
                // Log error but don't fail the comment creation
                Log::error('Failed to send comment notifications: ' . $notifyError->getMessage(), [
                    'comment_id' => $comment->id,
                    'error' => $notifyError
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comment posted successfully',
                'data' => [
                    'comment' => $this->formatComment($comment)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to store comment: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except(['attachments'])
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to post comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a comment
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateComment(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'body' => 'required|string|max:5000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $comment = Comment::with(['commentable'])->findOrFail($id);

            // Check if user owns the comment OR is the attendance creator
            $isOwner = (int)$comment->user_id === (int)Auth::id();
            $isCreator = false;
            
            // Check if comment is on a StudentAttendance and user is the attendance creator
            if ($comment->commentable_type === StudentAttendance::class && $comment->commentable) {
                $studentAttendance = $comment->commentable;
                // Load attendance relationship if not already loaded
                if (!$studentAttendance->relationLoaded('attendance')) {
                    $studentAttendance->load('attendance');
                }
                $attendance = $studentAttendance->attendance ?? null;
                if ($attendance && (int)$attendance->created_by === (int)Auth::id()) {
                    $isCreator = true;
                }
            }
            
            if (!$isOwner && !$isCreator) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to edit this comment'
                ], 403);
            }

            // Update comment
            $comment->update([
                'body' => $request->body,
            ]);

            // Load relationships
            $comment->load(['user', 'attachments', 'parent.user']);

            return response()->json([
                'success' => true,
                'message' => 'Comment updated successfully',
                'data' => [
                    'comment' => $this->formatComment($comment)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update comment: ' . $e->getMessage(), [
                'exception' => $e,
                'comment_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a comment
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment($id)
    {
        try {
            $comment = Comment::with(['commentable'])->findOrFail($id);

            // Check if user owns the comment OR is the attendance creator
            $isOwner = (int)$comment->user_id === (int)Auth::id();
            $isCreator = false;
            
            // Check if comment is on a StudentAttendance and user is the attendance creator
            if ($comment->commentable_type === StudentAttendance::class && $comment->commentable) {
                $studentAttendance = $comment->commentable;
                // Load attendance relationship if not already loaded
                if (!$studentAttendance->relationLoaded('attendance')) {
                    $studentAttendance->load('attendance');
                }
                $attendance = $studentAttendance->attendance ?? null;
                if ($attendance && (int)$attendance->created_by === (int)Auth::id()) {
                    $isCreator = true;
                }
            }
            
            if (!$isOwner && !$isCreator) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this comment'
                ], 403);
            }

            // Delete comment (soft delete if using SoftDeletes)
            $comment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete comment: ' . $e->getMessage(), [
                'exception' => $e,
                'comment_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete comment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format comment for JSON response
     * 
     * @param \App\Models\Comment $comment
     * @return array
     */
    private function formatComment($comment)
    {
        $user = $comment->user;
        $avatarUrl = $user->photo_path 
            ? asset('storage/' . $user->photo_path) 
            : asset('build/images/users/user-dummy-img.jpg');

        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'user_id' => $comment->user_id,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar_url' => $avatarUrl,
            ],
            'parent_id' => $comment->parent_id,
            'parent' => $comment->parent ? [
                'id' => $comment->parent->id,
                'user' => [
                    'name' => $comment->parent->user->name ?? 'Unknown',
                ]
            ] : null,
            'replies' => $comment->replies->map(function ($reply) {
                return $this->formatComment($reply);
            }),
            'attachments' => $comment->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_url' => $attachment->url,
                    'file_type' => $attachment->file_type,
                    'file_size' => $attachment->file_size,
                    'original_name' => $attachment->original_name,
                    'is_image' => $attachment->isImage(),
                    'is_document' => $attachment->isDocument(),
                ];
            }),
            'created_at' => $comment->created_at->toISOString(),
            'created_at_formatted' => $comment->created_at->format('d M Y - h:iA'),
            'created_at_human' => $comment->created_at->diffForHumans(),
        ];
    }
}
