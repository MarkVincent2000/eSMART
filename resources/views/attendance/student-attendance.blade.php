{{-- Student Attendance Partial - Include this in index.blade.php when viewing a specific attendance --}}
<x-toast-notification />

@php
    // Prepare attendance data for JavaScript
    $attendanceData = [
        'id' => $attendance->id,
        'date' => $attendance->date->format('Y-m-d'),
        'start_time' => $attendance->start_time ? $attendance->start_time->utc()->toIso8601String() : null,
        'end_time' => $attendance->end_time ? $attendance->end_time->utc()->toIso8601String() : null,
        'category_name' => $attendance->category->name ?? 'Attendance Session'
    ];

    // Prepare student attendance data if exists
    $studentAttendanceData = null;
    if ($currentUserStudentAttendance) {
        $studentAttendanceData = [
            'id' => $currentUserStudentAttendance->id,
            'check_in_time' => $currentUserStudentAttendance->check_in_time ? $currentUserStudentAttendance->check_in_time->utc()->toIso8601String() : null,
            'check_out_time' => $currentUserStudentAttendance->check_out_time ? $currentUserStudentAttendance->check_out_time->utc()->toIso8601String() : null,
            'status' => $currentUserStudentAttendance->status,
            'duration_minutes' => $currentUserStudentAttendance->duration_minutes ?? 0
        ];
    }
@endphp

<script>
    // Pass data to JavaScript using JSON encoding (safe method)
    window.currentAttendanceId = {{ $attendance->id }};
    window.currentUserId = {{ auth()->id() }};
    @if($currentUserStudentAttendance)
        window.currentStudentAttendanceId = {{ $currentUserStudentAttendance->id }};
    @else
        window.currentStudentAttendanceId = null;
    @endif

    window.attendanceData = @json($attendanceData);
    window.studentAttendanceData = @json($studentAttendanceData);
</script>

