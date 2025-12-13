<div class="row">
    <x-toast-notification />
    <div class="col-12">
        <div class="row">
            <div class="col-xl-3">
                @can('manage-engagement')
                    <div class="card card-h-100">
                        <div class="card-body">
                            <button class="btn btn-primary w-100" id="btn-new-event"><i class="mdi mdi-plus"></i> Create New
                                Event</button>

                            <div id="external-events">
                                <br>
                                <p class="text-muted">Drag and drop your event or click in the calendar</p>
                                <div id="external-events-list"></div>
                            </div>

                        </div>
                    </div>
                @endcan
                <div>
                    <h5 class="mb-1">Upcoming Events</h5>
                    <p class="text-muted">Don't miss scheduled events</p>
                    <div class="pe-2 me-n1 mb-3" data-simplebar style="height: 300px">
                        <div id="upcoming-event-list"></div>
                    </div>
                </div>

            </div> <!-- end col-->

            <div class="col-xl-9">
                <div class="card card-h-100">
                    <div class="card-header border-0 d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Calendar</h5>
                        <button class="btn btn-sm btn-soft-primary" id="btn-refresh-calendar" title="Refresh Calendar">
                            <i class="ri-refresh-line align-middle"></i> Refresh
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div><!-- end col -->
        </div>
        <!--end row-->

        <div style='clear:both'></div>

        <!-- Add New Event MODAL -->
        <div class="modal fade" id="event-modal" tabindex="-1" aria-labelledby="modal-title" aria-hidden="true"
            data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content border-0">
                    <div class="modal-header p-3 bg-info-subtle">
                        <h5 class="modal-title" id="modal-title">Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form class="needs-validation" name="event-form" id="form-event" novalidate>
                            <input type="hidden" id="eventid" name="eventid" value="" />

                            <!-- View Mode -->
                            <div class="event-details d-none">
                                @can('manage-engagement')
                                    <div class="text-end mb-3">
                                        <button type="button" class="btn btn-sm btn-soft-primary" id="edit-event-btn"
                                            onclick="editEventFromView(); return false;">
                                            <i class="ri-edit-line align-bottom"></i> Edit
                                        </button>
                                    </div>
                                @endcan

                                <!-- Event Type Badge -->
                                <div class="mb-3">
                                    <span class="badge bg-primary-subtle text-primary fs-12" id="event-type-tag"></span>
                                    <span class="badge bg-success-subtle text-success fs-12 ms-2"
                                        id="event-status-tag"></span>
                                </div>

                                <!-- Date -->
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="ri-calendar-event-line text-muted fs-16"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-muted">Date</h6>
                                        <p class="fw-semibold mb-0" id="event-start-date-tag">-</p>
                                    </div>
                                </div>

                                <!-- Time -->
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="ri-time-line text-muted fs-16"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-muted">Time</h6>
                                        <p class="fw-semibold mb-0">
                                            <span id="event-timepicker1-tag">-</span>
                                            <span id="event-time-separator"> - </span>
                                            <span id="event-timepicker2-tag"></span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="ri-map-pin-line text-muted fs-16"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-muted">Location</h6>
                                        <p class="fw-semibold mb-0" id="event-location-tag">-</p>
                                    </div>
                                </div>

                                <!-- Category -->
                                <div class="d-flex mb-3" id="event-category-container">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="ri-price-tag-3-line text-muted fs-16"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-muted">Category</h6>
                                        <p class="fw-semibold mb-0" id="event-category-tag">-</p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="ri-discuss-line text-muted fs-16"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 text-muted">Description</h6>
                                        <p class="text-muted mb-0" id="event-description-tag">-</p>
                                    </div>
                                </div>

                                <!-- Semester & Section -->
                                <div class="row">
                                    <div class="col-6" id="event-semester-container">
                                        <div class="d-flex mb-2">
                                            <div class="flex-shrink-0 me-2">
                                                <i class="ri-book-line text-muted fs-16"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 text-muted">Semester</h6>
                                                <p class="fw-semibold mb-0" id="event-semester-tag">-</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6" id="event-section-container">
                                        <div class="d-flex mb-2">
                                            <div class="flex-shrink-0 me-2">
                                                <i class="ri-group-line text-muted fs-16"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 text-muted">Section</h6>
                                                <p class="fw-semibold mb-0" id="event-section-tag">-</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Mode -->
                            <div class="row event-form">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Event Title <span class="text-danger">*</span></label>
                                        <input class="form-control" placeholder="Enter event title" type="text"
                                            name="title" id="event-title" required value="" />
                                        <div class="invalid-feedback" id="title-error"></div>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" id="event-description"
                                            placeholder="Enter a description" rows="3" name="description"
                                            spellcheck="false"></textarea>
                                        <div class="invalid-feedback" id="description-error"></div>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" name="status" id="event-status" required>
                                            <option value="">Select status</option>
                                        </select>
                                        <div class="invalid-feedback" id="status-error"></div>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Event Type <span class="text-danger">*</span></label>
                                        <select class="form-select" name="event_type" id="event-type" required>
                                            <option value="">Select event type</option>
                                        </select>
                                        <div class="invalid-feedback" id="event_type-error"></div>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <input class="form-control" placeholder="Enter category" type="text"
                                            name="category" id="event-category" value="" />
                                        <div class="invalid-feedback" id="category-error"></div>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label>Event Date <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" id="event-start-date"
                                                class="form-control flatpickr flatpickr-input" placeholder="Select date"
                                                readonly required>
                                            <span class="input-group-text"><i class="ri-calendar-event-line"></i></span>
                                        </div>
                                        <div class="invalid-feedback" id="start_date-error"></div>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-12" id="event-time">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label">Start Time</label>
                                                <div class="input-group">
                                                    <input id="timepicker1" type="text"
                                                        class="form-control flatpickr flatpickr-input"
                                                        placeholder="Select start time" readonly>
                                                    <span class="input-group-text"><i class="ri-time-line"></i></span>
                                                </div>
                                                <div class="invalid-feedback" id="start_time-error"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label">End Time</label>
                                                <div class="input-group">
                                                    <input id="timepicker2" type="text"
                                                        class="form-control flatpickr flatpickr-input"
                                                        placeholder="Select end time" readonly>
                                                    <span class="input-group-text"><i class="ri-time-line"></i></span>
                                                </div>
                                                <div class="invalid-feedback" id="end_time-error"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end col-->
                                <!--end col-->
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="event-location">Location</label>
                                        <input type="text" class="form-control" name="location" id="event-location"
                                            placeholder="Event location">
                                        <div class="invalid-feedback" id="location-error"></div>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Semester</label>
                                        <select class="form-select" name="semester_id" id="event-semester">
                                            <option value="">Select semester</option>
                                        </select>
                                        <div class="invalid-feedback" id="semester_id-error"></div>
                                    </div>
                                </div>
                                <!--end col-->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Section</label>
                                        <select class="form-select" name="section_id" id="event-section">
                                            <option value="">Select section</option>
                                        </select>
                                        <div class="invalid-feedback" id="section_id-error"></div>
                                    </div>
                                </div>
                                <!--end col-->
                            </div>
                            <!--end row-->
                            <div class="hstack gap-2 justify-content-end">
                                @can('manage-engagement')
                                    <button type="button" class="btn btn-soft-danger" id="btn-delete-event"
                                        style="display: none;">
                                        <i class="ri-close-line align-bottom"></i> Delete
                                    </button>
                                @endcan
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                @can('manage-engagement')
                                    <button type="submit" class="btn btn-success" id="btn-save-event"
                                        style="display: inline-block;">
                                        Add Event
                                    </button>
                                @endcan
                            </div>
                        </form>
                    </div>
                </div> <!-- end modal-content-->
            </div> <!-- end modal dialog-->
        </div> <!-- end modal-->
        <!-- end modal-->
    </div>
</div> <!-- end row-->