<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Quarter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * AcademicController
 * 
 * This controller handles academic timeline management operations including
 * displaying semesters and quarters.
 */
class AcademicController extends Controller
{
    /**
     * Constructor - Apply authentication middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the academic timeline page
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Fetch all semesters with their quarters
        $semesters = Semester::with(['quarters' => function ($query) {
            $query->orderBy('name', 'asc');
        }])
        ->orderBy('name', 'asc')
        ->get();

        // Separate into 1st and 2nd semester
        $firstSemester = $semesters->where('name', 'like', '%1st%')->first();
        $secondSemester = $semesters->where('name', 'like', '%2nd%')->first();

        // If not found by name pattern, get by position
        if (!$firstSemester) {
            $firstSemester = $semesters->first();
        }
        if (!$secondSemester) {
            $secondSemester = $semesters->skip(1)->first();
        }

        return view('academic.academic-index');
    }

    /**
     * Get all semesters with quarters
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllSemesters()
    {
        try {
            $semesters = Semester::with(['quarters' => function ($query) {
                $query->orderBy('name', 'asc');
            }])
            ->orderBy('name', 'asc')
            ->get();

            // Helper function to format date safely
            $formatDate = function($date) {
                if (empty($date)) {
                    return null;
                }
                
                try {
                    if (is_object($date) && method_exists($date, 'format')) {
                        return $date->format('Y-m-d');
                    }
                    
                    if (is_string($date)) {
                        $parsed = \Carbon\Carbon::parse($date);
                        return $parsed->format('Y-m-d');
                    }
                    
                    return null;
                } catch (\Exception $e) {
                    return is_string($date) ? substr($date, 0, 10) : null;
                }
            };

            // Format semesters data
            $formattedSemesters = $semesters->map(function($semester) use ($formatDate) {
                $quarters = $semester->quarters->map(function($quarter) {
                    return [
                        'id' => $quarter->id,
                        'name' => $quarter->name,
                        'description' => $quarter->description,
                        'is_active' => (bool)$quarter->is_active,
                    ];
                });

                return [
                    'id' => (int)$semester->id,
                    'name' => (string)($semester->name ?? ''),
                    'school_year' => (string)($semester->school_year ?? ''),
                    'start_date' => $formatDate($semester->start_date),
                    'end_date' => $formatDate($semester->end_date),
                    'is_active' => (bool)($semester->is_active ?? false),
                    'is_display' => (bool)($semester->is_display ?? false),
                    'quarters' => $quarters,
                    'quarters_count' => $quarters->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedSemesters
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching semesters', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch semesters: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get semester data by ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSemester($id)
    {
        try {
            // Validate ID
            if (!is_numeric($id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid semester ID'
                ], 400);
            }

            $semester = Semester::with(['quarters' => function ($query) {
                $query->orderBy('name', 'asc');
            }])->findOrFail($id);

            // Helper function to format date safely
            $formatDate = function($date) {
                if (empty($date)) {
                    return null;
                }
                
                try {
                    // If it's a Carbon instance
                    if (is_object($date) && method_exists($date, 'format')) {
                        return $date->format('Y-m-d');
                    }
                    
                    // If it's a string, parse it
                    if (is_string($date)) {
                        $parsed = \Carbon\Carbon::parse($date);
                        return $parsed->format('Y-m-d');
                    }
                    
                    return null;
                } catch (\Exception $e) {
                    Log::warning('Date formatting error: ' . $e->getMessage(), ['date' => $date]);
                    return is_string($date) ? substr($date, 0, 10) : null;
                }
            };

            $quarters = $semester->quarters->map(function($quarter) {
                return [
                    'id' => $quarter->id,
                    'name' => $quarter->name,
                    'description' => $quarter->description,
                    'is_active' => (bool)$quarter->is_active,
                ];
            });

            return response()->json([
                'success' => true,
                'id' => (int)$semester->id,
                'name' => (string)($semester->name ?? ''),
                'school_year' => (string)($semester->school_year ?? ''),
                'start_date' => $formatDate($semester->start_date),
                'end_date' => $formatDate($semester->end_date),
                'is_active' => (bool)($semester->is_active ?? false),
                'is_display' => (bool)($semester->is_display ?? false),
                'quarters' => $quarters,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Semester not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching semester', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'semester_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch semester: ' . $e->getMessage(),
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    /**
     * Update semester
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSemester(Request $request, $id)
    {
        try {
            $semester = Semester::findOrFail($id);

            // Validate input
            $validator = Validator::make($request->all(), [
                'school_year' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^\d{4}-\d{4}$/',
                    // Ensure uniqueness per semester name (e.g., only one 1st Semester for a given school_year)
                    Rule::unique('semesters', 'school_year')
                        ->where(function ($query) use ($semester) {
                            return $query->where('name', $semester->name);
                        })
                        ->ignore($semester->id),
                ],
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'is_active' => 'nullable|boolean',
            ], [
                'school_year.required' => 'School year is required',
                'school_year.regex' => 'School year must be in the format YYYY-YYYY.',
                'school_year.unique' => 'This school year is already used for this semester.',
                'start_date.required' => 'Start date is required',
                'start_date.date' => 'Start date must be a valid date',
                'end_date.required' => 'End date is required',
                'end_date.date' => 'End date must be a valid date',
                'end_date.after_or_equal' => 'End date must be after or equal to start date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Determine requested active status
            $newIsActive = false;
            if ($request->has('is_active')) {
                $newIsActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            } elseif ($request->input('is_active') !== null) {
                $newIsActive = (bool) $request->input('is_active');
            }

            DB::transaction(function () use ($semester, $request, $newIsActive) {
                // If this semester is being set to active, deactivate all other semesters
                if ($newIsActive) {
                    $otherSemesterIds = Semester::where('school_year', $semester->school_year)
                        ->where('id', '<>', $semester->id)
                        ->pluck('id');

                    if ($otherSemesterIds->isNotEmpty()) {
                        Semester::whereIn('id', $otherSemesterIds)->update(['is_active' => false]);
                        Quarter::whereIn('semester_id', $otherSemesterIds)->update(['is_active' => false]);
                    }
                }

                // Update this semester (name is fixed and not editable)
                $semester->school_year = $request->school_year;
                $semester->start_date = $request->start_date;
                $semester->end_date = $request->end_date;
                $semester->is_active = $newIsActive;
                $semester->save();

                // If semester is active, set all its quarters to active as well
                if ($newIsActive) {
                    Quarter::where('semester_id', $semester->id)->update(['is_active' => true]);
                }

                // Refresh the model to get updated values
                $semester->refresh();
            });

            // Helper function to format date safely
            $formatDate = function($date) {
                if (empty($date)) {
                    return null;
                }
                
                try {
                    if (is_object($date) && method_exists($date, 'format')) {
                        return $date->format('Y-m-d');
                    }
                    
                    if (is_string($date)) {
                        $parsed = \Carbon\Carbon::parse($date);
                        return $parsed->format('Y-m-d');
                    }
                    
                    return null;
                } catch (\Exception $e) {
                    return is_string($date) ? substr($date, 0, 10) : null;
                }
            };

            return response()->json([
                'success' => true,
                'message' => 'Semester updated successfully',
                'data' => [
                    'id' => (int)$semester->id,
                    'name' => (string)($semester->name ?? ''),
                    'school_year' => (string)($semester->school_year ?? ''),
                    'start_date' => $formatDate($semester->start_date),
                    'end_date' => $formatDate($semester->end_date),
                    'is_active' => (bool)($semester->is_active ?? false),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Semester not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating semester', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'semester_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update semester: ' . $e->getMessage(),
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    /**
     * Update only the active status of a semester.
     * When a semester is activated, all of its quarters will also be activated.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSemesterStatus(Request $request, $id)
    {
        try {
            $semester = Semester::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'is_active' => 'required|boolean',
            ], [
                'is_active.required' => 'Status is required.',
                'is_active.boolean' => 'Status must be true or false.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $newIsActive = (bool) $request->is_active;

            DB::transaction(function () use ($semester, $newIsActive) {
                if ($newIsActive) {
                    // Deactivate any other semesters in the same school year
                    $otherSemesterIdsSameYear = Semester::where('school_year', $semester->school_year)
                        ->where('id', '<>', $semester->id)
                        ->pluck('id');

                    if ($otherSemesterIdsSameYear->isNotEmpty()) {
                        Semester::whereIn('id', $otherSemesterIdsSameYear)->update(['is_active' => false]);
                        Quarter::whereIn('semester_id', $otherSemesterIdsSameYear)->update(['is_active' => false]);
                    }

                    // Also ensure that any semesters currently displayed on the cards are set to inactive,
                    // regardless of school year. Their quarters are also deactivated.
                    $displayedIds = Semester::where('is_display', true)
                        ->where('id', '<>', $semester->id)
                        ->pluck('id');

                    if ($displayedIds->isNotEmpty()) {
                        Semester::whereIn('id', $displayedIds)->update(['is_active' => false]);
                        Quarter::whereIn('semester_id', $displayedIds)->update(['is_active' => false]);
                    }
                }

                $semester->is_active = $newIsActive;

                // If this is a 1st Semester being activated, update display flags so cards show this pair.
                $name = strtolower($semester->name ?? '');
                if ($newIsActive && (str_contains($name, '1st') || str_contains($name, 'first'))) {
                    // Clear display flags from all 1st/2nd semesters
                    Semester::whereIn('name', ['1st Semester', '2nd Semester'])->update(['is_display' => false]);

                    // Mark this 1st Semester as displayed
                    $semester->is_display = true;

                    // Also mark the matching 2nd Semester (same school year) as displayed if it exists
                    $second = Semester::where('school_year', $semester->school_year)
                        ->where('name', '2nd Semester')
                        ->first();
                    if ($second) {
                        $second->is_display = true;
                        $second->save();
                    }
                }

                $semester->save();

                if ($newIsActive) {
                    // Activate all quarters belonging to this semester
                    Quarter::where('semester_id', $semester->id)->update(['is_active' => true]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Semester status updated successfully',
                'id' => (int) $semester->id,
                'is_active' => (bool) $semester->is_active,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Semester not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating semester status', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'semester_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update semester status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store (create) a new semester.
     *
     * Used by the "Create New Semester" modal on the Academic Timeline page.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeSemester(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    // Limit to the two allowed semesters by business rule
                    Rule::in(['1st Semester', '2nd Semester']),
                ],
                'school_year' => [
                    'required',
                    'string',
                    'max:255',
                    'regex:/^\d{4}-\d{4}$/',
                    // Ensure uniqueness per semester name (e.g., only one 1st Semester for a given school_year)
                    Rule::unique('semesters', 'school_year')
                        ->where(function ($query) use ($request) {
                            return $query->where('name', $request->input('name'));
                        }),
                ],
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'is_active' => 'nullable|boolean',
            ], [
                'name.required' => 'Semester is required',
                'name.in' => 'Semester must be either 1st Semester or 2nd Semester.',
                'school_year.required' => 'School year is required',
                'school_year.regex' => 'School year must be in the format YYYY-YYYY.',
                'school_year.unique' => 'This school year is already used for this semester.',
                'start_date.required' => 'Start date is required',
                'start_date.date' => 'Start date must be a valid date',
                'end_date.required' => 'End date is required',
                'end_date.date' => 'End date must be a valid date',
                'end_date.after_or_equal' => 'End date must be after or equal to start date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Determine requested active status
            $newIsActive = false;
            if ($request->has('is_active')) {
                $newIsActive = filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN);
            } elseif ($request->input('is_active') !== null) {
                $newIsActive = (bool) $request->input('is_active');
            }

            $semester = new Semester();

            DB::transaction(function () use (&$semester, $request, $newIsActive) {
                // If this semester is being set to active, deactivate existing displayed semesters and their quarters,
                // and reset display flags. Display is driven by is_display, independent from is_active.
                if ($newIsActive) {
                    $otherSemesterIds = Semester::whereIn('name', ['1st Semester', '2nd Semester'])
                        ->pluck('id');

                    if ($otherSemesterIds->isNotEmpty()) {
                        Semester::whereIn('id', $otherSemesterIds)->update([
                            'is_active' => false,
                            'is_display' => false,
                        ]);
                        Quarter::whereIn('semester_id', $otherSemesterIds)->update(['is_active' => false]);
                    }
                }

                $semester->name = $request->name;
                $semester->school_year = $request->school_year;
                $semester->start_date = $request->start_date;
                $semester->end_date = $request->end_date;
                $semester->is_active = $newIsActive;
                // Newly created semester pair should be the one displayed on cards
                $semester->is_display = in_array($request->name, ['1st Semester', '2nd Semester'], true);
                $semester->save();

                // Auto-create quarters for this semester if none exist yet
                if ($semester->id && !$semester->quarters()->exists()) {
                    $name = strtolower($semester->name ?? '');

                    if (str_contains($name, '1st') || str_contains($name, 'first')) {
                        // 1st Semester -> 1st & 2nd Quarter
                        $q1 = new Quarter();
                        $q1->semester_id = $semester->id;
                        $q1->name = '1st Quarter';
                        $q1->description = 'Automatically created for 1st Semester';
                        $q1->is_active = $newIsActive; // follow semester active state
                        $q1->save();

                        $q2 = new Quarter();
                        $q2->semester_id = $semester->id;
                        $q2->name = '2nd Quarter';
                        $q2->description = 'Automatically created for 1st Semester';
                        $q2->is_active = $newIsActive;
                        $q2->save();
                    } elseif (str_contains($name, '2nd') || str_contains($name, 'second')) {
                        // 2nd Semester -> 3rd & 4th Quarter
                        $q3 = new Quarter();
                        $q3->semester_id = $semester->id;
                        $q3->name = '3rd Quarter';
                        $q3->description = 'Automatically created for 2nd Semester';
                        $q3->is_active = $newIsActive;
                        $q3->save();

                        $q4 = new Quarter();
                        $q4->semester_id = $semester->id;
                        $q4->name = '4th Quarter';
                        $q4->description = 'Automatically created for 2nd Semester';
                        $q4->is_active = $newIsActive;
                        $q4->save();
                    }
                }

                // If semester is active, set all its quarters to active as well
                if ($newIsActive && $semester->id) {
                    Quarter::where('semester_id', $semester->id)->update(['is_active' => true]);
                }
            });

            // Helper function to format date safely
            $formatDate = function ($date) {
                if (empty($date)) {
                    return null;
                }

                try {
                    if (is_object($date) && method_exists($date, 'format')) {
                        return $date->format('Y-m-d');
                    }

                    if (is_string($date)) {
                        $parsed = \Carbon\Carbon::parse($date);
                        return $parsed->format('Y-m-d');
                    }

                    return null;
                } catch (\Exception $e) {
                    return is_string($date) ? substr($date, 0, 10) : null;
                }
            };

            return response()->json([
                'success' => true,
                'message' => 'Semester created successfully',
                'data' => [
                    'id' => (int) $semester->id,
                    'name' => (string) ($semester->name ?? ''),
                    'school_year' => (string) ($semester->school_year ?? ''),
                    'start_date' => $formatDate($semester->start_date),
                    'end_date' => $formatDate($semester->end_date),
                    'is_active' => (bool) ($semester->is_active ?? false),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating semester', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create semester: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a specific quarter's active status.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateQuarter(Request $request, $id)
    {
        try {
            $quarter = Quarter::with('semester')->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'is_active' => 'required|boolean',
            ], [
                'is_active.required' => 'Status is required.',
                'is_active.boolean' => 'Status must be true or false.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $newIsActive = (bool) $request->is_active;

            // Business rule: a quarter cannot be active if its semester is not active
            if ($newIsActive) {
                $semester = $quarter->semester;
                if (!$semester || !$semester->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot activate this quarter because its semester is inactive.',
                    ], 422);
                }
            }

            DB::transaction(function () use ($quarter, $newIsActive) {
                // If this quarter is being set to active, deactivate all other quarters in the same semester
                if ($newIsActive && $quarter->semester_id) {
                    Quarter::where('semester_id', $quarter->semester_id)
                        ->where('id', '<>', $quarter->id)
                        ->update(['is_active' => false]);
                }

                $quarter->update([
                    'is_active' => $newIsActive,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Quarter status updated successfully',
                'id' => (int) $quarter->id,
                'is_active' => (bool) $quarter->is_active,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Quarter not found',
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error updating quarter', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'quarter_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update quarter: ' . $e->getMessage(),
            ], 500);
        }
    }
}
