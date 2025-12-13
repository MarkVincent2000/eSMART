<?php

namespace App\Livewire\Activities;

use App\Models\Event\Event;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Carbon\Carbon;

class Engagement extends Component
{
    // Modal state
    public $showCreateEventModal = false;
    public $showViewEventModal = false;
    public $showEditEventModal = false;

    // Selected event
    public $selectedEventId = null;
    public $selectedEvent = null;

    // Form fields
    public $title = '';
    public $description = '';
    public $event_type = 'other';
    public $category = '';
    public $start_date = '';
    public $end_date = '';
    public $start_time = '';
    public $end_time = '';
    public $location = '';
    public $semester_id = null;
    public $section_id = null;
    public $status = 'draft';

    public function mount()
    {
        $this->loadActiveSemester();
    }

    public function loadActiveSemester()
    {
        $activeSemester = Semester::where('is_active', true)->first();
        if ($activeSemester) {
            $this->semester_id = $activeSemester->id;
        }
    }

    public function openCreateEventModal()
    {
        $this->resetForm();
        // Preload active semester
        $this->loadActiveSemester();
        $this->showCreateEventModal = true;
    }

    public function closeCreateEventModal()
    {
        $this->showCreateEventModal = false;
        $this->showEditEventModal = false;
        $this->resetForm();
    }

    public function viewEvent($eventId)
    {
        $this->selectedEventId = $eventId;
        $this->selectedEvent = Event::find($eventId);
        
        if (!$this->selectedEvent) {
            $this->dispatch('show-toast', [
                'message' => 'Event not found.',
                'type' => 'error',
                'title' => 'Error'
            ]);
            return;
        }

        $this->showViewEventModal = true;
    }

    public function closeViewEventModal()
    {
        $this->showViewEventModal = false;
        $this->showEditEventModal = false;
        $this->selectedEventId = null;
        $this->selectedEvent = null;
        $this->resetForm();
    }

    public function editEvent()
    {
        if (!$this->selectedEvent) {
            return;
        }

        // Load event data into form
        $this->title = $this->selectedEvent->title;
        $this->description = $this->selectedEvent->description ?? '';
        $this->event_type = $this->selectedEvent->event_type;
        $this->category = $this->selectedEvent->category ?? '';
        $this->start_date = Carbon::parse($this->selectedEvent->start_date)->format('Y-m-d');
        $this->end_date = $this->selectedEvent->end_date ? Carbon::parse($this->selectedEvent->end_date)->format('Y-m-d') : '';
        $this->start_time = $this->selectedEvent->start_time ? Carbon::parse($this->selectedEvent->start_time)->format('H:i') : '';
        $this->end_time = $this->selectedEvent->end_time ? Carbon::parse($this->selectedEvent->end_time)->format('H:i') : '';
        $this->location = $this->selectedEvent->location ?? '';
        $this->semester_id = $this->selectedEvent->semester_id;
        $this->section_id = $this->selectedEvent->section_id;
        $this->status = $this->selectedEvent->status;

        $this->showViewEventModal = false;
        $this->showEditEventModal = true;
        $this->showCreateEventModal = true;
    }

