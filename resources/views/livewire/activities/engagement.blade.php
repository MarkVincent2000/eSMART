<div class="row" x-data @view-event.window="$wire.viewEvent($event.detail.eventId)">
    <x-toast-notification />
    <div class="col-12">
        <div class="row">
            <div class="col-xl-3">
                <div class="card card-h-100">
                    <div class="card-body">
                        <x-button variant="solid" color="primary" block icon="mdi mdi-plus"
                            wire:click="openCreateEventModal">
                            Create New Event
                        </x-button>

                        <div id="external-events">
                            <br>
                            <p class="text-muted">Drag and drop your event or click in the calendar</p>
                            @php
                                // Map event types to color classes
                                $colorMap = [
                                    'academic' => 'bg-primary-subtle text-primary',
                                    'social' => 'bg-success-subtle text-success',
                                    'sports' => 'bg-info-subtle text-info',
                                    'cultural' => 'bg-warning-subtle text-warning',
                                    'workshop' => 'bg-danger-subtle text-danger',
                                    'seminar' => 'bg-dark-subtle text-dark',
                                    'conference' => 'bg-primary-subtle text-primary',
                                    'ceremony' => 'bg-success-subtle text-success',
                                    'meeting' => 'bg-info-subtle text-info',
                                    'other' => 'bg-secondary-subtle text-secondary',
                                ];
                                $eventTypes = $this->eventTypeOptions;
                            @endphp
                            @foreach($eventTypes as $eventType)
                                @php
                                    $colorClass = $colorMap[$eventType['value']] ?? 'bg-secondary-subtle text-secondary';
                                    $bgClass = explode(' ', $colorClass)[0]; // Get just the bg class for data-class
                                @endphp
                                <div class="external-event fc-event {{ $colorClass }}" data-class="{{ $bgClass }}"
                                    data-event-type="{{ $eventType['value'] }}">
                                    <i class="mdi mdi-checkbox-blank-circle font-size-11 me-2"></i>{{ $eventType['label'] }}
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
                <div>
                    <h5 class="mb-1">Upcoming Events</h5>
                    <p class="text-muted">Don't miss scheduled events</p>
                    <div class="pe-2 me-n1 mb-3" data-simplebar style="height: 400px">
                        <div id="upcoming-event-list"></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body bg-info-subtle">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i data-feather="calendar" class="text-info icon-dual-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="fs-15">Welcome to your Calendar!</h6>
                                <p class="text-muted mb-0">Event that applications book will appear here. Click on an
                                    event to see the details and manage applicants event.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end card-->
            </div> <!-- end col-->

            <div class="col-xl-9" wire:ignore.self>
                <div class="card card-h-100">
                    <div class="card-body">
                        <div id="calendar" wire:ignore data-events="{{ json_encode($this->calendarEvents) }}"></div>
                    </div>
                </div>
            </div><!-- end col -->
        </div>
        <!--end row-->

        <div style='clear:both'></div>

        <!-- Create/Edit Event Modal -->
        <x-modal wire:model="showCreateEventModal" :title="$showEditEventModal ? 'Edit Event' : 'Create New Event'"
            size="lg" centered :showFooter="true" overflow="visible">
            <form wire:submit.prevent="{{ $showEditEventModal ? 'updateEvent' : 'createEvent' }}">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="title" class="form-label">Event Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title"
                            wire:model="title" placeholder="Enter event title">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                            wire:model="description" rows="3" placeholder="Enter event description"></textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <x-select wire:model="status" :options="$this->eventStatusOptions" placeholder="Select status"
                            id="status" class="form-select" />
                        @error('status')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="col-md-6 mb-3">
                        <label for="event_type" class="form-label">Event Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('event_type') is-invalid @enderror" id="event_type"
                            wire:model="event_type">
                            <option value="">Select event type</option>
                            @foreach($this->eventTypeOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                        @error('event_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" class="form-control @error('category') is-invalid @enderror" id="category"
                            wire:model="category" placeholder="Enter category">
                        @error('category')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                            id="start_date" wire:model="start_date">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date"
                            wire:model="end_date" min="{{ $start_date }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                            id="start_time" wire:model="start_time">
                        @error('start_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="end_time"
                            wire:model="end_time">
                        @error('end_time')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control @error('location') is-invalid @enderror" id="location"
                            wire:model="location" placeholder="Enter event location">
                        @error('location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="semester_id" class="form-label">Semester</label>
                        <select class="form-select @error('semester_id') is-invalid @enderror" id="semester_id"
                            wire:model="semester_id">
                            <option value="">Select semester</option>
                            @foreach($this->semesterOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                        @error('semester_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="section_id" class="form-label">Section</label>
                        <select class="form-select @error('section_id') is-invalid @enderror" id="section_id"
                            wire:model="section_id">
                            <option value="">Select section</option>
                            @foreach($this->sectionOptions as $option)
                                <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                        @error('section_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>




                </div>

                <x-slot name="footer">
                    <x-button type="button" variant="outline" color="secondary" wire:click="closeCreateEventModal">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="solid" color="primary"
                        wire:click="{{ $showEditEventModal ? 'updateEvent' : 'createEvent' }}"
                        wire:target="{{ $showEditEventModal ? 'updateEvent' : 'createEvent' }}">
                        {{ $showEditEventModal ? 'Update Event' : 'Create Event' }}
                    </x-button>
                </x-slot>
            </form>
        </x-modal>

        <!-- View Event Modal -->
        <x-modal wire:model="showViewEventModal" title="{{ $selectedEvent ? $selectedEvent->title : 'Event Details' }}"
            size="md" centered :showFooter="false">
            @if($selectedEvent)
                <div class="event-details">
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-calendar-line me-2 text-primary"></i>
                            <span class="fw-medium">Date:</span>
                        </div>
                        <div class="ms-4">
                            @php
                                $startDate = \Carbon\Carbon::parse($selectedEvent->start_date);
                                $endDate = $selectedEvent->end_date ? \Carbon\Carbon::parse($selectedEvent->end_date) : null;
                                $dateFormat = $endDate && $endDate->format('Y-m-d') !== $startDate->format('Y-m-d')
                                    ? $startDate->format('d F, Y') . ' to ' . $endDate->format('d F, Y')
                                    : $startDate->format('d F, Y');
                            @endphp
                            {{ $dateFormat }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-time-line me-2 text-primary"></i>
                            <span class="fw-medium">Time:</span>
                        </div>
                        <div class="ms-4">
                            @if($selectedEvent->start_time && $selectedEvent->end_time)
                                @php
                                    $startTime = \Carbon\Carbon::parse($selectedEvent->start_time)->format('g:i A');
                                    $endTime = \Carbon\Carbon::parse($selectedEvent->end_time)->format('g:i A');
                                @endphp
                                {{ $startTime }} to {{ $endTime }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </div>
                    </div>

                    @if($selectedEvent->location)
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ri-map-pin-line me-2 text-primary"></i>
                                <span class="fw-medium">Location:</span>
                            </div>
                            <div class="ms-4">
                                {{ $selectedEvent->location }}
                            </div>
                        </div>
                    @endif

                    @if($selectedEvent->description)
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="ri-message-3-line me-2 text-primary"></i>
                                <span class="fw-medium">Description:</span>
                            </div>
                            <div class="ms-4">
                                {{ $selectedEvent->description }}
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <x-button type="button" variant="outline" color="secondary" wire:click="editEvent">
                            Edit
                        </x-button>
                        <x-button type="button" variant="solid" color="danger" wire:click="deleteEvent"
                            wire:confirm="Are you sure you want to delete this event?">
                            <i class="ri-close-line me-1"></i> Delete
                        </x-button>
                    </div>
                </div>
            @endif
        </x-modal>
    </div>
</div> <!-- end row-->