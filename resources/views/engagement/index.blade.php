@extends('layouts.master')
@section('title')
    Engagement
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/nouislider/nouislider.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/gridjs/theme/mermaid.min.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('build/libs/flatpickr/flatpickr.min.css') }}">
    <link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')

    <x-breadcrumb title="Engagement" li_1="Engagement Management" />

    @include('engagement.engagement')

@endsection
@section('script')
    <script src="{{ URL::asset('build/libs/fullcalendar/index.global.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('build/js/pages/sweetalerts.init.js') }}"></script>
    <script src="{{ URL::asset('build/js/app.js') }}"></script>




    <script>
        // Engagement Calendar AJAX Implementation
        document.addEventListener('DOMContentLoaded', function () {
            // Check if user has manage-engagement permission
            var canManageEngagement = {{ auth()->user()->can('manage-engagement') ? 'true' : 'false' }};

            var calendarInitialized = false;
            var formData = null;
            var currentEventId = null;
            var isEditMode = false;
            var isViewMode = false;
            var tempDraggedEvent = null; // Track temporarily dragged event

            // Flatpickr elements
            var start_date = document.getElementById("event-start-date");
            var timepicker1 = document.getElementById("timepicker1");
            var timepicker2 = document.getElementById("timepicker2");
            var flatpickrDateInstance = null;
            var flatpickrTime1Instance = null;
            var flatpickrTime2Instance = null;
            var flatpickrDateInstance = null;
            var flatpickrTime1Instance = null;
            var flatpickrTime2Instance = null;

            // API Routes - Using engagement route group from routes/engagement/web.php
            const API = {
                calendarEvents: '{{ route("engagement.calendar-events") }}',
                formData: '{{ route("engagement.form-data") }}',
                getEvent: (id) => {
                    return '{{ url("/engagement/event") }}/' + encodeURIComponent(id);
                },
                store: '{{ route("engagement.store") }}',
                update: (id) => {
                    return '{{ url("/engagement/event") }}/' + encodeURIComponent(id);
                },
                destroy: (id) => {
                    return '{{ url("/engagement/event") }}/' + encodeURIComponent(id);
                },
            };

            // Toast notification helper
            function showToast(message, type = 'success', title = '') {
                if (typeof window.showToast === 'function') {
                    window.showToast(message, type, title);
                } else if (typeof window.dispatchEvent !== 'undefined') {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { message, type, title }
                    }));
                }
            }

            // Get initial view based on screen size
            function getInitialView() {
                if (window.innerWidth >= 768 && window.innerWidth < 1200) {
                    return 'timeGridWeek';
                } else if (window.innerWidth <= 768) {
                    return 'listMonth';
                } else {
                    return 'dayGridMonth';
                }
            }

            // Load form data (semesters, sections, event types, etc.)
            function loadFormData() {
                fetch(API.formData)
                    .then(response => response.json())
                    .then(data => {
                        formData = data;
                        populateFormDropdowns();
                        populateExternalEvents();
                    })
                    .catch(error => {
                        showToast('Failed to load form data', 'error', 'Error');
                    });
            }

            // Populate form dropdowns
            function populateFormDropdowns() {
                if (!formData) return;

                // Populate event type
                const eventTypeSelect = document.getElementById('event-type');
                if (eventTypeSelect) {
                    eventTypeSelect.innerHTML = '<option value="">Select event type</option>';
                    formData.eventTypeOptions.forEach(option => {
                        const optionEl = document.createElement('option');
                        optionEl.value = option.value;
                        optionEl.textContent = option.label;
                        eventTypeSelect.appendChild(optionEl);
                    });
                }

                // Populate status
                const statusSelect = document.getElementById('event-status');
                if (statusSelect) {
                    statusSelect.innerHTML = '<option value="">Select status</option>';
                    formData.eventStatusOptions.forEach(option => {
                        const optionEl = document.createElement('option');
                        optionEl.value = option.value;
                        optionEl.textContent = option.label;
                        statusSelect.appendChild(optionEl);
                    });
                }

                // Populate semester
                const semesterSelect = document.getElementById('event-semester');
                if (semesterSelect) {
                    semesterSelect.innerHTML = '<option value="">Select semester</option>';
                    formData.semesterOptions.forEach(option => {
                        const optionEl = document.createElement('option');
                        optionEl.value = option.value;
                        optionEl.textContent = option.label;
                        semesterSelect.appendChild(optionEl);
                    });
                    if (formData.activeSemesterId) {
                        semesterSelect.value = formData.activeSemesterId;
                    }
                }

                // Populate section
                const sectionSelect = document.getElementById('event-section');
                if (sectionSelect) {
                    sectionSelect.innerHTML = '<option value="">Select section</option>';
                    formData.sectionOptions.forEach(option => {
                        const optionEl = document.createElement('option');
                        optionEl.value = option.value;
                        optionEl.textContent = option.label;
                        sectionSelect.appendChild(optionEl);
                    });
                }
            }

            // Populate external events
            function populateExternalEvents() {
                if (!formData) return;

                const colorMap = {
                    'academic': 'bg-primary-subtle text-primary',
                    'social': 'bg-success-subtle text-success',
                    'sports': 'bg-info-subtle text-info',
                    'cultural': 'bg-warning-subtle text-warning',
                    'workshop': 'bg-danger-subtle text-danger',
                    'seminar': 'bg-dark-subtle text-dark',
                    'conference': 'bg-primary-subtle text-primary',
                    'ceremony': 'bg-success-subtle text-success',
                    'meeting': 'bg-info-subtle text-info',
                    'other': 'bg-secondary-subtle text-secondary',
                };

                const externalEventsList = document.getElementById('external-events-list');
                if (externalEventsList) {
                    externalEventsList.innerHTML = '';
                    formData.eventTypeOptions.forEach(eventType => {
                        const colorClass = colorMap[eventType.value] || 'bg-secondary-subtle text-secondary';
                        const bgClass = colorClass.split(' ')[0];
                        const div = document.createElement('div');
                        div.className = `external-event fc-event ${colorClass}`;
                        div.setAttribute('data-class', bgClass);
                        div.setAttribute('data-event-type', eventType.value);
                        div.innerHTML = `<i class="mdi mdi-checkbox-blank-circle font-size-11 me-2"></i>${eventType.label}`;
                        externalEventsList.appendChild(div);
                    });
                    // Re-initialize draggable after populating
                    setTimeout(initDraggable, 100);
                }
            }

            // Initialize draggable external events
            function initDraggable() {
                var externalEventContainerEl = document.getElementById('external-events');
                if (!externalEventContainerEl) {
                    return;
                }

                // Destroy existing draggable if it exists
                if (window.externalEventsDraggable) {
                    try {
                        window.externalEventsDraggable.destroy();
                    } catch (e) {
                    }
                    window.externalEventsDraggable = null;
                }

                // Initialize draggable for external events
                var Draggable = FullCalendar.Draggable;
                window.externalEventsDraggable = new Draggable(externalEventContainerEl, {
                    itemSelector: '.external-event',
                    eventData: function (eventEl) {
                        var eventType = eventEl.getAttribute('data-event-type') || 'other';
                        var className = eventEl.getAttribute('data-class') || 'bg-secondary-subtle';
                        return {
                            id: Math.floor(Math.random() * 11000),
                            title: eventEl.innerText.trim(),
                            allDay: true,
                            start: new Date(),
                            className: className,
                            extendedProps: {
                                event_type: eventType
                            }
                        };
                    }
                });
            }

            // Load calendar events
            function loadCalendarEvents() {
                fetch(API.calendarEvents)
                    .then(response => response.json())
                    .then(events => {
                        const calendarEl = document.getElementById('calendar');
                        if (calendarEl) {
                            if (window.engagementCalendarInstance) {
                                window.engagementCalendarInstance.removeAllEvents();
                                window.engagementCalendarInstance.addEventSource(events);
                            }
                        }
                        // Update upcoming events list
                        loadUpcomingEvents();
                    })
                    .catch(error => {
                    });
            }

            // Variables for infinite scroll
            let allUpcomingEvents = [];
            let currentEventIndex = 0;
            const eventsPerPage = 5;
            let isLoadingMore = false;

            // Color mapping for event types (shared)
            const colorMap = {
                'academic': 'primary',
                'social': 'success',
                'sports': 'info',
                'cultural': 'warning',
                'workshop': 'danger',
                'seminar': 'dark',
                'conference': 'primary',
                'ceremony': 'success',
                'meeting': 'info',
                'other': 'secondary',
            };

            // Load and display upcoming events
            function loadUpcomingEvents() {
                fetch(API.calendarEvents)
                    .then(response => response.json())
                    .then(events => {
                        const upcomingEventsList = document.getElementById('upcoming-event-list');
                        if (!upcomingEventsList) {
                            return;
                        }

                        // Get today's date at midnight
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);

                        // Filter upcoming events and sort by creation date (newest first)
                        allUpcomingEvents = events
                            .filter(event => {
                                const eventStart = new Date(event.start);
                                return eventStart >= today;
                            })
                            .sort((a, b) => {
                                // Sort by creation date (newest first)
                                const dateA = new Date(a.extendedProps?.created_at || a.start);
                                const dateB = new Date(b.extendedProps?.created_at || b.start);
                                return dateB - dateA; // Descending order (newest created first)
                            });

                        // Reset index and clear list
                        currentEventIndex = 0;
                        upcomingEventsList.innerHTML = '';

                        if (allUpcomingEvents.length === 0) {
                            upcomingEventsList.innerHTML = `
                                                                                         <div class="text-center text-muted py-4">
                                                                                            <i class="ri-calendar-line fs-1 mb-2"></i>
                                                                                              <p>No upcoming events</p>
                                                                                               </div>
                                                                                            `;
                            return;
                        }

                        // Load initial batch of events
                        loadMoreEvents();

                        // Setup infinite scroll (with delay to ensure SimpleBar is initialized)
                        setTimeout(() => {
                            setupInfiniteScroll();
                        }, 500);
                    })
                    .catch(error => {
                    });
            }

            // Load more events (batch of eventsPerPage)
            function loadMoreEvents() {
                if (isLoadingMore) {
                    return;
                }

                const upcomingEventsList = document.getElementById('upcoming-event-list');
                if (!upcomingEventsList) {
                    return;
                }

                // Check if there are more events to load
                if (currentEventIndex >= allUpcomingEvents.length) {
                    // Remove loading indicator if it exists
                    const loadingIndicator = document.getElementById('events-loading');
                    if (loadingIndicator) {
                        loadingIndicator.remove();
                    }

                    // Show "end of list" message if not already shown
                    if (!document.getElementById('events-end-message') && allUpcomingEvents.length > eventsPerPage) {
                        const endMessage = document.createElement('div');
                        endMessage.id = 'events-end-message';
                        endMessage.className = 'text-center text-muted py-3';
                        endMessage.innerHTML = '<small>No more events to display</small>';
                        upcomingEventsList.appendChild(endMessage);
                    }
                    return;
                }

                isLoadingMore = true;

                // Get next batch of events
                const endIndex = Math.min(currentEventIndex + eventsPerPage, allUpcomingEvents.length);
                const eventsToLoad = allUpcomingEvents.slice(currentEventIndex, endIndex);

                // Remove loading indicator if it exists
                const loadingIndicator = document.getElementById('events-loading');
                if (loadingIndicator) {
                    loadingIndicator.remove();
                }

                // Create and append event cards
                eventsToLoad.forEach(event => {
                    const eventDate = new Date(event.start);
                    const eventType = event.extendedProps?.event_type || 'other';
                    const color = colorMap[eventType] || 'secondary';

                    // Format date
                    const dateOptions = { day: 'numeric', month: 'short', year: 'numeric' };
                    const formattedDate = eventDate.toLocaleDateString('en-GB', dateOptions);

                    // Format end date if available
                    let endDateDisplay = '';
                    if (event.end && !event.allDay) {
                        const endDate = new Date(event.end);
                        const endDateStr = endDate.toLocaleDateString('en-GB', dateOptions);
                        if (formattedDate !== endDateStr) {
                            endDateDisplay = ' to ' + endDateStr;
                        }
                    }

                    // Format time if available
                    let timeDisplay = 'Full day event';
                    if (!event.allDay) {
                        const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
                        const startTime = eventDate.toLocaleTimeString('en-US', timeOptions);

                        if (event.end) {
                            const endDate = new Date(event.end);
                            const endTime = endDate.toLocaleTimeString('en-US', timeOptions);
                            if (startTime !== endTime) {
                                timeDisplay = startTime + ' to ' + endTime;
                            } else {
                                timeDisplay = startTime;
                            }
                        } else {
                            timeDisplay = startTime;
                        }
                    }

                    // Get description
                    const description = event.extendedProps?.description || '';

                    const eventItem = document.createElement('div');
                    eventItem.className = 'card mb-3';
                    eventItem.style.cursor = 'pointer';
                    eventItem.innerHTML = `
                                                                                                                  <div class="card-body">
                                                                                                                      <div class="d-flex mb-3">
                                                                                                                          <div class="flex-grow-1">
                                                                                                                        <i class="mdi mdi-checkbox-blank-circle me-2 text-${color}"></i>
                                                                                                                      <span class="fw-medium">${formattedDate}${endDateDisplay}</span>
                                                                                                                    </div>
                                                                                                                      <div class="flex-shrink-0">
                                                                                                                     </div>
                                                                                                                       </div>
                                                                                                                       <h6 class="card-title fs-16">${event.title}</h6>
                                                                                                                      ${event.extendedProps?.location ? `
                                                                                                                          <p class="text-muted mb-2">
                                                                                                                         <i class="ri-map-pin-line"></i> ${event.extendedProps.location}
                                                                                                                          </p>
                                                                                                                            ` : ''}
                                                                                                                          ${description ? `
                                                                                                                             <p class="text-muted text-truncate-two-lines mb-0">${description}</p>
                                                                                                                              ` : ''}
                                                                                                                            </div>
                                                                                                                            `;

                    // Add click event to view details
                    eventItem.addEventListener('click', () => {
                        if (event.id) {
                            viewEvent(event.id);
                        }
                    });

                    upcomingEventsList.appendChild(eventItem);
                });

                // Update index
                currentEventIndex = endIndex;

                // Add loading indicator if there are more events
                if (currentEventIndex < allUpcomingEvents.length) {
                    const loader = document.createElement('div');
                    loader.id = 'events-loading';
                    loader.className = 'text-center py-3';
                    loader.innerHTML = `
                                                                                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                                                                           <span class="visually-hidden">Loading...</span>
                                                                                                             </div>
                                                                                                  <p class="text-muted mt-2 mb-0"><small>Scroll for more events (${allUpcomingEvents.length - currentEventIndex} remaining)</small></p>
                                                                                                              `;
                    upcomingEventsList.appendChild(loader);
                }

                isLoadingMore = false;

                // Check if we need to load more immediately (if content doesn't fill container)
                setTimeout(() => {
                    checkAndLoadMore();
                }, 200);
            }

            // Check if content fills container, if not load more
            function checkAndLoadMore() {
                const upcomingEventsList = document.getElementById('upcoming-event-list');
                if (!upcomingEventsList) return;

                const simplebarContainer = document.querySelector('[data-simplebar]');
                if (!simplebarContainer) return;

                let scrollElement = simplebarContainer.querySelector('.simplebar-content-wrapper') || simplebarContainer;

                const hasScroll = scrollElement.scrollHeight > scrollElement.clientHeight;
                const hasMoreEvents = currentEventIndex < allUpcomingEvents.length;

                // If no scrollbar and more events exist, load more
                if (!hasScroll && hasMoreEvents && !isLoadingMore) {
                    loadMoreEvents();
                }
            }

            // Setup infinite scroll listener
            function setupInfiniteScroll(retryCount = 0) {
                // Find the simplebar scrollable element
                const simplebarContainer = document.querySelector('[data-simplebar]');

                if (!simplebarContainer) {
                    // Retry up to 3 times with increasing delays
                    if (retryCount < 3) {
                        const delay = (retryCount + 1) * 300;
                        setTimeout(() => setupInfiniteScroll(retryCount + 1), delay);
                    }
                    return;
                }

                // Try multiple methods to find the scrollable element
                let scrollElement = null;

                // Method 1: Look for SimpleBar's content wrapper
                scrollElement = simplebarContainer.querySelector('.simplebar-content-wrapper');

                // Method 2: Look for SimpleBar's scroll content
                if (!scrollElement) {
                    scrollElement = simplebarContainer.querySelector('.simplebar-scroll-content');
                }

                // Method 3: Check if SimpleBar instance exists
                if (!scrollElement && simplebarContainer.SimpleBar) {
                    scrollElement = simplebarContainer.SimpleBar.getScrollElement();
                }

                // Method 4: Fallback to container
                if (!scrollElement) {
                    scrollElement = simplebarContainer;
                }

                // Check if element is actually scrollable
                const hasOverflow = scrollElement.scrollHeight > scrollElement.clientHeight;

                if (!hasOverflow && retryCount < 3) {
                    setTimeout(() => setupInfiniteScroll(retryCount + 1), 300);
                    return;
                }

                // Remove existing listener if any
                if (scrollElement._scrollHandler) {
                    scrollElement.removeEventListener('scroll', scrollElement._scrollHandler);
                }

                // Create and store scroll handler
                scrollElement._scrollHandler = handleScroll;
                scrollElement.addEventListener('scroll', scrollElement._scrollHandler);

                // Test if scroll events fire by manually triggering
                setTimeout(() => {
                    scrollElement.scrollTop = scrollElement.scrollTop + 1;
                    scrollElement.scrollTop = scrollElement.scrollTop - 1;
                }, 500);
            }

            // Handle scroll event
            function handleScroll(e) {
                if (isLoadingMore) {
                    return;
                }

                const scrollElement = e.target;
                const scrollTop = scrollElement.scrollTop;
                const scrollHeight = scrollElement.scrollHeight;
                const clientHeight = scrollElement.clientHeight;

                // Use adaptive threshold (30% of client height or minimum 30px for better triggering)
                const threshold = Math.max(30, clientHeight * 0.3);

                // Check if scrolled near bottom
                if (scrollTop + clientHeight >= scrollHeight - threshold) {
                    loadMoreEvents();
                }
            }

            // Helper function to get icon for event type
            function getEventIcon(eventType) {
                const iconMap = {
                    'academic': 'book-open-line',
                    'social': 'group-line',
                    'sports': 'football-line',
                    'cultural': 'palette-line',
                    'workshop': 'tools-line',
                    'seminar': 'presentation-line',
                    'conference': 'user-voice-line',
                    'ceremony': 'award-line',
                    'meeting': 'discuss-line',
                    'other': 'calendar-event-line',
                };
                return iconMap[eventType] || 'calendar-event-line';
            }

            // Initialize calendar
            function initCalendar() {
                var calendarEl = document.getElementById('calendar');
                if (!calendarEl) {
                    return;
                }

                if (window.engagementCalendarInstance && calendarInitialized) {
                    if (window.engagementCalendarInstance.el === calendarEl &&
                        document.body.contains(calendarEl)) {
                        return;
                    } else {
                        try {
                            window.engagementCalendarInstance.destroy();
                        } catch (e) {
                        }
                        window.engagementCalendarInstance = null;
                        calendarInitialized = false;
                    }
                }

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    timeZone: 'local',
                    editable: true,
                    droppable: true,
                    selectable: true,
                    navLinks: true,
                    initialView: getInitialView(),
                    themeSystem: 'bootstrap',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                    },
                    // Prevent dragging events to past dates
                    eventAllow: function (dropInfo, draggedEvent) {
                        // Get today's date at midnight
                        var today = new Date();
                        today.setHours(0, 0, 0, 0);

                        // Get the drop date at midnight
                        var dropDate = new Date(dropInfo.start);
                        dropDate.setHours(0, 0, 0, 0);

                        // Allow drop only if date is today or in the future
                        if (dropDate >= today) {
                            return true;
                        }

                        // Show message that past dates are not allowed
                        showToast('Cannot move event to past dates', 'warning', 'Not Allowed');
                        return false;
                    },
                    // Prevent selecting past dates
                    selectAllow: function (selectInfo) {
                        var today = new Date();
                        today.setHours(0, 0, 0, 0);

                        var selectDate = new Date(selectInfo.start);
                        selectDate.setHours(0, 0, 0, 0);

                        return selectDate >= today;
                    },
                    windowResize: function (view) {
                        var newView = getInitialView();
                        calendar.changeView(newView);
                    },
                    eventReceive: function (info) {
                        // Only allow creating events if user has permission
                        if (!canManageEngagement) {
                            // Remove the event if user doesn't have permission
                            info.event.remove();
                            return;
                        }

                        // Check if dropped on a past date
                        var today = new Date();
                        today.setHours(0, 0, 0, 0);

                        var dropDate = new Date(info.event.start);
                        dropDate.setHours(0, 0, 0, 0);

                        if (dropDate < today) {
                            info.event.remove();
                            showToast('Cannot create event on past dates', 'warning', 'Not Allowed');
                            return;
                        }

                        // Store the temporary dragged event
                        tempDraggedEvent = info.event;

                        // Open create event modal
                        openCreateEventModal();

                        // Pre-fill event type from dragged element
                        if (info.event.extendedProps.event_type) {
                            document.getElementById('event-type').value = info.event.extendedProps.event_type;
                        }

                        // Pre-fill the date from where it was dropped
                        if (info.event.start) {
                            const droppedDate = info.event.start.toISOString().split('T')[0];
                            if (flatpickrDateInstance) {
                                flatpickrDateInstance.setDate(droppedDate, false);
                            }
                        }
                    },
                    dateClick: function (info) {
                        // Only allow creating events if user has permission
                        if (!canManageEngagement) {
                            return;
                        }

                        // Check if clicked date is in the past
                        var today = new Date();
                        today.setHours(0, 0, 0, 0);

                        var clickedDateObj = new Date(info.date);
                        clickedDateObj.setHours(0, 0, 0, 0);

                        if (clickedDateObj < today) {
                            showToast('Cannot create event on past dates', 'warning', 'Not Allowed');
                            return;
                        }

                        // Open create event modal with clicked date pre-filled
                        // Extract date string safely - ensure it's a plain string, not an object
                        var clickedDate = null;
                        if (info.dateStr && typeof info.dateStr === 'string') {
                            clickedDate = info.dateStr.split('T')[0]; // Get just YYYY-MM-DD part
                        } else if (info.date && info.date instanceof Date) {
                            // Fallback: format Date object to YYYY-MM-DD
                            var year = info.date.getFullYear();
                            var month = String(info.date.getMonth() + 1).padStart(2, '0');
                            var day = String(info.date.getDate()).padStart(2, '0');
                            clickedDate = year + '-' + month + '-' + day;
                        }
                        if (clickedDate) {
                            openCreateEventModalWithDate(clickedDate);
                        }
                    },
                    eventClick: function (info) {
                        var eventId = info.event.id;
                        if (eventId) {
                            viewEvent(eventId);
                        }
                        info.jsEvent.preventDefault();
                    },
                    eventDrop: function (info) {
                        // Check permission
                        if (!canManageEngagement) {
                            info.revert();
                            return;
                        }

                        var eventId = info.event.id;
                        if (!eventId) {
                            info.revert();
                            return;
                        }

                        // Get the NEW date (AFTER the drag)
                        var newStartDate = info.event.start;
                        var newEndDate = info.event.end;

                        // Check if dropped on a past date
                        var today = new Date();
                        today.setHours(0, 0, 0, 0);

                        var dropDate = new Date(newStartDate);
                        dropDate.setHours(0, 0, 0, 0);

                        if (dropDate < today) {
                            showToast('Cannot move event to past dates', 'warning', 'Not Allowed');
                            info.revert();
                            return;
                        }

                        // Store ORIGINAL event data (BEFORE the drag)
                        var originalStartDate = info.oldEvent.start;
                        var originalEndDate = info.oldEvent.end;
                        var originalAllDay = info.oldEvent.allDay;

                        // Format NEW dates for the form
                        var newStartDateStr = null;
                        var newEndDateStr = null;

                        if (newStartDate) {
                            var year = newStartDate.getFullYear();
                            var month = String(newStartDate.getMonth() + 1).padStart(2, '0');
                            var day = String(newStartDate.getDate()).padStart(2, '0');
                            newStartDateStr = year + '-' + month + '-' + day;
                        }

                        if (newEndDate) {
                            var year = newEndDate.getFullYear();
                            var month = String(newEndDate.getMonth() + 1).padStart(2, '0');
                            var day = String(newEndDate.getDate()).padStart(2, '0');
                            newEndDateStr = year + '-' + month + '-' + day;
                        }

                        // Store EVERYTHING we need for revert
                        window.draggedEventNewDates = {
                            // New dates for the form
                            startDate: newStartDateStr,
                            endDate: newEndDateStr,
                            // Original dates for reverting
                            originalStart: originalStartDate,
                            originalEnd: originalEndDate,
                            originalAllDay: originalAllDay,
                            // Event ID to find it in calendar
                            eventId: eventId,
                            eventTitle: info.event.title
                        };

                        // Open edit modal with the event
                        viewEventForDrag(eventId);
                    },
                    events: function (fetchInfo, successCallback, failureCallback) {
                        fetch(API.calendarEvents)
                            .then(response => response.json())
                            .then(events => {
                                // Mark past events as non-editable
                                var today = new Date();
                                today.setHours(0, 0, 0, 0);

                                events.forEach(event => {
                                    var eventDate = new Date(event.start);
                                    eventDate.setHours(0, 0, 0, 0);

                                    // Mark past events as non-editable
                                    if (eventDate < today) {
                                        event.editable = false;
                                        event.classNames = event.classNames || [];
                                        event.classNames.push('past-event');
                                    }
                                });

                                successCallback(events);
                            })
                            .catch(error => {
                                failureCallback(error);
                            });
                    },
                    // Style past events to show they're read-only
                    eventDidMount: function (info) {
                        var today = new Date();
                        today.setHours(0, 0, 0, 0);

                        var eventDate = new Date(info.event.start);
                        eventDate.setHours(0, 0, 0, 0);

                        if (eventDate < today) {
                            // Add styling to past events
                            info.el.style.opacity = '0.6';
                            info.el.style.cursor = 'default';
                            info.el.title = 'Past event (read-only)';
                        }
                    }
                });

                calendar.render();
                window.engagementCalendarInstance = calendar;
                calendarInitialized = true;
                initDraggable();
            }

            // Switch to form mode
            function switchToFormMode() {
                const eventDetails = document.querySelector('.event-details');
                const eventForm = document.querySelector('.event-form');
                const editBtn = document.getElementById('edit-event-btn');
                const deleteBtn = document.getElementById('btn-delete-event');
                const saveBtn = document.getElementById('btn-save-event');

                // Hide event details, show form
                if (eventDetails) {
                    eventDetails.classList.add('d-none');
                    eventDetails.style.display = 'none';
                }

                if (eventForm) {
                    eventForm.classList.remove('d-none');
                    // Use flex for Bootstrap row class
                    eventForm.style.display = 'flex';
                }

                // Hide edit button in form mode
                if (editBtn) {
                    editBtn.classList.add('d-none');
                    editBtn.style.display = 'none';
                }

                // Only show delete button in edit mode, not in create mode (and only if has permission)
                if (deleteBtn) {
                    if (isEditMode && canManageEngagement) {
                        deleteBtn.classList.remove('d-none');
                        deleteBtn.style.display = 'inline-block';
                    } else {
                        deleteBtn.classList.add('d-none');
                        deleteBtn.style.display = 'none';
                    }
                }

                // Always show save button in form mode (only if has permission)
                if (saveBtn && canManageEngagement) {
                    saveBtn.style.display = 'inline-block';
                    saveBtn.classList.remove('d-none');
                    saveBtn.textContent = isEditMode ? 'Update Event' : 'Add Event';
                } else if (saveBtn) {
                    saveBtn.style.display = 'none';
                    saveBtn.classList.add('d-none');
                }

                // Always show time pickers in form mode
                const eventTimeEl = document.getElementById('event-time');
                if (eventTimeEl) {
                    eventTimeEl.style.display = 'block';
                }
            }

            // Switch to view mode
            function switchToViewMode() {
                const eventDetails = document.querySelector('.event-details');
                const eventForm = document.querySelector('.event-form');
                const editBtn = document.getElementById('edit-event-btn');
                const deleteBtn = document.getElementById('btn-delete-event');
                const saveBtn = document.getElementById('btn-save-event');

                if (eventDetails) {
                    eventDetails.classList.remove('d-none');
                    // Explicitly set display to block to ensure visibility
                    eventDetails.style.display = 'block';
                }

                if (eventForm) {
                    eventForm.classList.add('d-none');
                    // Explicitly hide the form
                    eventForm.style.display = 'none';
                }

                // Show edit button in view mode (only if user has permission AND event is not in the past)
                var isPastEvent = window.currentEventIsPast || false;
                if (editBtn && canManageEngagement && !isPastEvent) {
                    editBtn.classList.remove('d-none');
                    editBtn.style.display = 'inline-block';
                } else if (editBtn) {
                    editBtn.classList.add('d-none');
                    editBtn.style.display = 'none';
                }

                // Show delete button in view mode (only if user has permission AND event is not in the past)
                if (deleteBtn && canManageEngagement && !isPastEvent) {
                    deleteBtn.classList.remove('d-none');
                    deleteBtn.style.display = 'inline-block';
                } else if (deleteBtn) {
                    deleteBtn.classList.add('d-none');
                    deleteBtn.style.display = 'none';
                }

                // Hide save button in view mode
                if (saveBtn) {
                    saveBtn.classList.add('d-none');
                    saveBtn.style.display = 'none';
                }
            }

            // Initialize Flatpickr
            function flatPickrInit() {
                // Initialize date range picker
                if (start_date) {
                    if (!flatpickrDateInstance) {
                        flatpickrDateInstance = flatpickr(start_date, {
                            enableTime: false,
                            mode: "range",
                            minDate: "today",
                            dateFormat: "Y-m-d",
                            altInput: true,
                            altFormat: "j F Y",
                            onChange: function (selectedDates, dateStr, instance) {
                                // Always keep time pickers visible
                                var eventTimeEl = document.getElementById('event-time');
                                if (eventTimeEl) {
                                    eventTimeEl.style.display = 'block';
                                }
                            },
                        });
                    }
                }

                // Initialize time pickers (12-hour format with AM/PM)
                if (timepicker1 && !flatpickrTime1Instance) {
                    flatpickrTime1Instance = flatpickr(timepicker1, {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "h:i K",
                        time_24hr: false
                    });
                }

                if (timepicker2 && !flatpickrTime2Instance) {
                    flatpickrTime2Instance = flatpickr(timepicker2, {
                        enableTime: true,
                        noCalendar: true,
                        dateFormat: "h:i K",
                        time_24hr: false
                    });
                }
            }

            // Clear Flatpickr values
            function flatpickrValueClear() {
                if (flatpickrDateInstance) {
                    flatpickrDateInstance.clear();
                }
                if (flatpickrTime1Instance) {
                    flatpickrTime1Instance.clear();
                }
                if (flatpickrTime2Instance) {
                    flatpickrTime2Instance.clear();
                }
            }

            // Open create event modal
            function openCreateEventModal(selectedDate = null) {
                // Check permission before opening modal
                if (!canManageEngagement) {
                    return;
                }

                // Note: Don't clear tempDraggedEvent here as this is called by eventReceive
                // The tempDraggedEvent will be cleared only when saved or modal is closed

                isEditMode = false;
                isViewMode = false;
                currentEventId = null;

                document.getElementById('form-event').reset();
                document.getElementById('eventid').value = '';
                document.getElementById('modal-title').textContent = 'Create New Event';
                clearFormErrors();
                flatpickrValueClear();

                // Set active semester if available
                if (formData && formData.activeSemesterId) {
                    document.getElementById('event-semester').value = formData.activeSemesterId;
                }

                // Switch to form mode first (this handles showing/hiding sections)
                switchToFormMode();

                // Ensure event details are hidden and form is shown
                const eventDetails = document.querySelector('.event-details');
                const eventForm = document.querySelector('.event-form');
                if (eventDetails) {
                    eventDetails.classList.add('d-none');
                    eventDetails.style.display = 'none';
                }
                if (eventForm) {
                    eventForm.classList.remove('d-none');
                    eventForm.style.display = 'flex';
                }

                // Ensure time container is visible
                const eventTimeContainer = document.getElementById('event-time');
                if (eventTimeContainer) {
                    eventTimeContainer.style.display = 'block';
                    eventTimeContainer.classList.remove('d-none');
                }

                // Explicitly set button states for create mode (respect permission)
                const deleteBtn = document.getElementById('btn-delete-event');
                const saveBtn = document.getElementById('btn-save-event');
                if (deleteBtn) {
                    deleteBtn.classList.add('d-none');
                    deleteBtn.style.display = 'none';
                }
                if (saveBtn && canManageEngagement) {
                    saveBtn.classList.remove('d-none');
                    saveBtn.style.display = 'inline-block';
                    saveBtn.textContent = 'Add Event';
                } else if (saveBtn) {
                    saveBtn.classList.add('d-none');
                    saveBtn.style.display = 'none';
                }

                // Show modal using Bootstrap
                const modalEl = document.getElementById('event-modal');
                if (modalEl) {
                    // Get existing modal instance or create new one
                    let modal = bootstrap.Modal.getInstance(modalEl);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalEl);
                    }

                    // Ensure aria-hidden is removed before showing
                    modalEl.removeAttribute('aria-hidden');
                    modalEl.setAttribute('aria-modal', 'true');

                    modal.show();

                    // Initialize Flatpickr after modal is shown
                    setTimeout(function () {
                        flatPickrInit();
                        // Set the selected date if provided
                        if (selectedDate) {
                            // Wait a bit more to ensure Flatpickr is fully initialized
                            setTimeout(function () {
                                if (flatpickrDateInstance && selectedDate && typeof selectedDate === 'string') {
                                    // Ensure date is a plain string in YYYY-MM-DD format
                                    var dateToSet = selectedDate.split('T')[0];
                                    // Verify it's a valid date string format (YYYY-MM-DD)
                                    if (/^\d{4}-\d{2}-\d{2}$/.test(dateToSet)) {
                                        try {
                                            flatpickrDateInstance.setDate(dateToSet, false);
                                            // Show time pickers for single date
                                            var eventTimeEl = document.getElementById('event-time');
                                            if (eventTimeEl) {
                                                eventTimeEl.style.display = 'block';
                                            }
                                        } catch (e) {
                                            // Fallback: set value directly on input element
                                            if (start_date) {
                                                start_date.value = dateToSet;
                                            }
                                        }
                                    }
                                }
                            }, 150);
                        }
                    }, 100);
                }
            }

            // Open create event modal with pre-selected date
            function openCreateEventModalWithDate(dateStr) {
                openCreateEventModal(dateStr);
            }

            // Close event modal
            function closeEventModal() {
                // Remove temporary dragged event if it wasn't saved
                if (tempDraggedEvent) {
                    tempDraggedEvent.remove();
                    tempDraggedEvent = null;
                }

                const modalEl = document.getElementById('event-modal');
                if (modalEl) {
                    // Remove focus from any focused element inside the modal first
                    const focusedElement = modalEl.querySelector(':focus');
                    if (focusedElement) {
                        focusedElement.blur();
                    }

                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) {
                        modal.hide();
                    } else {
                        // Remove focus before setting aria-hidden
                        document.activeElement?.blur();

                        // If no instance, manually hide
                        modalEl.style.display = 'none';
                        modalEl.setAttribute('aria-hidden', 'true');
                        modalEl.removeAttribute('aria-modal');

                        // Remove backdrop if exists
                        const backdrop = document.querySelector('.modal-backdrop');
                        if (backdrop) {
                            backdrop.remove();
                        }
                    }
                }

                // IMPORTANT: Don't reset the form or states here if drag data exists
                // Let the hidden.bs.modal event handle cleanup AFTER the revert
                if (!window.draggedEventNewDates) {
                    // Reset the form
                    document.getElementById('form-event').reset();
                    clearFormErrors();
                    flatpickrValueClear();

                    // Reset button state
                    const saveBtn = document.getElementById('btn-save-event');
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Add Event';
                    }

                    // Reset states
                    isEditMode = false;
                    isViewMode = false;
                    currentEventId = null;
                    window.currentEventIsPast = false;
                }

                // Always reset display states (safe to do immediately)
                const eventDetails = document.querySelector('.event-details');
                const eventForm = document.querySelector('.event-form');
                if (eventDetails) {
                    eventDetails.classList.add('d-none');
                    eventDetails.style.display = 'none';
                }
                if (eventForm) {
                    eventForm.classList.add('d-none');
                    eventForm.style.display = 'none';
                }
            }

            // View event
            function viewEvent(eventId) {
                // Clear temporary dragged event (we're viewing an existing event)
                tempDraggedEvent = null;

                // Reset states
                currentEventId = eventId;
                isViewMode = true;
                isEditMode = false;

                const modalEl = document.getElementById('event-modal');
                const modalTitle = document.getElementById('modal-title');

                fetch(API.getEvent(eventId))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to fetch event data: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(event => {

                        if (!event || !event.id) {
                            throw new Error('Invalid event data received');
                        }

                        // Check if event is in the past
                        var today = new Date();
                        today.setHours(0, 0, 0, 0);

                        var eventDate = new Date(event.start_date);
                        eventDate.setHours(0, 0, 0, 0);

                        var isPastEvent = eventDate < today;

                        // Store past event status globally
                        window.currentEventIsPast = isPastEvent;

                        // Set modal title FIRST
                        if (modalTitle) {
                            var titleText = event.title || 'Event Details';
                            if (isPastEvent) {
                                titleText += ' (Past Event - Read Only)';
                            }
                            modalTitle.textContent = titleText;
                        }

                        // Switch to view mode (this shows the event-details div and hides form)
                        switchToViewMode();

                        // Populate the details immediately (no timeout)
                        displayEventDetails(event);

                        // Force a reflow to ensure DOM is updated
                        const eventDetailsDiv = document.querySelector('.event-details');
                        if (eventDetailsDiv) {
                            void eventDetailsDiv.offsetHeight; // Force reflow
                        }

                        // NOW show the modal with everything ready
                        if (modalEl) {
                            let modal = bootstrap.Modal.getInstance(modalEl);
                            if (!modal) {
                                modal = new bootstrap.Modal(modalEl);
                            }

                            modalEl.removeAttribute('aria-hidden');
                            modalEl.setAttribute('aria-modal', 'true');

                            modal.show();
                        }
                    })
                    .catch(error => {
                        showToast('Failed to load event details: ' + error.message, 'error', 'Error');
                    });
            }

            // Display event details in modal
            function displayEventDetails(event) {
                // Event Type
                const eventTypeTag = document.getElementById('event-type-tag');
                if (eventTypeTag) {
                    eventTypeTag.textContent = event.event_type ? capitalizeFirst(event.event_type) : '';
                    // Update badge color based on event type
                    eventTypeTag.className = 'badge fs-12';
                    const typeColorMap = {
                        'academic': 'bg-primary-subtle text-primary',
                        'social': 'bg-success-subtle text-success',
                        'sports': 'bg-info-subtle text-info',
                        'cultural': 'bg-warning-subtle text-warning',
                        'workshop': 'bg-danger-subtle text-danger',
                        'seminar': 'bg-dark-subtle text-dark',
                        'conference': 'bg-primary-subtle text-primary',
                        'ceremony': 'bg-success-subtle text-success',
                        'meeting': 'bg-info-subtle text-info',
                        'other': 'bg-secondary-subtle text-secondary',
                    };
                    eventTypeTag.className += ' ' + (typeColorMap[event.event_type] || 'bg-secondary-subtle text-secondary');
                }

                // Event Status
                const eventStatusTag = document.getElementById('event-status-tag');
                if (eventStatusTag) {
                    eventStatusTag.textContent = event.status ? capitalizeFirst(event.status) : '';
                    eventStatusTag.className = 'badge fs-12 ms-2';
                    const statusColorMap = {
                        'draft': 'bg-secondary-subtle text-secondary',
                        'pending': 'bg-warning-subtle text-warning',
                        'approved': 'bg-info-subtle text-info',
                        'published': 'bg-success-subtle text-success',
                        'cancelled': 'bg-danger-subtle text-danger',
                        'completed': 'bg-primary-subtle text-primary',
                        'postponed': 'bg-warning-subtle text-warning',
                    };
                    eventStatusTag.className += ' ' + (statusColorMap[event.status] || 'bg-secondary-subtle text-secondary');
                }

                // Format date
                let dateFormat = '-';
                try {
                    if (event.start_date) {
                        const startDate = new Date(event.start_date);
                        if (!isNaN(startDate.getTime())) {
                            if (event.end_date) {
                                const endDate = new Date(event.end_date);
                                if (!isNaN(endDate.getTime()) && endDate.toDateString() !== startDate.toDateString()) {
                                    dateFormat = formatDate(startDate) + ' to ' + formatDate(endDate);
                                } else {
                                    dateFormat = formatDate(startDate);
                                }
                            } else {
                                dateFormat = formatDate(startDate);
                            }
                        }
                    }
                } catch (e) {
                    dateFormat = event.start_date || '-';
                }

                // Update date display
                const dateTag = document.getElementById('event-start-date-tag');
                if (dateTag) {
                    dateTag.textContent = dateFormat;
                }

                // Format time
                let startTimeFormatted = '';
                let endTimeFormatted = '';
                let hasTime = false;

                try {
                    if (event.start_time) {
                        startTimeFormatted = formatTime(event.start_time);
                        hasTime = true;
                    }
                    if (event.end_time) {
                        endTimeFormatted = formatTime(event.end_time);
                        hasTime = true;
                    }
                } catch (e) {
                }

                // Update time display
                const timeTag1 = document.getElementById('event-timepicker1-tag');
                const timeTag2 = document.getElementById('event-timepicker2-tag');
                const timeSeparator = document.getElementById('event-time-separator');

                if (timeTag1) {
                    timeTag1.textContent = hasTime ? (startTimeFormatted || '-') : 'All Day';
                }
                if (timeTag2) {
                    timeTag2.textContent = endTimeFormatted;
                }
                if (timeSeparator && hasTime && startTimeFormatted && endTimeFormatted) {
                    timeSeparator.style.display = 'inline';
                } else if (timeSeparator) {
                    timeSeparator.style.display = 'none';
                }

                // Update location
                const locationTag = document.getElementById('event-location-tag');
                if (locationTag) {
                    locationTag.textContent = event.location || 'Not specified';
                }

                // Update category
                const categoryTag = document.getElementById('event-category-tag');
                const categoryContainer = document.getElementById('event-category-container');
                if (categoryTag) {
                    categoryTag.textContent = event.category || 'Not specified';
                }
                if (categoryContainer) {
                    categoryContainer.style.display = event.category ? 'flex' : 'none';
                }

                // Update description
                const descriptionTag = document.getElementById('event-description-tag');
                if (descriptionTag) {
                    descriptionTag.textContent = event.description || 'No description provided';
                }

                // Update semester
                const semesterTag = document.getElementById('event-semester-tag');
                const semesterContainer = document.getElementById('event-semester-container');
                if (semesterTag) {
                    if (event.semester_name) {
                        semesterTag.textContent = event.semester_name;
                        if (semesterContainer) semesterContainer.style.display = 'block';
                    } else if (event.semester_id && formData) {
                        const semester = formData.semesterOptions.find(s => s.value == event.semester_id);
                        semesterTag.textContent = semester ? semester.label : 'Not specified';
                        if (semesterContainer) semesterContainer.style.display = semester ? 'block' : 'none';
                    } else {
                        semesterTag.textContent = 'Not specified';
                        if (semesterContainer) semesterContainer.style.display = 'none';
                    }
                }

                // Update section
                const sectionTag = document.getElementById('event-section-tag');
                const sectionContainer = document.getElementById('event-section-container');
                if (sectionTag) {
                    if (event.section_name) {
                        sectionTag.textContent = event.section_name;
                        if (sectionContainer) sectionContainer.style.display = 'block';
                    } else if (event.section_id && formData) {
                        const section = formData.sectionOptions.find(s => s.value == event.section_id);
                        sectionTag.textContent = section ? section.label : 'Not specified';
                        if (sectionContainer) sectionContainer.style.display = section ? 'block' : 'none';
                    } else {
                        sectionTag.textContent = 'Not specified';
                        if (sectionContainer) sectionContainer.style.display = 'none';
                    }
                }
            }

            // View event for drag (opens edit mode with new date)
            function viewEventForDrag(eventId) {
                // Clear temporary dragged event
                tempDraggedEvent = null;

                // Set states for edit mode
                currentEventId = eventId;
                isViewMode = false;
                isEditMode = true;

                const modalEl = document.getElementById('event-modal');
                const modalTitle = document.getElementById('modal-title');

                fetch(API.getEvent(eventId))
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Failed to fetch event data: ' + response.statusText);
                        }
                        return response.json();
                    })
                    .then(event => {
                        if (!event || !event.id) {
                            throw new Error('Invalid event data received');
                        }

                        // Set modal title
                        if (modalTitle) {
                            modalTitle.textContent = 'Edit Event: ' + (event.title || 'Event');
                        }

                        // Clear form and populate with event data
                        document.getElementById('eventid').value = event.id;
                        document.getElementById('event-title').value = event.title || '';
                        document.getElementById('event-description').value = event.description || '';
                        document.getElementById('event-type').value = event.event_type || '';
                        document.getElementById('event-category').value = event.category || '';
                        document.getElementById('event-location').value = event.location || '';
                        document.getElementById('event-status').value = event.status || '';
                        document.getElementById('event-semester').value = event.semester_id || '';
                        document.getElementById('event-section').value = event.section_id || '';

                        // Initialize Flatpickr if not already done
                        if (!flatpickrDateInstance || !flatpickrTime1Instance || !flatpickrTime2Instance) {
                            flatPickrInit();
                        }

                        // Update dates with the NEW dragged dates
                        if (window.draggedEventNewDates) {
                            const newDates = window.draggedEventNewDates;

                            // Set the new date in the date picker
                            if (flatpickrDateInstance && newDates.startDate) {
                                if (newDates.endDate && newDates.endDate !== newDates.startDate) {
                                    // Date range
                                    flatpickrDateInstance.setDate([newDates.startDate, newDates.endDate], false);
                                } else {
                                    // Single date
                                    flatpickrDateInstance.setDate(newDates.startDate, false);
                                }
                            }
                        }

                        // Set times if available
                        if (event.start_time) {
                            const startTime24 = event.start_time.split(' ')[1]?.substring(0, 5);
                            if (startTime24 && flatpickrTime1Instance) {
                                const startTime12 = convertTo12Hour(startTime24);
                                const timepicker1El = document.getElementById('timepicker1');
                                if (timepicker1El) {
                                    timepicker1El.value = startTime12;
                                    const [hours, minutesAndPeriod] = startTime12.split(':');
                                    const [minutes, period] = minutesAndPeriod.split(' ');
                                    const dateObj = new Date();
                                    dateObj.setHours(period === 'PM' && hours !== '12' ? parseInt(hours) + 12 :
                                        period === 'AM' && hours === '12' ? 0 : parseInt(hours));
                                    dateObj.setMinutes(parseInt(minutes));
                                    flatpickrTime1Instance.setDate(dateObj, false);
                                }
                            }
                        }

                        if (event.end_time) {
                            const endTime24 = event.end_time.split(' ')[1]?.substring(0, 5);
                            if (endTime24 && flatpickrTime2Instance) {
                                const endTime12 = convertTo12Hour(endTime24);
                                const timepicker2El = document.getElementById('timepicker2');
                                if (timepicker2El) {
                                    timepicker2El.value = endTime12;
                                    const [hours, minutesAndPeriod] = endTime12.split(':');
                                    const [minutes, period] = minutesAndPeriod.split(' ');
                                    const dateObj = new Date();
                                    dateObj.setHours(period === 'PM' && hours !== '12' ? parseInt(hours) + 12 :
                                        period === 'AM' && hours === '12' ? 0 : parseInt(hours));
                                    dateObj.setMinutes(parseInt(minutes));
                                    flatpickrTime2Instance.setDate(dateObj, false);
                                }
                            }
                        }

                        // Switch to form mode (edit)
                        switchToFormMode();

                        // Show the modal
                        if (modalEl) {
                            let modal = bootstrap.Modal.getInstance(modalEl);
                            if (!modal) {
                                modal = new bootstrap.Modal(modalEl);
                            }
                            modal.show();
                        }

                        // Clean up the stored dates
                        setTimeout(() => {
                            delete window.draggedEventNewDates;
                        }, 100);
                    })
                    .catch(error => {
                        showToast('Failed to load event for editing: ' + error.message, 'error', 'Error');

                        // Revert the calendar change
                        if (window.draggedEventNewDates && window.draggedEventNewDates.revertFunc) {
                            window.draggedEventNewDates.revertFunc();
                        }
                        delete window.draggedEventNewDates;
                    });
            }

            // Helper function to capitalize first letter
            function capitalizeFirst(str) {
                if (!str) return '';
                return str.charAt(0).toUpperCase() + str.slice(1);
            }

            // Edit event from view modal
            window.editEventFromView = function () {
                // Check permission before allowing edit
                if (!canManageEngagement) {
                    return;
                }

                // Check if event is in the past
                if (window.currentEventIsPast) {
                    showToast('Cannot edit past events', 'warning', 'Not Allowed');
                    return;
                }

                if (!currentEventId) {
                    return;
                }

                fetch(API.getEvent(currentEventId))
                    .then(response => response.json())
                    .then(event => {

                        isEditMode = true;
                        isViewMode = false;
                        currentEventId = event.id;

                        // Update modal title
                        document.getElementById('modal-title').textContent = 'Edit Event';

                        // Populate form fields
                        document.getElementById('eventid').value = event.id;
                        document.getElementById('event-title').value = event.title || '';
                        document.getElementById('event-description').value = event.description || '';
                        document.getElementById('event-type').value = event.event_type || '';
                        document.getElementById('event-category').value = event.category || '';
                        document.getElementById('event-location').value = event.location || '';
                        document.getElementById('event-semester').value = event.semester_id || '';
                        document.getElementById('event-section').value = event.section_id || '';
                        document.getElementById('event-status').value = event.status || 'draft';

                        // Initialize Flatpickr if not already initialized
                        if (!flatpickrDateInstance || !flatpickrTime1Instance || !flatpickrTime2Instance) {
                            flatPickrInit();
                        }

                        // Set date range in Flatpickr
                        var startDate = event.start_date ? event.start_date.split(' ')[0] : '';
                        var endDate = event.end_date ? event.end_date.split(' ')[0] : '';
                        var dateRange = endDate && endDate !== startDate ? startDate + ' to ' + endDate : startDate;

                        // Wait for Flatpickr to be ready before setting values
                        setTimeout(function () {
                            // Set date range
                            if (flatpickrDateInstance && dateRange && typeof dateRange === 'string') {
                                try {
                                    flatpickrDateInstance.setDate(dateRange, false);
                                } catch (e) {
                                    if (start_date) {
                                        start_date.value = dateRange;
                                    }
                                }
                            }

                            // Parse time - handle both "HH:MM" and "YYYY-MM-DD HH:MM:SS" formats
                            var startTime24 = '';
                            var endTime24 = '';

                            if (event.start_time) {
                                if (event.start_time.includes(' ')) {
                                    // Format: "YYYY-MM-DD HH:MM:SS"
                                    startTime24 = event.start_time.split(' ')[1]?.substring(0, 5) || '';
                                } else if (event.start_time.includes(':')) {
                                    // Format: "HH:MM:SS" or "HH:MM"
                                    startTime24 = event.start_time.substring(0, 5);
                                }
                            }

                            if (event.end_time) {
                                if (event.end_time.includes(' ')) {
                                    // Format: "YYYY-MM-DD HH:MM:SS"
                                    endTime24 = event.end_time.split(' ')[1]?.substring(0, 5) || '';
                                } else if (event.end_time.includes(':')) {
                                    // Format: "HH:MM:SS" or "HH:MM"
                                    endTime24 = event.end_time.substring(0, 5);
                                }
                            }

                            // Convert to 12-hour format for Flatpickr
                            var startTime = startTime24 ? convertTo12Hour(startTime24) : '';
                            var endTime = endTime24 ? convertTo12Hour(endTime24) : '';

                            // Get time picker elements
                            const timepicker1El = document.getElementById('timepicker1');
                            const timepicker2El = document.getElementById('timepicker2');

                            // Set start time
                            if (startTime) {

                                // Method 1: Set directly on input element (Flatpickr will parse it)
                                if (timepicker1El) {
                                    // First, set the value
                                    timepicker1El.value = startTime;

                                    // Then update Flatpickr's internal value if instance exists
                                    if (timepicker1El._flatpickr) {
                                        try {
                                            // Parse the 12-hour time to create a Date object
                                            const timeParts = startTime.match(/(\d+):(\d+)\s*(AM|PM)/i);
                                            if (timeParts) {
                                                let hours = parseInt(timeParts[1]);
                                                const minutes = parseInt(timeParts[2]);
                                                const meridiem = timeParts[3].toUpperCase();

                                                if (meridiem === 'PM' && hours !== 12) hours += 12;
                                                if (meridiem === 'AM' && hours === 12) hours = 0;

                                                const dateObj = new Date(2000, 0, 1, hours, minutes);
                                                timepicker1El._flatpickr.setDate(dateObj, false);
                                            }
                                        } catch (e) {
                                        }
                                    }
                                }
                            } else {
                                if (timepicker1El) {
                                    timepicker1El.value = '';
                                    if (timepicker1El._flatpickr) {
                                        timepicker1El._flatpickr.clear();
                                    }
                                }
                            }

                            // Set end time
                            if (endTime) {
                                // Method 1: Set directly on input element (Flatpickr will parse it)
                                if (timepicker2El) {
                                    // First, set the value
                                    timepicker2El.value = endTime;

                                    // Then update Flatpickr's internal value if instance exists
                                    if (timepicker2El._flatpickr) {
                                        try {
                                            // Parse the 12-hour time to create a Date object
                                            const timeParts = endTime.match(/(\d+):(\d+)\s*(AM|PM)/i);
                                            if (timeParts) {
                                                let hours = parseInt(timeParts[1]);
                                                const minutes = parseInt(timeParts[2]);
                                                const meridiem = timeParts[3].toUpperCase();

                                                if (meridiem === 'PM' && hours !== 12) hours += 12;
                                                if (meridiem === 'AM' && hours === 12) hours = 0;

                                                const dateObj = new Date(2000, 0, 1, hours, minutes);
                                                timepicker2El._flatpickr.setDate(dateObj, false);
                                            }
                                        } catch (e) {
                                        }
                                    }
                                }
                            } else {
                                if (timepicker2El) {
                                    timepicker2El.value = '';
                                    if (timepicker2El._flatpickr) {
                                        timepicker2El._flatpickr.clear();
                                    }
                                }
                            }
                        }, 150);

                        // Always show time pickers in edit mode
                        var eventTimeEl = document.getElementById('event-time');
                        if (eventTimeEl) {
                            eventTimeEl.style.display = 'block';
                        }

                        // Switch to form mode FIRST (this handles the display switching)
                        switchToFormMode();

                        // Ensure proper display after switchToFormMode
                        setTimeout(() => {
                            const eventDetails = document.querySelector('.event-details');
                            const eventForm = document.querySelector('.event-form');

                            if (eventDetails) {
                                eventDetails.classList.add('d-none');
                                eventDetails.style.display = 'none';
                            }

                            if (eventForm) {
                                eventForm.classList.remove('d-none');
                                eventForm.style.display = 'flex';
                            }

                            // Explicitly show time container
                            const eventTimeContainer = document.getElementById('event-time');
                            if (eventTimeContainer) {
                                eventTimeContainer.style.display = 'block';
                                eventTimeContainer.classList.remove('d-none');
                            }

                            // Explicitly set button states for edit mode (respect permission)
                            const deleteBtn = document.getElementById('btn-delete-event');
                            const saveBtn = document.getElementById('btn-save-event');

                            if (deleteBtn && canManageEngagement) {
                                deleteBtn.classList.remove('d-none');
                                deleteBtn.style.display = 'inline-block';
                            } else if (deleteBtn) {
                                deleteBtn.classList.add('d-none');
                                deleteBtn.style.display = 'none';
                            }

                            if (saveBtn && canManageEngagement) {
                                saveBtn.classList.remove('d-none');
                                saveBtn.style.display = 'inline-block';
                                saveBtn.textContent = 'Update Event';
                            } else if (saveBtn) {
                                saveBtn.classList.add('d-none');
                                saveBtn.style.display = 'none';
                            }
                        }, 50);
                    })
                    .catch(error => {
                        showToast('Failed to load event for editing', 'error', 'Error');
                    });
            };

            // Delete event
            window.deleteEventFromView = function () {
                // Check permission before allowing delete
                if (!canManageEngagement) {
                    return;
                }

                // Check if event is in the past
                if (window.currentEventIsPast) {
                    showToast('Cannot delete past events', 'warning', 'Not Allowed');
                    return;
                }

                if (!currentEventId) return;

                // Get event title for confirmation message
                const eventTitle = document.getElementById('modal-title').textContent || 'this event';

                // Get CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                    document.querySelector('input[name="_token"]')?.value ||
                    '{{ csrf_token() }}';

                // Show SweetAlert2 confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    html: `You are about to delete <strong>"${eventTitle}"</strong>.<br>This action cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="ri-delete-bin-line"></i> Yes, delete it!',
                    cancelButtonText: '<i class="ri-close-line"></i> Cancel',
                    buttonsStyling: true,
                    customClass: {
                        confirmButton: 'btn btn-danger me-2',
                        cancelButton: 'btn btn-secondary'
                    },
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return fetch(API.destroy(currentEventId), {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(response.statusText);
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (!data.success) {
                                    throw new Error(data.message || 'Failed to delete event');
                                }
                                return data;
                            })
                            .catch(error => {
                                Swal.showValidationMessage(
                                    `Request failed: ${error.message}`
                                );
                                return null;
                            });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        // Show success message
                        Swal.fire({
                            title: 'Deleted!',
                            text: result.value.message || 'Event has been deleted successfully.',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'btn btn-success'
                            },
                            timer: 2000,
                            timerProgressBar: true
                        });

                        // Close modal and reload events
                        closeEventModal();
                        loadCalendarEvents();
                    }
                });
            };

            // Helper function to convert 12-hour format to 24-hour format
            function convertTo24Hour(time12h) {
                if (!time12h) return '';

                // Check if already in 24-hour format (no AM/PM)
                if (!time12h.match(/AM|PM/i)) {
                    return time12h;
                }

                const [time, modifier] = time12h.split(' ');
                let [hours, minutes] = time.split(':');

                if (hours === '12') {
                    hours = '00';
                }

                if (modifier.toUpperCase() === 'PM') {
                    hours = parseInt(hours, 10) + 12;
                }

                return `${hours}:${minutes}`;
            }

            // Helper function to convert 24-hour format to 12-hour format
            function convertTo12Hour(time24h) {
                if (!time24h) return '';

                // Check if already in 12-hour format (has AM/PM)
                if (time24h.match(/AM|PM/i)) {
                    return time24h;
                }

                // Parse the time
                let [hours, minutes] = time24h.split(':');
                hours = parseInt(hours, 10);

                // Determine AM/PM
                const ampm = hours >= 12 ? 'PM' : 'AM';

                // Convert to 12-hour format
                hours = hours % 12;
                hours = hours ? hours : 12; // 0 should be 12

                // Pad minutes if needed
                minutes = minutes || '00';
                if (minutes.length === 1) minutes = '0' + minutes;

                return `${hours}:${minutes} ${ampm}`;
            }

            // Handle form submission
            const eventForm = document.getElementById('form-event');
            if (eventForm) {
                eventForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    clearFormErrors();

                    // Get save button and show loading state
                    const saveBtn = document.getElementById('btn-save-event');
                    const originalBtnText = saveBtn.textContent;
                    const isUpdate = originalBtnText.includes('Update');

                    // Set loading state
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = `
                                                                                                                                                     <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                                                                                                                                    ${isUpdate ? 'Updating...' : 'Saving...'}
                                                                                                                                                        `;

                    // Get Flatpickr date range value
                    var dateRangeValue = start_date.value;
                    var dates = dateRangeValue.split(" to ");
                    var startDate = dates[0] ? dates[0].trim() : '';
                    var endDate = dates.length > 1 ? dates[1].trim() : null;

                    // Get time values from Flatpickr and convert to 24-hour format
                    var startTime = timepicker1.value ? convertTo24Hour(timepicker1.value) : '';
                    var endTime = timepicker2.value ? convertTo24Hour(timepicker2.value) : '';

                    // Build form data
                    const data = {
                        eventid: document.getElementById('eventid').value || '',
                        title: document.getElementById('event-title').value,
                        description: document.getElementById('event-description').value,
                        event_type: document.getElementById('event-type').value,
                        category: document.getElementById('event-category').value,
                        start_date: startDate,
                        end_date: endDate,
                        start_time: startTime,
                        end_time: endTime,
                        location: document.getElementById('event-location').value,
                        semester_id: document.getElementById('event-semester').value || null,
                        section_id: document.getElementById('event-section').value || null,
                        status: document.getElementById('event-status').value
                    };

                    const eventId = data.eventid;
                    const url = eventId ? API.update(eventId) : API.store;
                    const method = eventId ? 'PUT' : 'POST';

                    // Get CSRF token safely
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                        document.querySelector('input[name="_token"]')?.value ||
                        '{{ csrf_token() }}';

                    fetch(url, {
                        method: method,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    })
                        .then(response => {
                            return response.json();
                        })
                        .then(result => {
                            // Reset button state
                            saveBtn.disabled = false;
                            saveBtn.textContent = originalBtnText;

                            if (result.success) {
                                // Clear temporary dragged event reference (it's now saved)
                                tempDraggedEvent = null;

                                // Clear dragged event dates (DON'T revert since we saved successfully)
                                if (window.draggedEventNewDates) {
                                    delete window.draggedEventNewDates;
                                }

                                showToast(result.message, 'success', 'Success');
                                closeEventModal();
                                loadCalendarEvents();
                            } else {
                                if (result.errors) {
                                    displayFormErrors(result.errors);
                                }
                                showToast(result.message || 'Operation failed', 'error', 'Error');
                            }
                        })
                        .catch(error => {
                            // Reset button state on error
                            saveBtn.disabled = false;
                            saveBtn.textContent = originalBtnText;

                            showToast('Failed to save event: ' + error.message, 'error', 'Error');
                        });
                });
            }

            // Display form errors
            function displayFormErrors(errors) {
                Object.keys(errors).forEach(field => {
                    const input = document.getElementById('event-' + field) || document.getElementById(field);
                    const errorDiv = document.getElementById(field + '-error');
                    if (input) {
                        input.classList.add('is-invalid');
                    }
                    if (errorDiv) {
                        errorDiv.textContent = errors[field][0];
                    }
                });
            }

            // Clear form errors
            function clearFormErrors() {
                document.querySelectorAll('.is-invalid').forEach(el => {
                    el.classList.remove('is-invalid');
                });
                document.querySelectorAll('[id$="-error"]').forEach(el => {
                    el.textContent = '';
                });
            }

            // Format date helper
            function formatDate(date) {
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                return `${date.getDate()} ${months[date.getMonth()]}, ${date.getFullYear()}`;
            }

            // Format time helper
            function formatTime(timeString) {
                if (!timeString) return '-';

                try {
                    // Handle different time formats
                    let time;
                    if (typeof timeString === 'string') {
                        // If it's already in HH:mm format, use it directly
                        if (/^\d{2}:\d{2}$/.test(timeString)) {
                            const [hours, minutes] = timeString.split(':');
                            time = new Date(2000, 0, 1, parseInt(hours), parseInt(minutes));
                        } else if (timeString.includes(' ')) {
                            // If it's a datetime string, extract time part
                            const timePart = timeString.split(' ')[1] || timeString.split('T')[1];
                            const [hours, minutes] = timePart.split(':');
                            time = new Date(2000, 0, 1, parseInt(hours), parseInt(minutes));
                        } else {
                            time = new Date('2000-01-01 ' + timeString);
                        }
                    } else {
                        time = new Date(timeString);
                    }

                    if (isNaN(time.getTime())) {
                        return timeString; // Return original if parsing fails
                    }

                    let hours = time.getHours();
                    const minutes = time.getMinutes();
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12;
                    const minutesStr = minutes < 10 ? '0' + minutes : minutes;
                    return `${hours}:${minutesStr} ${ampm}`;
                } catch (e) {
                    return timeString || '-';
                }
            }

            // Refresh calendar function
            function refreshCalendar() {
                const refreshBtn = document.getElementById('btn-refresh-calendar');

                if (refreshBtn) {
                    // Show loading state
                    const originalContent = refreshBtn.innerHTML;
                    refreshBtn.disabled = true;
                    refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Refreshing...';

                    // Reload calendar events
                    if (window.engagementCalendarInstance) {
                        window.engagementCalendarInstance.refetchEvents();
                    }

                    // Reload upcoming events
                    loadUpcomingEvents();

                    // Show success message and restore button
                    setTimeout(() => {
                        showToast('Calendar refreshed successfully', 'success', 'Refreshed');
                        refreshBtn.disabled = false;
                        refreshBtn.innerHTML = originalContent;
                    }, 800);
                }
            }

            // Event listeners
            const btnNewEvent = document.getElementById('btn-new-event');
            if (btnNewEvent) {
                btnNewEvent.addEventListener('click', openCreateEventModal);
            }

            const btnDeleteEvent = document.getElementById('btn-delete-event');
            if (btnDeleteEvent) {
                btnDeleteEvent.addEventListener('click', deleteEventFromView);
            }

            const btnRefreshCalendar = document.getElementById('btn-refresh-calendar');
            if (btnRefreshCalendar) {
                btnRefreshCalendar.addEventListener('click', refreshCalendar);
            }

            // Handle modal close events (X button, backdrop click, ESC key)
            const eventModal = document.getElementById('event-modal');
            if (eventModal) {
                eventModal.addEventListener('hidden.bs.modal', function () {
                    // Remove temporary dragged event if modal is closed without saving
                    if (tempDraggedEvent) {
                        tempDraggedEvent.remove();
                        tempDraggedEvent = null;
                    }

                    // Manually revert dragged event if modal closed without saving
                    if (window.draggedEventNewDates) {
                        try {
                            var originalStart = window.draggedEventNewDates.originalStart;
                            var originalEnd = window.draggedEventNewDates.originalEnd;
                            var eventId = window.draggedEventNewDates.eventId;

                            // Get the calendar instance
                            var calendar = window.engagementCalendarInstance;

                            if (calendar && eventId && originalStart) {
                                // Find the event in the calendar by ID
                                var event = calendar.getEventById(eventId);

                                if (event) {
                                    event.setStart(originalStart);

                                    if (originalEnd) {
                                        event.setEnd(originalEnd);
                                    } else {
                                        // Clear end date if original didn't have one
                                        event.setEnd(null);
                                    }

                                    // Force calendar to refresh/re-render the event
                                    calendar.refetchEvents();
                                } else {
                                    loadCalendarEvents();
                                }
                            }
                        } catch (e) {
                        }

                        delete window.draggedEventNewDates;
                        document.getElementById('form-event').reset();
                        clearFormErrors();
                        flatpickrValueClear();

                        // Reset button state
                        const saveBtn = document.getElementById('btn-save-event');
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.textContent = 'Add Event';
                        }

                        // Reset states
                        isEditMode = false;
                        isViewMode = false;
                        currentEventId = null;
                        window.currentEventIsPast = false;
                    } else {
                        // Also reset form if no drag data (normal close)
                        document.getElementById('form-event').reset();
                        clearFormErrors();
                        flatpickrValueClear();

                        // Reset button state
                        const saveBtn = document.getElementById('btn-save-event');
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.textContent = 'Add Event';
                        }

                        // Reset states
                        isEditMode = false;
                        isViewMode = false;
                        currentEventId = null;
                        window.currentEventIsPast = false;
                    }
                });
            }

            // Initialize
            loadFormData();
            setTimeout(function () {
                initCalendar();
                // Initialize Flatpickr after form data is loaded
                setTimeout(flatPickrInit, 200);
                // Load upcoming events after calendar is initialized
                setTimeout(loadUpcomingEvents, 300);
            }, 100);

            // Handle window resize
            var resizeTimer;
            window.addEventListener('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function () {
                    if (window.engagementCalendarInstance && calendarInitialized) {
                        try {
                            var newView = getInitialView();
                            window.engagementCalendarInstance.changeView(newView);
                        } catch (e) {
                        }
                    }
                }, 250);
            });
        });
    </script>
@endsection