<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceCategory;
use App\Models\Attendance\StudentAttendance;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\StudentInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * AttendanceController
 * 
 * This controller handles all attendance session management operations including
 * creating, viewing, updating, and deleting attendance sessions.
 */
class AttendanceController extends Controller
{
    /**
     * Constructor - Apply authentication middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the attendance management page
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('attendance.index');
    }

    /**
     * Get all attendances with filters
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendances(Request $request)
    {
        try {
            $query = Attendance::with(['semester', 'sections', 'category', 'creator']);

            // Apply filters
            if ($request->has('semester_id') && $request->semester_id) {
                $query->forSemester($request->semester_id);
            }

            if ($request->has('section_id') && $request->section_id) {
                $query->forSection($request->section_id);
            }

            if ($request->has('category_id') && $request->category_id) {
                $query->ofCategory($request->category_id);
            }

            if ($request->has('date') && $request->date) {
                $query->forDate($request->date);
            }

            if ($request->has('start_date') && $request->has('end_date')) {
                $query->dateRange($request->start_date, $request->end_date);
            }

            if ($request->has('is_active')) {
                if ($request->is_active === 'true' || $request->is_active === '1') {
                    $query->active();
                }
            }

            $attendances = $query->orderBy('date', 'desc')
                ->orderBy('start_time', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $attendances
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendances.'
            ], 500);
        }
    }

    /**
     * Get form data for creating/editing attendance
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFormData()
    {
        try {
            // Get categories
            $categories = AttendanceCategory::active()
                ->ordered()
                ->get(['id', 'name', 'slug', 'color', 'icon'])
                ->map(function($category) {
                    return [
                        'value' => $category->id,
                        'label' => $category->name,
                        'color' => $category->color,
                        'icon' => $category->icon
                    ];
                });

            // Get semesters
            $semesters = Semester::where('is_active', true)
                ->orderBy('school_year', 'desc')
                ->get(['id', 'name', 'school_year'])
                ->map(function($semester) {
                    return [
                        'value' => $semester->id,
                        'label' => $semester->name . ' (' . $semester->school_year . ')'
                    ];
                });

            // Get sections
            $sections = Section::where('active', true)
                ->orderBy('year_level')
                ->orderBy('name')
                ->get(['id', 'name', 'year_level'])
                ->map(function($section) {
                    $yearLevel = $section->year_level ? $section->year_level->label() : '';
                    return [
                        'value' => $section->id,
                        'label' => $yearLevel ? "{$section->name} ({$yearLevel})" : $section->name
                    ];
                });

            $data = [
                'categories' => $categories,
                'semesters' => $semesters,
                'sections' => $sections,
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch form data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created attendance session
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Handle "Add All Sections" option first to determine validation rules
        $addAllSections = $request->boolean('add_all_sections', false);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'semester_id' => 'required|exists:semesters,id',
            'section_ids' => $addAllSections ? 'nullable|array' : 'required|array|min:1',
            'section_ids.*' => 'exists:sections,id',
            'add_all_sections' => 'boolean',
            'category_id' => 'nullable|exists:attendance_categories,id',
            'attendance_type' => 'nullable|string|in:' . implode(',', Attendance::getTypes()),
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s|after:start_time',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Custom validation: At least one of start_time, end_time, or location must be provided
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $location = $request->input('location');
        
        if (empty($startTime) && empty($endTime) && empty($location)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'start_time' => ['At least one of Start Time, End Time, or Location must be provided.'],
                    'end_time' => ['At least one of Start Time, End Time, or Location must be provided.'],
                    'location' => ['At least one of Start Time, End Time, or Location must be provided.']
                ]
            ], 422);
        }

        try {
            // Handle "Add All Sections" option
            $sectionIds = $request->input('section_ids', []);
            
            // Ensure section_ids is an array
            if (!is_array($sectionIds)) {
                $sectionIds = $sectionIds ? [$sectionIds] : [];
            }
            
            // Filter out empty values and convert to integers
            $sectionIds = array_filter(array_map('intval', $sectionIds), function($id) {
                return $id > 0;
            });
            
            if ($addAllSections) {
                // Get all active sections
                $allSections = Section::where('active', true)->pluck('id')->toArray();
                if (!empty($allSections)) {
                    $sectionIds = $allSections;
                }
            }

            // Validate that we have at least one section
            if (empty($sectionIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one section or enable "Add All Sections".',
                    'errors' => ['section_ids' => ['At least one section must be selected.']]
                ], 422);
            }

            $data = $request->only([
                'title',
                'description',
                'semester_id',
                'category_id',
                'attendance_type',
                'date',
                'start_time',
                'end_time',
                'location',
                'latitude',
                'longitude',
                'is_active'
            ]);

            $data['created_by'] = Auth::id();
            $data['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;

            // Calculate scheduled duration if times are provided
            if ($request->start_time && $request->end_time) {
                $start = \Carbon\Carbon::parse($request->start_time);
                $end = \Carbon\Carbon::parse($request->end_time);
                $data['scheduled_duration_minutes'] = $start->diffInMinutes($end);
            }

            $attendance = Attendance::create($data);

            // Attach sections using pivot relationship
            if (!empty($sectionIds)) {
                try {
                    $attendance->sections()->attach($sectionIds);
                } catch (\Exception $attachError) {
                    // Don't fail the entire request if attach fails
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance session created successfully.',
                'data' => $attendance->load(['semester', 'sections', 'category', 'creator'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendance session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store student attendances for an attendance session
     * Creates StudentAttendance records for all enrolled students in active semesters
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id Attendance session ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeStudentAttendances(Request $request, $id)
    {
        try {
            $attendance = Attendance::with('sections')->find($id);

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance session not found.'
                ], 404);
            }

            // Get section IDs from the attendance
            $sectionIds = [];
            try {
                $sectionIds = $attendance->sections()->pluck('sections.id')->toArray();
            } catch (\Exception $e) {
                // Alternative method
                $sectionIds = $attendance->sections->pluck('id')->toArray();
            }

            if (empty($sectionIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No sections assigned to this attendance session.'
                ], 400);
            }

            // Create student attendances
            $studentsAdded = $this->createStudentAttendances($attendance, $sectionIds);

            return response()->json([
                'success' => true,
                'message' => "Successfully added {$studentsAdded} student(s) to the attendance.",
                'data' => [
                    'attendance_id' => $attendance->id,
                    'students_added' => $studentsAdded
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store student attendances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create StudentAttendance records for all enrolled students in active semesters
     * for the given attendance session and sections.
     * 
     * @param Attendance $attendance
     * @param array $sectionIds
     * @return int Number of students added
     */
    private function createStudentAttendances(Attendance $attendance, array $sectionIds): int
    {
        if (empty($sectionIds)) {
            return 0;
        }

        try {
            // Get active semester IDs first
            $activeSemesterIds = Semester::where('is_active', true)->pluck('id')->toArray();

            // Get all enrolled students from the specified sections
            // where their semester is active and matches attendance semester
            // Check for both 'enrolled' and 'enroll' status values
            $students = StudentInfo::whereIn('section_id', $sectionIds)
                ->whereIn('status', ['enrolled', 'enroll']) // Support both status values
                ->where('semester_id', $attendance->semester_id) // Match attendance semester
                ->whereIn('semester_id', $activeSemesterIds) // Ensure semester is active
                ->with(['user', 'semester'])
                ->get();

            if ($students->isEmpty()) {
                return 0;
            }

            // Create student attendance records using Eloquent create()
            $createdCount = 0;

            foreach ($students as $student) {
                // Check if StudentAttendance already exists for this attendance and student
                $exists = StudentAttendance::where('attendance_id', $attendance->id)
                    ->where('student_info_id', $student->id)
                    ->exists();

                if (!$exists && $student->user_id) {
                    try {
                        // Use Eloquent create() instead of bulk insert
                        StudentAttendance::create([
                            'attendance_id' => $attendance->id,
                            'user_id' => $student->user_id,
                            'student_info_id' => $student->id,
                            'status' => StudentAttendance::STATUS_ABSENT, // Default to absent
                        ]);
                        
                        $createdCount++;
                    } catch (\Exception $createError) {
                        // Continue with next student instead of failing completely
                        continue;
                    }
                }
            }

            return $createdCount;

        } catch (\Exception $e) {
            // Don't throw exception, just return 0
            return 0;
        }
    }