    public function updateEvent()
    {
        if (!$this->selectedEventId) {
            return;
        }

        try {
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'event_type' => 'required|in:' . implode(',', Event::getTypes()),
                'category' => 'nullable|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i',
                'location' => 'nullable|string|max:255',
                'semester_id' => 'nullable|exists:semesters,id',
                'section_id' => 'nullable|exists:sections,id',
                'status' => 'required|in:' . implode(',', Event::getStatuses()),
            ];

            // Only validate end_time > start_time if both are provided
            if ($this->start_time && $this->end_time) {
                $rules['end_time'] .= '|after:start_time';
            }

            $this->validate($rules, [
                'title.required' => 'The event title is required.',
                'event_type.required' => 'Please select an event type.',
                'start_date.required' => 'The start date is required.',
                'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
                'end_time.after' => 'The end time must be after the start time.',
                'status.required' => 'Please select a status.',
            ]);

            $event = Event::find($this->selectedEventId);
            if (!$event) {
                $this->dispatch('show-toast', [
                    'message' => 'Event not found.',
                    'type' => 'error',
                    'title' => 'Error'
                ]);
                return;
            }

            $eventData = [
                'title' => $this->title,
                'description' => $this->description,
                'event_type' => $this->event_type,
                'category' => $this->category,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date ?: null,
                'start_time' => $this->start_time ? $this->start_date . ' ' . $this->start_time : null,
                'end_time' => $this->end_time ? ($this->end_date ?: $this->start_date) . ' ' . $this->end_time : null,
                'location' => $this->location,
                'semester_id' => $this->semester_id,
                'section_id' => $this->section_id,
                'status' => $this->status,
            ];

            $event->update($eventData);

            // Show success toast notification
            $this->dispatch('show-toast', [
                'message' => 'Event updated successfully!',
                'type' => 'success',
                'title' => 'Success'
            ]);

            $this->closeViewEventModal();
            $this->closeCreateEventModal();
            
            // Dispatch event to refresh calendar with both the updated event and all events
            $this->dispatch('event-updated', [
                'event' => $this->formatEventForCalendar($event->fresh()),
                'allEvents' => $this->calendarEvents
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors - show first error in toast
            $firstError = collect($e->errors())->first()[0] ?? 'Please check the form for errors.';
            
            $this->dispatch('show-toast', [
                'message' => $firstError,
                'type' => 'error',
                'title' => 'Validation Error'
            ]);
            
            // Re-throw to show field-level errors
            throw $e;
            
        } catch (\Exception $e) {
            // Other errors
            $this->dispatch('show-toast', [
                'message' => 'Failed to update event: ' . $e->getMessage(),
                'type' => 'error',
                'title' => 'Error'
            ]);
        }
    }

    public function deleteEvent()
    {
        if (!$this->selectedEventId) {
            return;
        }

        try {
            $event = Event::find($this->selectedEventId);
            if (!$event) {
                $this->dispatch('show-toast', [
                    'message' => 'Event not found.',
                    'type' => 'error',
                    'title' => 'Error'
                ]);
                return;
            }

            $event->delete();

            // Show success toast notification
            $this->dispatch('show-toast', [
                'message' => 'Event deleted successfully!',
                'type' => 'success',
                'title' => 'Success'
            ]);

            $this->closeViewEventModal();
            
            // Dispatch event to refresh calendar with all events
            $this->dispatch('event-deleted', [
                'eventId' => $this->selectedEventId,
                'allEvents' => $this->calendarEvents
            ]);
            
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'message' => 'Failed to delete event: ' . $e->getMessage(),
                'type' => 'error',
                'title' => 'Error'
            ]);
        }
    }

    public function resetForm()
    {
        $this->title = '';
        $this->description = '';
        $this->event_type = 'other';
        $this->category = '';
        $this->start_date = '';
        $this->end_date = '';
        $this->start_time = '';
        $this->end_time = '';
        $this->location = '';
        $this->semester_id = null;
        $this->section_id = null;
        $this->status = 'draft';
        $this->selectedEventId = null;
        $this->selectedEvent = null;
    }

    public function createEvent()
    {
        try {
            $rules = [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'event_type' => 'required|in:' . implode(',', Event::getTypes()),
                'category' => 'nullable|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i',
                'location' => 'nullable|string|max:255',
                'semester_id' => 'nullable|exists:semesters,id',
                'section_id' => 'nullable|exists:sections,id',
                'status' => 'required|in:' . implode(',', Event::getStatuses()),
            ];

            // Only validate end_time > start_time if both are provided
            if ($this->start_time && $this->end_time) {
                $rules['end_time'] .= '|after:start_time';
            }

            $this->validate($rules, [
                'title.required' => 'The event title is required.',
                'event_type.required' => 'Please select an event type.',
                'start_date.required' => 'The start date is required.',
                'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
                'end_time.after' => 'The end time must be after the start time.',
                'status.required' => 'Please select a status.',
            ]);

            $eventData = [
                'title' => $this->title,
                'description' => $this->description,
                'event_type' => $this->event_type,
                'category' => $this->category,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date ?: null,
                'start_time' => $this->start_time ? $this->start_date . ' ' . $this->start_time : null,
                'end_time' => $this->end_time ? ($this->end_date ?: $this->start_date) . ' ' . $this->end_time : null,
                'location' => $this->location,
                'semester_id' => $this->semester_id,
                'section_id' => $this->section_id,
                'status' => $this->status,
                'created_by' => Auth::id(),
            ];

            $newEvent = Event::create($eventData);

            // Show success toast notification
            $this->dispatch('show-toast', [
                'message' => 'Event created successfully!',
                'type' => 'success',
                'title' => 'Success'
            ]);

            $this->closeCreateEventModal();
            
            // Dispatch event to refresh calendar with the new event data and all events
            $this->dispatch('event-created', [
                'event' => $this->formatEventForCalendar($newEvent),
                'allEvents' => $this->calendarEvents
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors - show first error in toast
            $firstError = collect($e->errors())->first()[0] ?? 'Please check the form for errors.';
            
            $this->dispatch('show-toast', [
                'message' => $firstError,
                'type' => 'error',
                'title' => 'Validation Error'
            ]);
            
            // Re-throw to show field-level errors
            throw $e;
            
        } catch (\Exception $e) {
            // Other errors
            $this->dispatch('show-toast', [
                'message' => 'Failed to create event: ' . $e->getMessage(),
                'type' => 'error',
                'title' => 'Error'
            ]);
        }
    }

    #[Computed]
    public function semesterOptions()
    {
        return Semester::where('is_active', true)
            ->orderBy('school_year', 'desc')
            ->orderBy('name')
            ->get()
            ->map(function($semester) {
                return [
                    'value' => $semester->id,
                    'label' => $semester->name . ' (' . $semester->school_year . ')'
                ];
            })->toArray();
    }

    #[Computed]
    public function sectionOptions()
    {
        return Section::where('active', true)
            ->orderBy('name')
            ->get()
            ->map(function($section) {
                return [
                    'value' => $section->id,
                    'label' => $section->name
                ];
            })->toArray();
    }

    #[Computed]
    public function eventTypeOptions()
    {
        return collect(Event::getTypes())->map(function($type) {
            return [
                'value' => $type,
                'label' => ucfirst($type)
            ];
        })->toArray();
    }

    #[Computed]
    public function eventStatusOptions()
    {
        return collect(Event::getStatuses())->map(function($status) {
            return [
                'value' => $status,
                'label' => ucfirst($status)
            ];
        })->toArray();
    }

    private function formatEventForCalendar($event)
    {
        // Map event types to color classes
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

        return $this->formatSingleEvent($event, $colorMap);
    }

    private function formatSingleEvent($event, $colorMap)
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
            ],
        ];
    }

    #[Computed]
    public function calendarEvents()
    {
        // Map event types to color classes
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

        return Event::all()->map(function($event) use ($colorMap) {
            return $this->formatSingleEvent($event, $colorMap);
        })->toArray();
    }

    public function getEventsForCalendar()
    {
        return $this->calendarEvents;
    }

    public function render()
    {
        return view('livewire.activities.engagement');
    }
}
