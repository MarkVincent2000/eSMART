<?php

namespace App\Http\Controllers\Engagement;

use App\Http\Controllers\Controller;
use App\Models\Event\Event;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\StudentInfo;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EngagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('engagement.index');
    }

    public function getCalendarEvents()
    {
        $colorMap = [
            'academic' => 'bg-primary-subtle',
            'social' => 'bg-success-subtle',
            'sports' => 'bg-info-subtle',
            'cultural' => 'bg-warning-subtle',
            'workshop' => 'bg-danger-subtle',
            'seminar' => 'bg-dark-subtle',
            'conference' => 'bg-primary-subtle',
            'ceremony' => 'bg-success-subtle',
            'meeting' => 'bg-info-subtle',
            'other' => 'bg-secondary-subtle',
        ];

        $events = Event::all()->map(function($event) use ($colorMap) {
            return $this->formatEventForCalendar($event, $colorMap);
        })->toArray();

        return response()->json($events);
    }

    public function getEvent($id)
    {
        $event = Event::with(['semester', 'section', 'creator'])->find($id);
        
        if (!$event) {
            return response()->json(['error' => 'Event not found.'], 404);
        }

        // Format the event data for display
        $eventData = [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'event_type' => $event->event_type,
            'category' => $event->category,
            'start_date' => $event->start_date ? $event->start_date->format('Y-m-d') : null,
            'end_date' => $event->end_date ? $event->end_date->format('Y-m-d') : null,
            'start_time' => $event->start_time ? $event->start_time->format('Y-m-d H:i:s') : null,
            'end_time' => $event->end_time ? $event->end_time->format('Y-m-d H:i:s') : null,
            'location' => $event->location,
            'semester_id' => $event->semester_id,
            'section_id' => $event->section_id,
            'status' => $event->status,
            'semester_name' => $event->semester ? $event->semester->name . ' (' . $event->semester->school_year . ')' : null,
            'section_name' => $event->section ? $event->section->name : null,
            'created_by' => $event->created_by,
            'creator_name' => $event->creator ? $event->creator->name : null,
        ];

        return response()->json($eventData);
    }

    public function getFormData()
    {
        $activeSemester = Semester::where('is_active', true)->first();
        
        $data = [
            'semesterOptions' => Semester::where('is_active', true)
                ->orderBy('school_year', 'desc')
                ->orderBy('name')
                ->get()
                ->map(function($semester) {
                    return [
                        'value' => $semester->id,
                        'label' => $semester->name . ' (' . $semester->school_year . ')'
                    ];
                })->toArray(),
            'sectionOptions' => Section::where('active', true)
                ->orderBy('name')
                ->get()
                ->map(function($section) {
                    return [
                        'value' => $section->id,
                        'label' => $section->name
                    ];
                })->toArray(),
            'eventTypeOptions' => collect(Event::getTypes())->map(function($type) {
                return [
                    'value' => $type,
                    'label' => ucfirst($type)
                ];
            })->toArray(),
            'eventStatusOptions' => collect(Event::getStatuses())->map(function($status) {
                return [
                    'value' => $status,
                    'label' => ucfirst($status)
                ];
            })->toArray(),
            'activeSemesterId' => $activeSemester ? $activeSemester->id : null,
        ];

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:' . implode(',', Event::getTypes()),
            'category' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i' . ($request->start_time ? '|after:start_time' : ''),
            'location' => 'nullable|string|max:255',
            'semester_id' => 'nullable|exists:semesters,id',
            'section_id' => 'nullable|exists:sections,id',
            'status' => 'required|in:' . implode(',', Event::getStatuses()),
        ], [
            'title.required' => 'The event title is required.',
            'event_type.required' => 'Please select an event type.',
            'start_date.required' => 'The start date is required.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'end_time.after' => 'The end time must be after the start time.',
            'status.required' => 'Please select a status.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $eventData = [
                'title' => $request->title,
                'description' => $request->description,
                'event_type' => $request->event_type,
                'category' => $request->category,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date ?: null,
                'start_time' => $request->start_time ? $request->start_date . ' ' . $request->start_time : null,
                'end_time' => $request->end_time ? ($request->end_date ?: $request->start_date) . ' ' . $request->end_time : null,
                'location' => $request->location,
                'semester_id' => $request->semester_id,
                'section_id' => $request->section_id,
                'status' => $request->status,
                'created_by' => Auth::id(),
            ];

            $newEvent = Event::create($eventData);

            // Send notifications to students
            $this->notifyStudents($newEvent, 'created');

            $colorMap = [
                'academic' => 'bg-primary-subtle',
                'social' => 'bg-success-subtle',
                'sports' => 'bg-info-subtle',
                'cultural' => 'bg-warning-subtle',
                'workshop' => 'bg-danger-subtle',
                'seminar' => 'bg-dark-subtle',
                'conference' => 'bg-primary-subtle',
                'ceremony' => 'bg-success-subtle',
                'meeting' => 'bg-info-subtle',
                'other' => 'bg-secondary-subtle',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully!',
                'event' => $this->formatEventForCalendar($newEvent, $colorMap),
                'allEvents' => $this->getAllEventsFormatted($colorMap)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);
        
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:' . implode(',', Event::getTypes()),
            'category' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i' . ($request->start_time ? '|after:start_time' : ''),
            'location' => 'nullable|string|max:255',
            'semester_id' => 'nullable|exists:semesters,id',
            'section_id' => 'nullable|exists:sections,id',
            'status' => 'required|in:' . implode(',', Event::getStatuses()),
        ], [
            'title.required' => 'The event title is required.',
            'event_type.required' => 'Please select an event type.',
            'start_date.required' => 'The start date is required.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'end_time.after' => 'The end time must be after the start time.',
            'status.required' => 'Please select a status.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $eventData = [
                'title' => $request->title,
                'description' => $request->description,
                'event_type' => $request->event_type,
                'category' => $request->category,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date ?: null,
                'start_time' => $request->start_time ? $request->start_date . ' ' . $request->start_time : null,
                'end_time' => $request->end_time ? ($request->end_date ?: $request->start_date) . ' ' . $request->end_time : null,
                'location' => $request->location,
                'semester_id' => $request->semester_id,
                'section_id' => $request->section_id,
                'status' => $request->status,
            ];

            $event->update($eventData);

            // Send notifications to students
            $this->notifyStudents($event, 'updated');

            $colorMap = [
                'academic' => 'bg-primary-subtle',
                'social' => 'bg-success-subtle',
                'sports' => 'bg-info-subtle',
                'cultural' => 'bg-warning-subtle',
                'workshop' => 'bg-danger-subtle',
                'seminar' => 'bg-dark-subtle',
                'conference' => 'bg-primary-subtle',
                'ceremony' => 'bg-success-subtle',
                'meeting' => 'bg-info-subtle',
                'other' => 'bg-secondary-subtle',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully!',
                'event' => $this->formatEventForCalendar($event->fresh(), $colorMap),
                'allEvents' => $this->getAllEventsFormatted($colorMap)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $event = Event::find($id);
        
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.'
            ], 404);
        }

        try {
            $event->delete();

            $colorMap = [
                'academic' => 'bg-primary-subtle',
                'social' => 'bg-success-subtle',
                'sports' => 'bg-info-subtle',
                'cultural' => 'bg-warning-subtle',
                'workshop' => 'bg-danger-subtle',
                'seminar' => 'bg-dark-subtle',
                'conference' => 'bg-primary-subtle',
                'ceremony' => 'bg-success-subtle',
                'meeting' => 'bg-info-subtle',
                'other' => 'bg-secondary-subtle',
            ];

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully!',
                'eventId' => $id,
                'allEvents' => $this->getAllEventsFormatted($colorMap)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ], 500);
        }
    }

    private function formatEventForCalendar($event, $colorMap)
    {
        // Determine if it's an all-day event (no time specified)
        $allDay = empty($event->start_time) && empty($event->end_time);
        
        // Parse dates as Carbon instances
        $startDate = Carbon::parse($event->start_date);
        
        // Build start datetime
        if ($allDay) {
            $start = $startDate->format('Y-m-d');
        } else {
            $startTime = $event->start_time ? Carbon::parse($event->start_time)->format('H:i:s') : '00:00:00';
            $start = $startDate->format('Y-m-d') . 'T' . $startTime;
        }
        
        // Build end datetime
        $end = null;
        if ($event->end_date) {
            $endDate = Carbon::parse($event->end_date);
            if ($allDay) {
                // For all-day events, end date should be the day after
                $end = $endDate->addDay()->format('Y-m-d');
            } else {
                $endTime = $event->end_time ? Carbon::parse($event->end_time)->format('H:i:s') : '23:59:59';
                $end = $endDate->format('Y-m-d') . 'T' . $endTime;
            }
        } elseif (!$allDay && $event->start_time) {
            // If no end date but has start time, set end time to 1 hour after start
            $endTime = Carbon::parse($event->start_time)->addHour()->format('H:i:s');
            $end = $startDate->format('Y-m-d') . 'T' . $endTime;
        }

        return [
            'id' => $event->id,
            'title' => $event->title,
            'start' => $start,
            'end' => $end,
            'allDay' => $allDay,
            'className' => $colorMap[$event->event_type] ?? 'bg-secondary-subtle',
            'extendedProps' => [
                'event_type' => $event->event_type,
                'description' => $event->description,
                'location' => $event->location,
                'category' => $event->category,
                'status' => $event->status,
                'created_at' => $event->created_at ? $event->created_at->toIso8601String() : null,
            ],
        ];
    }

    private function getAllEventsFormatted($colorMap)
    {
        return Event::all()->map(function($event) use ($colorMap) {
            return $this->formatEventForCalendar($event, $colorMap);
        })->toArray();
    }

    /**
     * Notify students about event creation or update
     *
     * @param Event $event
     * @param string $action ('created' or 'updated')
     * @return void
     */
    private function notifyStudents(Event $event, string $action = 'created')
    {
        try {
            // Build query to find students
            $query = StudentInfo::with('user');

            // Filter by semester if specified
            if ($event->semester_id) {
                $query->where('semester_id', $event->semester_id);
            }

            // Filter by section if specified
            if ($event->section_id) {
                $query->where('section_id', $event->section_id);
            }

            // Get students
            $students = $query->get();

            // Format event date and time for notification
            $startDate = Carbon::parse($event->start_date)->format('F j, Y');
            $eventTime = '';
            
            if ($event->start_time) {
                $startTime = Carbon::parse($event->start_time)->format('g:i A');
                if ($event->end_time) {
                    $endTime = Carbon::parse($event->end_time)->format('g:i A');
                    $eventTime = " from {$startTime} to {$endTime}";
                } else {
                    $eventTime = " at {$startTime}";
                }
            }

            // Determine notification type and title based on action
            $notificationType = $action === 'created' ? 'event_created' : 'event_updated';
            $actionText = $action === 'created' ? 'New Event' : 'Event Updated';
            
            // Create notification title
            $title = "{$actionText}: {$event->title}";
            
            // Create notification body
            $body = "A " . ucfirst($event->event_type) . " event has been {$action} for {$startDate}{$eventTime}";
            if ($event->location) {
                $body .= " at {$event->location}";
            }
            $body .= ".";

            // Create additional data
            $notificationData = [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'event_type' => $event->event_type,
                'start_date' => $event->start_date,
                'start_time' => $event->start_time,
                'location' => $event->location,
                'action' => $action,
            ];

            // Create notifications for each student
            foreach ($students as $student) {
                // Skip if student doesn't have a user account
                if (!$student->user) {
                    continue;
                }

                Notification::create([
                    'user_id' => $student->user_id,
                    'type' => $notificationType,
                    'title' => $title,
                    'body' => $body,
                    'data' => $notificationData,
                    'notifiable_id' => $event->id,
                    'notifiable_type' => Event::class,
                    'read_at' => null,
                ]);
            }

            // Log notification count for debugging
            Log::info("Sent {$action} notifications to " . $students->count() . " students for event: {$event->title}");

        } catch (\Exception $e) {
            // Log error but don't fail the event creation/update
            Log::error("Failed to send notifications for event {$event->id}: " . $e->getMessage());
        }
    }
}