    /**
     * Display the specified attendance session
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $attendance = Attendance::with([
                'semester',
                'sections',
                'category',
                'creator',
                'studentAttendances.user',
                'studentAttendances.user.studentInfo'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'attendance' => $attendance,
                    'stats' => [
                        'total_students' => $attendance->getTotalStudentCount(),
                        'present_count' => $attendance->getPresentCount(),
                        'absent_count' => $attendance->getAbsentCount(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance session not found.'
            ], 404);
        }
    }

    /**
     * Update the specified attendance session
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance session not found.'
            ], 404);
        }

        // Check if attendance is locked
        if ($attendance->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update locked attendance session.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'semester_id' => 'required|exists:semesters,id',
            'section_ids' => 'required_without:add_all_sections|array',
            'section_ids.*' => 'exists:sections,id',
            'add_all_sections' => 'boolean',
            'category_id' => 'nullable|exists:attendance_categories,id',
            'attendance_type' => 'nullable|string|in:' . implode(',', Attendance::getTypes()),
            'date' => 'required|date',
            'start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s|after:start_time',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Custom validation: At least one of start_time, end_time, or location must be provided
        $startTime = $request->input('start_time');
        $endTime = $request->input('end_time');
        $location = $request->input('location');
        
        if (empty($startTime) && empty($endTime) && empty($location)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => [
                    'start_time' => ['At least one of Start Time, End Time, or Location must be provided.'],
                    'end_time' => ['At least one of Start Time, End Time, or Location must be provided.'],
                    'location' => ['At least one of Start Time, End Time, or Location must be provided.']
                ]
            ], 422);
        }

        try {
            // Handle "Add All Sections" option
            $sectionIds = $request->input('section_ids', []);
            
            // Ensure section_ids is an array
            if (!is_array($sectionIds)) {
                $sectionIds = $sectionIds ? [$sectionIds] : [];
            }
            
            // Filter out empty values and convert to integers
            $sectionIds = array_filter(array_map('intval', $sectionIds), function($id) {
                return $id > 0;
            });
            
            if ($request->has('add_all_sections') && $request->boolean('add_all_sections')) {
                // Get all active sections
                $allSections = Section::where('active', true)->pluck('id')->toArray();
                if (!empty($allSections)) {
                    $sectionIds = $allSections;
                }
            }

            $data = $request->only([
                'title',
                'description',
                'semester_id',
                'category_id',
                'attendance_type',
                'date',
                'start_time',
                'end_time',
                'location',
                'latitude',
                'longitude',
                'is_active'
            ]);

            // Recalculate scheduled duration if times are provided
            if ($request->start_time && $request->end_time) {
                $start = \Carbon\Carbon::parse($request->start_time);
                $end = \Carbon\Carbon::parse($request->end_time);
                $data['scheduled_duration_minutes'] = $start->diffInMinutes($end);
            }

            $attendance->update($data);

            // Sync sections using pivot relationship
            if (!empty($sectionIds)) {
                $attendance->sections()->sync($sectionIds);
            } else {
                $attendance->sections()->detach();
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance session updated successfully.',
                'data' => $attendance->fresh(['semester', 'sections', 'category', 'creator'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance session.'
            ], 500);
        }
    }

    /**
     * Remove the specified attendance session
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);

            // Check if attendance is locked
            if ($attendance->isLocked()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete locked attendance session.'
                ], 403);
            }

            // Get count of student attendances before deletion
            $studentAttendanceCount = $attendance->studentAttendances()->count();

            // Delete associated student attendance records
            $attendance->studentAttendances()->delete();

            // Delete the attendance session
            $attendance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendance session deleted successfully. ' . ($studentAttendanceCount > 0 ? "Deleted {$studentAttendanceCount} associated student attendance record(s)." : '')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lock an attendance session
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function lock($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->lock();

            return response()->json([
                'success' => true,
                'message' => 'Attendance session locked successfully.',
                'data' => $attendance->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to lock attendance session.'
            ], 500);
        }
    }

    /**
     * Unlock an attendance session
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlock($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->unlock();

            return response()->json([
                'success' => true,
                'message' => 'Attendance session unlocked successfully.',
                'data' => $attendance->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unlock attendance session.'
            ], 500);
        }
    }

    /**
     * Approve a student attendance
     * 
     * @param int $id Student attendance ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveStudentAttendance($id)
    {
        try {
            $studentAttendance = StudentAttendance::findOrFail($id);

            // Check if already approved
            if ($studentAttendance->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This attendance has already been approved.'
                ], 400);
            }

            // Check if has check-in time
            if (!$studentAttendance->check_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot approve attendance without check-in time.'
                ], 400);
            }

            // Approve the attendance
            $studentAttendance->approve(Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Student attendance approved successfully.',
                'data' => $studentAttendance->fresh(['user', 'user.studentInfo'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve student attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Disapprove a student attendance
     * 
     * @param int $id Student attendance ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function disapproveStudentAttendance($id)
    {
        try {
            $studentAttendance = StudentAttendance::findOrFail($id);

            // Check if not approved
            if (!$studentAttendance->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This attendance is not approved.'
                ], 400);
            }

            // Disapprove the attendance
            $studentAttendance->unapprove();

            return response()->json([
                'success' => true,
                'message' => 'Student attendance disapproved successfully.',
                'data' => $studentAttendance->fresh(['user', 'user.studentInfo'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disapprove student attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve student attendances
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkApproveStudentAttendances(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:student_attendances,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ids = $request->input('ids', []);
            $approvedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($ids as $id) {
                try {
                    $studentAttendance = StudentAttendance::find($id);
                    
                    if (!$studentAttendance) {
                        $skippedCount++;
                        continue;
                    }

                    // Check if already approved
                    if ($studentAttendance->isApproved()) {
                        $skippedCount++;
                        continue;
                    }

                    // Check if has check-in time
                    if (!$studentAttendance->check_in_time) {
                        $skippedCount++;
                        continue;
                    }

                    // Approve the attendance
                    $studentAttendance->approve(Auth::id());
                    $approvedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to approve attendance ID {$id}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$approvedCount} attendance(s)." . ($skippedCount > 0 ? " {$skippedCount} skipped." : ''),
                'data' => [
                    'approved' => $approvedCount,
                    'skipped' => $skippedCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk approve student attendances: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk disapprove student attendances
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkDisapproveStudentAttendances(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:student_attendances,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ids = $request->input('ids', []);
            $disapprovedCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($ids as $id) {
                try {
                    $studentAttendance = StudentAttendance::find($id);
                    
                    if (!$studentAttendance) {
                        $skippedCount++;
                        continue;
                    }

                    // Check if not approved
                    if (!$studentAttendance->isApproved()) {
                        $skippedCount++;
                        continue;
                    }

                    // Disapprove the attendance
                    $studentAttendance->unapprove();
                    $disapprovedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to disapprove attendance ID {$id}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully disapproved {$disapprovedCount} attendance(s)." . ($skippedCount > 0 ? " {$skippedCount} skipped." : ''),
                'data' => [
                    'disapproved' => $disapprovedCount,
                    'skipped' => $skippedCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk disapprove student attendances: ' . $e->getMessage()
            ], 500);
        }
    }
}