<div class="row">
    <div class="col-xxl-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="card-title mb-3 flex-grow-1 text-start">Time Tracking</h6>
                <div class="mb-2">
                    <lord-icon src="https://cdn.lordicon.com/kbtmbyzy.json" trigger="loop"
                        colors="primary:#405189,secondary:#02a8b5" style="width:90px;height:90px"></lord-icon>
                </div>

                <!-- Digital Clock -->
                <div class="mb-3">
                    <div class="card border-primary-subtle bg-primary-subtle">
                        <div class="card-body py-2">
                            <p class="text-muted mb-1 fs-11">Current Time</p>
                            <div id="digitalClock" class="fw-bold fs-18 text-primary">
                                <span id="clockTime">--:--:--</span>
                                <span id="clockAmPm" class="fs-14 ms-1">--</span>
                            </div>
                            <p class="text-muted mb-0 fs-10" id="clockDate">-- --, ----</p>
                        </div>
                    </div>
                </div>

                <div id="timeTrackingAlertContainer"></div>
                <h3 class="mb-1" id="timeDuration">N/A</h3>
                <h5 class="fs-14 mb-2">
                    <i class="ri-time-line align-bottom me-1"></i>
                    <span id="startTime">N/A</span> - <span id="endTime">N/A</span>
                </h5>
                <p class="text-muted mb-4 fs-12" id="attendanceTitle">Attendance Session</p>
                <div class="hstack gap-2 justify-content-center" id="timeTrackingButtons">
                    <button class="btn btn-danger btn-sm" id="timeOutBtn" style="display: none;">
                        <i class="ri-logout-circle-line align-bottom me-1"></i>Time Out
                    </button>
                    <button class="btn btn-success btn-sm" id="timeInBtn" style="display: none;">
                        <i class="ri-login-circle-line align-bottom me-1"></i>Time In
                    </button>
                </div>
                <div id="noAttendanceMessage" style="display: none;">
                    <h3 class="mb-1">N/A</h3>
                    <h5 class="fs-14 mb-4">No Attendance Record</h5>
                    <p class="text-muted mb-4 fs-12">You are not enrolled in this attendance session.</p>
                </div>
            </div>
        </div>
        <!--end card-->
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="card-title mb-3">Attendance Information</h6>
                <div class="table-card">
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-medium">Category</td>
                                <td>{{ $attendance->category->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Date</td>
                                <td>{{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('M d, Y') : 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Type</td>
                                <td>
                                    @if($attendance->attendance_type)
                                        <span
                                            class="badge bg-info-subtle text-info">{{ ucfirst($attendance->attendance_type) }}</span>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Semester</td>
                                <td>{{ $attendance->semester->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Sections</td>
                                <td>
                                    @if($attendance->sections->count() > 0)
                                        {{ $attendance->sections->pluck('name')->join(', ') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-medium">Location</td>
                                <td>{{ $attendance->location ?? 'N/A' }}</td>
                            </tr>
                            @if($currentUserStudentAttendance)
                                <tr>
                                    <td class="fw-medium">Your Status</td>
                                    <td>
                                        @php
                                            $status = $currentUserStudentAttendance->status;
                                            $badgeClass = 'bg-secondary-subtle text-secondary';
                                            if ($status === 'present') {
                                                $badgeClass = 'bg-success-subtle text-success';
                                            } elseif ($status === 'absent') {
                                                $badgeClass = 'bg-danger-subtle text-danger';
                                            } elseif ($status === 'late') {
                                                $badgeClass = 'bg-warning-subtle text-warning';
                                            } elseif ($status === 'pending') {
                                                $badgeClass = 'bg-info-subtle text-info';
                                            } elseif ($status === 'excused') {
                                                $badgeClass = 'bg-primary-subtle text-primary';
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                    </td>
                                </tr>
                                @if($currentUserStudentAttendance->check_in_time)
                                    <tr>
                                        <td class="fw-medium">Check In</td>
                                        <td>{{ \Carbon\Carbon::parse($currentUserStudentAttendance->check_in_time)->setTimezone('Asia/Manila')->format('h:i A') }}
                                        </td>
                                    </tr>
                                @endif
                                @if($currentUserStudentAttendance->check_out_time)
                                    <tr>
                                        <td class="fw-medium">Check Out</td>
                                        <td>{{ \Carbon\Carbon::parse($currentUserStudentAttendance->check_out_time)->setTimezone('Asia/Manila')->format('h:i A') }}
                                        </td>
                                    </tr>
                                @endif
                                @if($currentUserStudentAttendance->duration_minutes)
                                    <tr>
                                        <td class="fw-medium">Duration</td>
                                        <td>
                                            @php
                                                $durMinutes = $currentUserStudentAttendance->duration_minutes;
                                                $durHours = floor($durMinutes / 60);
                                                $durMinutesRemainder = $durMinutes % 60;
                                                $durDisplay = '';
                                                if ($durHours > 0) {
                                                    $durDisplay = "{$durHours} " . ($durHours == 1 ? 'hr' : 'hrs');
                                                    if ($durMinutesRemainder > 0) {
                                                        $durDisplay .= " {$durMinutesRemainder} " . ($durMinutesRemainder == 1 ? 'min' : 'min');
                                                    }
                                                } else {
                                                    $durDisplay = "{$durMinutesRemainder} " . ($durMinutesRemainder == 1 ? 'min' : 'min');
                                                }
                                            @endphp
                                            {{ $durDisplay }}
                                        </td>
                                    </tr>
                                @endif
                            @endif
                        </tbody>
                    </table>
                    <!--end table-->
                </div>
            </div>
        </div>
        <!--end card-->
        <div>
            <h5 class="mb-1">Students</h5>
            <p class="text-muted">All students in this attendance session</p>
            <div class="pe-2 me-n1 mb-3" data-simplebar style="height: 100px">
                <div id="studentsListContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">Loading students...</p>
                    </div>
                </div>
            </div>
        </div>
        <!--end card-->

    </div>
    <!---end col-->
    <div class="col-xxl-9">

        <!--end card-->
        <div class="card">
            <div class="card-header">
                <div>
                    <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#home-1" role="tab">
                                Comments (5)
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#profile-1" role="tab">
                                Time Entries
                                <span id="timeEntriesTabDuration">(0 min)</span>
                            </a>
                        </li>
                    </ul>
                    <!--end nav-->
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="home-1" role="tabpanel">
                        <h5 class="card-title mb-4">Comments</h5>
                        <div data-simplebar style="height: 508px;" class="px-3 mx-n3 mb-2">
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ URL::asset('build/images/users/avatar-7.jpg') }}" alt=""
                                        class="avatar-xs rounded-circle material-shadow" />
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="fs-13"><a href="pages-profile">Joseph Parker</a> <small
                                            class="text-muted">20 Dec 2021 - 05:47AM</small></h5>
                                    <p class="text-muted">I am getting message from customers that when they place
                                        order always get error message .</p>
                                    <a href="javascript: void(0);" class="badge text-muted bg-light"><i
                                            class="mdi mdi-reply"></i> Reply</a>
                                    <div class="d-flex mt-4">
                                        <div class="flex-shrink-0">
                                            <img src="{{ URL::asset('build/images/users/avatar-10.jpg') }}" alt=""
                                                class="avatar-xs rounded-circle material-shadow" />
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="fs-13"><a href="pages-profile">Tonya Noble</a> <small
                                                    class="text-muted">22 Dec 2021 - 02:32PM</small></h5>
                                            <p class="text-muted">Please be sure to check your Spam mailbox to see if
                                                your email filters have identified the email from Dell as spam.</p>
                                            <a href="javascript: void(0);" class="badge text-muted bg-light"><i
                                                    class="mdi mdi-reply"></i> Reply</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex mb-4">
                                <div class="flex-shrink-0">
                                    <img src="{{ URL::asset('build/images/users/avatar-8.jpg') }}" alt=""
                                        class="avatar-xs rounded-circle material-shadow" />
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="fs-13"><a href="pages-profile">Thomas Taylor</a> <small
                                            class="text-muted">24 Dec 2021 - 05:20PM</small></h5>
                                    <p class="text-muted">If you have further questions, please contact Customer
                                        Support from the "Action Menu" on your <a href="javascript:void(0);"
                                            class="text-decoration-underline">Online Order Support</a>.</p>
                                    <a href="javascript: void(0);" class="badge text-muted bg-light"><i
                                            class="mdi mdi-reply"></i> Reply</a>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <img src="{{ URL::asset('build/images/users/avatar-10.jpg') }}" alt=""
                                        class="avatar-xs rounded-circle material-shadow" />
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="fs-13"><a href="pages-profile">Tonya Noble</a> <small
                                            class="text-muted">26 min ago</small></h5>
                                    <p class="text-muted">Your <a href="javascript:void(0)"
                                            class="text-decoration-underline">Online Order Support</a> provides you
                                        with the most current status of your order. To help manage your order refer to
                                        the "Action Menu" to initiate return, contact Customer Support and more.</p>
                                    <div class="row g-2 mb-3">
                                        <div class="col-lg-1 col-sm-2 col-6">
                                            <img src="{{ URL::asset('build/images/small/img-4.jpg') }}" alt=""
                                                class="img-fluid rounded">
                                        </div>
                                        <div class="col-lg-1 col-sm-2 col-6">
                                            <img src="{{ URL::asset('build/images/small/img-5.jpg') }}" alt=""
                                                class="img-fluid rounded">
                                        </div>
                                    </div>
                                    <a href="javascript: void(0);" class="badge text-muted bg-light"><i
                                            class="mdi mdi-reply"></i> Reply</a>
                                    <div class="d-flex mt-4">
                                        <div class="flex-shrink-0">
                                            <img src="{{ URL::asset('build/images/users/avatar-6.jpg') }}" alt=""
                                                class="avatar-xs rounded-circle material-shadow" />
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h5 class="fs-13"><a href="pages-profile">Nancy Martino</a> <small
                                                    class="text-muted">8 sec ago</small></h5>
                                            <p class="text-muted">Other shipping methods are available at checkout if
                                                you want your purchase delivered faster.</p>
                                            <a href="javascript: void(0);" class="badge text-muted bg-light"><i
                                                    class="mdi mdi-reply"></i> Reply</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form class="mt-4">
                            <div class="row g-3">
                                <div class="col-lg-12">
                                    <label for="exampleFormControlTextarea1" class="form-label">Leave a
                                        Comments</label>
                                    <textarea class="form-control bg-light border-light"
                                        id="exampleFormControlTextarea1" rows="3"
                                        placeholder="Enter comments"></textarea>
                                </div>
                                <!--end col-->
                                <div class="col-12 text-end">
                                    <button type="button" class="btn btn-ghost-secondary btn-icon waves-effect me-1"><i
                                            class="ri-attachment-line fs-16"></i></button>
                                    <a href="javascript:void(0);" class="btn btn-success">Post Comments</a>
                                </div>
                            </div>
                            <!--end row-->
                        </form>
                    </div>
                    <!--end tab-pane-->

                    <!--end tab-pane-->
                    <div class="tab-pane" id="profile-1" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
                            <h6 class="card-title mb-0">Time Entries</h6>
                            <div class="d-flex gap-2">
                                <div class="search-box">
                                    <input type="text" id="timeEntriesSearch" class="form-control form-control-sm"
                                        placeholder="Search time entries...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive table-card">
                            <table class="table align-middle mb-0">
                                <thead class="table-light text-muted">
                                    <tr>
                                        <th scope="col">Member</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Time In</th>
                                        <th scope="col">Time Out</th>
                                        <th scope="col">Duration</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="timeEntriesTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="text-muted mb-0 mt-2">Loading time entries...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <!--end table-->
                        </div>
                        <!-- Pagination -->
                        <div id="timeEntriesPagination" class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <p class="text-muted mb-0" id="timeEntriesSummary">Showing 0 to 0 of 0 entries</p>
                            </div>
                            <div>
                                <nav aria-label="Time entries pagination">
                                    <ul class="pagination pagination-sm mb-0" id="timeEntriesPaginationList">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <!--edn tab-pane-->

                </div>
                <!--end tab-content-->
            </div>
        </div>
        <!--end card-->
    </div>
    <!--end col-->
</div>


<!-- end modal -->

{{-- Time In Confirmation Modal --}}
<div class="modal fade" id="timeInModal" tabindex="-1" aria-labelledby="timeInModalLabel" aria-hidden="true"
    data-bs-backdrop="static" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-3 ps-4 bg-success-subtle">
                <h5 class="modal-title" id="timeInModalLabel">
                    <i class="ri-login-circle-line align-bottom me-2"></i>Time In Confirmation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center">
                    <div class="mb-3">
                        <lord-icon src="https://cdn.lordicon.com/kbtmbyzy.json" trigger="loop"
                            colors="primary:#28a745,secondary:#28a745" style="width:80px;height:80px"></lord-icon>
                    </div>
                    <h5 class="mb-3">Confirm Time In</h5>
                    <p class="text-muted mb-0">Are you sure you want to record your time in? This action will set your
                        attendance status to pending.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmTimeInBtn">
                    <i class="ri-login-circle-line align-bottom me-1"></i>Yes, Time In
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Time Out Confirmation Modal --}}
<div class="modal fade" id="timeOutModal" tabindex="-1" aria-labelledby="timeOutModalLabel" aria-hidden="true"
    data-bs-backdrop="static" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-3 ps-4 bg-danger-subtle">
                <h5 class="modal-title" id="timeOutModalLabel">
                    <i class="ri-logout-circle-line align-bottom me-2"></i>Time Out Confirmation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center">
                    <div class="mb-3">
                        <lord-icon src="https://cdn.lordicon.com/kbtmbyzy.json" trigger="loop"
                            colors="primary:#dc3545,secondary:#dc3545" style="width:80px;height:80px"></lord-icon>
                    </div>
                    <h5 class="mb-3">Confirm Time Out</h5>
                    <p class="text-muted mb-0">Are you sure you want to record your time out? This action will calculate
                        your duration and set your attendance status to pending.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmTimeOutBtn">
                    <i class="ri-logout-circle-line align-bottom me-1"></i>Yes, Time Out
                </button>
            </div>
        </div>
    </div>
</div>