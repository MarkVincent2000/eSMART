<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\AttendanceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * AttendanceCategoryController
 * 
 * This controller manages all CRUD operations for attendance categories.
 * Attendance categories are used to classify different types of attendance sessions
 * (e.g., Class, Laboratory, Lecture, Exam, Event, etc.)
 */
class AttendanceCategoryController extends Controller
{
    /**
     * Constructor - Apply authentication middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of attendance categories
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $categories = AttendanceCategory::with(['attendances' => function($query) {
                $query->with(['semester', 'sections', 'creator'])
                    ->orderBy('date', 'desc')
                    ->orderBy('created_at', 'desc'); // Sort by newest first
            }])
                ->orderBy('display_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching attendance categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories.'
            ], 500);
        }
    }

    /**
     * Get all active attendance categories
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveCategories()
    {
        try {
            $categories = AttendanceCategory::active()
                ->ordered()
                ->get(['id', 'name', 'slug', 'description', 'color', 'icon']);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching active categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active categories.'
            ], 500);
        }
    }

    /**
     * Store a newly created attendance category
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:attendance_categories,name',
            'slug' => 'nullable|string|max:255|unique:attendance_categories,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'name',
                'slug',
                'description',
                'color',
                'icon',
                'is_active',
                'display_order'
            ]);

            // Set defaults
            $data['is_active'] = $request->has('is_active') ? (bool) $request->is_active : true;
            $data['display_order'] = $request->display_order ?? 0;
            $data['color'] = $request->color ?? '#6366f1';

            $category = AttendanceCategory::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => $category
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating attendance category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category.'
            ], 500);
        }
    }

    /**
     * Display the specified attendance category
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $category = AttendanceCategory::with('attendances')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'category' => $category,
                    'attendance_count' => $category->getAttendanceCount(),
                    'active_attendance_count' => $category->getActiveAttendanceCount()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }
    }

    /**
     * Update the specified attendance category
     * 
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $category = AttendanceCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:attendance_categories,name,' . $id,
            'slug' => 'nullable|string|max:255|unique:attendance_categories,slug,' . $id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->only([
                'name',
                'slug',
                'description',
                'color',
                'icon',
                'is_active',
                'display_order'
            ]);

            $category->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => $category->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating attendance category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category.'
            ], 500);
        }
    }

    /**
     * Remove the specified attendance category
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $category = AttendanceCategory::findOrFail($id);

            // Check if category has attendances
            if ($category->attendances()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with existing attendances. Please reassign or delete attendances first.'
                ], 400);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category.'
            ], 500);
        }
    }

    /**
     * Activate a category
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate($id)
    {
        try {
            $category = AttendanceCategory::findOrFail($id);
            $category->activate();

            return response()->json([
                'success' => true,
                'message' => 'Category activated successfully.',
                'data' => $category->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error activating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate category.'
            ], 500);
        }
    }

    /**
     * Deactivate a category
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivate($id)
    {
        try {
            $category = AttendanceCategory::findOrFail($id);
            $category->deactivate();

            return response()->json([
                'success' => true,
                'message' => 'Category deactivated successfully.',
                'data' => $category->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Error deactivating category: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate category.'
            ], 500);
        }
    }

    /**
     * Bulk update display order of categories
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:attendance_categories,id',
            'categories.*.display_order' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            foreach ($request->categories as $categoryData) {
                AttendanceCategory::where('id', $categoryData['id'])
                    ->update(['display_order' => $categoryData['display_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Category order updated successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating category order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category order.'
            ], 500);
        }
    }
}
