{{-- Include Toast Notification Component --}}
<div>
    <x-toast-notification />

    <div class="card">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-lg-auto">
                    <div class="hstack gap-2">
                        @can('manage-attendance')
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                                <i class="ri-price-tag-3-line align-bottom me-1"></i> Add Category
                            </button>
                        @endcan
                        <button class="btn btn-soft-primary" onclick="window.reloadAttendanceCategories()"
                            title="Refresh Categories">
                            <i class="ri-refresh-line align-bottom"></i>
                        </button>
                    </div>
                </div>
                <!--end col-->
                <div class="col-lg-3 col-auto">
                    <div class="search-box">
                        <input type="text" class="form-control search" id="search-task-options"
                            placeholder="Search categories ...">
                        <i class="ri-search-line search-icon"></i>
                    </div>
                </div>

                <!--end col-->
            </div>
            <!--end row-->
        </div>
        <!--end card-body-->
    </div>
    <!--end card-->

    <div class="tasks-board mb-3" id="kanbanboard">
        <!-- Categories will be dynamically loaded here -->
    </div>
    <!--end kanbanboard-->

    <!--end task-board-->



    <!--end add board modal-->

    <!-- Create Attendance Modal -->
    <div class="modal fade" id="createAttendanceModal" tabindex="-1" aria-labelledby="createAttendanceModalLabel"
        data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-info-subtle">
                    <h5 class="modal-title" id="createAttendanceModalLabel">Create Attendance Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="createAttendanceForm">
                        <input type="hidden" id="attendanceId" name="attendance_id" value="">
                        <div class="row g-3">
                            <!-- Title -->
                            <div class="col-lg-12">
                                <label for="attendanceTitle" class="form-label">Title <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="attendanceTitle" name="title"
                                    placeholder="Enter attendance session title" required>
                                <div class="invalid-feedback" id="error-title"></div>
                            </div>

                            <!-- Description -->
                            <div class="col-lg-12">
                                <label for="attendanceDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="attendanceDescription" name="description" rows="3"
                                    placeholder="Enter description (optional)"></textarea>
                            </div>

                            <!-- Attendance Type -->
                            <div class="col-lg-12">
                                <label for="attendanceType" class="form-label">Type</label>
                                <select class="form-select" id="attendanceType" name="attendance_type">
                                    <option value="" disabled selected>Select Type</option>
                                    <option value="class">Class</option>
                                    <option value="laboratory">Laboratory</option>
                                    <option value="lecture">Lecture</option>
                                    <option value="exam">Exam</option>
                                    <option value="event">Event</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="other">Other</option>
                                </select>
                                <div class="invalid-feedback" id="error-attendance_type"></div>
                            </div>

                            <!-- Semester -->
                            <div class="col-lg-6">
                                <label for="attendanceSemester" class="form-label">Semester <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="attendanceSemester" name="semester_id" required>
                                    <option value="" disabled selected>Select Semester</option>
                                    <!-- Options will be populated dynamically -->
                                </select>
                                <div class="invalid-feedback" id="error-semester_id"></div>
                            </div>

                            <!-- Section -->
                            <div class="col-lg-6">
                                <label for="attendanceSection" class="form-label">Section <span
                                        class="text-danger">*</span></label>
                                <x-select-vanilla id="attendanceSection" name="section_ids"
                                    placeholder="Select Section(s)" :options="[]" :searchable="true" :multiple="true" />
                                <div class="invalid-feedback" id="error-section_ids"></div>
                                <div class="mt-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="addAllSections"
                                            name="add_all_sections">
                                        <label class="form-check-label" for="addAllSections">
                                            Add All Sections
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Date -->
                            <div class="col-lg-12">
                                <label for="attendanceDate" class="form-label">Date <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control flatpickr flatpickr-input"
                                        id="attendanceDate" name="date" placeholder="Select date" readonly required>
                                    <span class="input-group-text"><i class="ri-calendar-event-line"></i></span>
                                </div>
                                <div class="invalid-feedback" id="error-date"></div>
                            </div>

                            <!-- Start Time -->
                            <div class="col-lg-6">
                                <label for="attendanceStartTime" class="form-label">Start Time <small
                                        class="text-muted">(at
                                        least one required)</small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control flatpickr flatpickr-input"
                                        id="attendanceStartTime" name="start_time" placeholder="Select start time"
                                        readonly>
                                    <span class="input-group-text"><i class="ri-time-line"></i></span>
                                </div>
                                <div class="invalid-feedback" id="error-start_time"></div>
                            </div>

                            <!-- End Time -->
                            <div class="col-lg-6">
                                <label for="attendanceEndTime" class="form-label">End Time <small class="text-muted">(at
                                        least one required)</small></label>
                                <div class="input-group">
                                    <input type="text" class="form-control flatpickr flatpickr-input"
                                        id="attendanceEndTime" name="end_time" placeholder="Select end time" readonly>
                                    <span class="input-group-text"><i class="ri-time-line"></i></span>
                                </div>
                                <div class="invalid-feedback" id="error-end_time"></div>
                            </div>

                            <!-- Location -->
                            <div class="col-lg-12">
                                <label for="attendanceLocation" class="form-label">Location <small
                                        class="text-muted">(at
                                        least one required)</small></label>
                                <input type="text" class="form-control" id="attendanceLocation" name="location"
                                    placeholder="Enter location">
                                <div class="invalid-feedback" id="error-location"></div>
                            </div>

                            <!-- Is Active -->
                            <div class="col-lg-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="attendanceIsActive"
                                        name="is_active" checked>
                                    <label class="form-check-label" for="attendanceIsActive">
                                        Active (Students can check-in)
                                    </label>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="col-lg-12">
                                <div class="hstack gap-2 justify-content-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success" id="submitAttendanceBtn">
                                        <span class="button-text">Create Attendance</span>
                                        <span class="button-spinner d-none">
                                            <span class="spinner-border spinner-border-sm" role="status"
                                                aria-hidden="true"></span>
                                            <span class="ms-1">Processing...</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end modal -->
    <!--end add board modal-->

    <!-- Create Category Modal -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-success-subtle">
                    <h5 class="modal-title" id="createCategoryModalLabel">Add Attendance Category</h5>
                    <button type="button" class="btn-close" id="addCategoryBtn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        <div class="row g-3">
                            <div class="col-lg-12">
                                <label for="categoryName" class="form-label">Category Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="categoryName" name="name"
                                    placeholder="Enter category name" required>
                                <div class="invalid-feedback">Please enter a category name.</div>
                            </div>

                            <div class="col-lg-12">
                                <label for="categoryDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="categoryDescription" name="description" rows="3"
                                    placeholder="Enter category description"></textarea>
                            </div>

                            <div class="col-lg-6">
                                <label for="categoryDisplayOrder" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="categoryDisplayOrder" name="display_order"
                                    value="0" min="0">
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-switch form-switch-lg mt-2">
                                    <input class="form-check-input" type="checkbox" id="categoryIsActive"
                                        name="is_active" checked>
                                    <label class="form-check-label" for="categoryIsActive">Active</label>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="hstack gap-2 justify-content-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success" id="addNewCategory">
                                        <span class="button-text">
                                            <i class="ri-save-line align-bottom me-1"></i> Create Category
                                        </span>
                                        <span class="button-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-1" role="status"
                                                aria-hidden="true"></span>
                                            Creating...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end create category modal -->

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-info-subtle">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Attendance Category</h5>
                    <button type="button" class="btn-close" id="editCategoryBtn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCategoryForm">
                        <input type="hidden" id="editCategoryId" name="id">
                        <div class="row g-3">
                            <div class="col-lg-12">
                                <label for="editCategoryName" class="form-label">Category Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editCategoryName" name="name"
                                    placeholder="Enter category name" required>
                                <div class="invalid-feedback">Please enter a category name.</div>
                            </div>

                            <div class="col-lg-12">
                                <label for="editCategoryDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="editCategoryDescription" name="description" rows="3"
                                    placeholder="Enter category description"></textarea>
                            </div>

                            <div class="col-lg-6">
                                <label for="editCategoryDisplayOrder" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="editCategoryDisplayOrder"
                                    name="display_order" value="0" min="0">
                            </div>

                            <div class="col-lg-6">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-switch form-switch-lg mt-2">
                                    <input class="form-check-input" type="checkbox" id="editCategoryIsActive"
                                        name="is_active">
                                    <label class="form-check-label" for="editCategoryIsActive">Active</label>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="hstack gap-2 justify-content-end">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-info" id="updateCategory">
                                        <span class="button-text">
                                            <i class="ri-save-line align-bottom me-1"></i> Update Category
                                        </span>
                                        <span class="button-spinner d-none">
                                            <span class="spinner-border spinner-border-sm me-1" role="status"
                                                aria-hidden="true"></span>
                                            Updating...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!--end edit category modal -->

    <div class="modal fade zoomIn" id="deleteCategoryModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="delete-btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="mt-2 text-center">
                        <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                            colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                        <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                            <h4>Are you sure ?</h4>
                            <p class="text-muted mx-4 mb-0">Are you sure you want to delete this category? This action
                                cannot be undone.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn w-sm btn-danger" id="confirm-delete-category">
                            <span class="button-text">Yes, Delete It!</span>
                            <span class="button-spinner d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span class="ms-1">Deleting...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end delete category modal -->

    <!-- Delete Attendance Confirmation Modal -->
    <div class="modal fade zoomIn" id="deleteAttendanceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                        id="delete-attendance-btn-close"></button>
                </div>
                <div class="modal-body">
                    <div class="mt-2 text-center">
                        <lord-icon src="https://cdn.lordicon.com/gsqxdxog.json" trigger="loop"
                            colors="primary:#f7b84b,secondary:#f06548" style="width:100px;height:100px"></lord-icon>
                        <div class="mt-4 pt-2 fs-15 mx-4 mx-sm-5">
                            <h4>Are you sure ?</h4>
                            <p class="text-muted mx-4 mb-0">Are you sure you want to delete this attendance session?
                                This
                                action
                                cannot be undone and will also delete all associated student attendance records.</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-center mt-4 mb-2">
                        <button type="button" class="btn w-sm btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn w-sm btn-danger" id="confirm-delete-attendance">
                            <span class="button-text">Yes, Delete It!</span>
                            <span class="button-spinner d-none">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span class="ms-1">Deleting...</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end delete attendance modal -->

    <!-- View Students Modal -->
    <div class="modal fade" id="viewStudentsModal" tabindex="-1" aria-labelledby="viewStudentsModalLabel"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0">
                <div class="modal-header p-3 bg-primary-subtle">
                    <h5 class="modal-title" id="viewStudentsModalLabel">
                        <i class="ri-group-line align-bottom me-2"></i> Students Attendance
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Loading State -->
                    <div id="studentsLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading students...</p>
                    </div>

                    <!-- Students List -->
                    <div id="studentsContent" class="d-none">
                        <!-- Stats Cards -->
                        <div class="row g-3 mb-4" id="studentsStats">
                            <!-- Stats will be populated here -->
                        </div>

                        <!-- Search Bar -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" class="form-control" id="studentsSearchInput"
                                    placeholder="Search by name, student ID, status, or remarks...">
                                <button class="btn btn-light" type="button" id="clearStudentsSearch"
                                    style="display: none;">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                            <small class="text-muted" id="studentsSearchResults"></small>
                        </div>

                        <!-- Bulk Actions Container -->
                        <div id="bulkActionsContainer" class="mb-3 p-3 bg-light rounded border" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-medium text-muted">
                                        <span id="selectedCount">0 selected</span>
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success btn-sm" id="bulkApproveBtn"
                                        onclick="bulkApproveStudents()" disabled>
                                        <i class="ri-check-line align-bottom me-1"></i> Approve Selected
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" id="bulkDisapproveBtn"
                                        onclick="bulkDisapproveStudents()" disabled>
                                        <i class="ri-close-line align-bottom me-1"></i> Disapprove Selected
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAllStudents">
                                            </div>
                                        </th>
                                        <th scope="col">#</th>
                                        <th scope="col">Student Name</th>
                                        <th scope="col">Student ID</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Check In</th>
                                        <th scope="col">Check Out</th>
                                        <th scope="col">Duration</th>
                                        <th scope="col">Remarks</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsTableBody">
                                    <!-- Students will be populated here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3"
                            id="studentsPaginationContainer" style="display: none !important;">
                            <div>
                                <p class="text-muted mb-0">
                                    Showing <span id="studentsPaginationInfo">0-0 of 0</span> students
                                </p>
                            </div>
                            <nav aria-label="Students pagination">
                                <ul class="pagination pagination-sm mb-0" id="studentsPagination">
                                    <!-- Pagination will be populated here -->
                                </ul>
                            </nav>
                        </div>

                        <!-- Empty State -->
                        <div id="studentsEmpty" class="text-center py-5 d-none">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#405189,secondary:#0ab39c" style="width:100px;height:100px"></lord-icon>
                            <p class="text-muted mt-3 mb-0">No students found for this attendance session.</p>
                        </div>

                        <!-- No Search Results State -->
                        <div id="studentsNoSearchResults" class="text-center py-5 d-none">
                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                colors="primary:#405189,secondary:#0ab39c" style="width:100px;height:100px"></lord-icon>
                            <p class="text-muted mt-3 mb-0">No students found matching your search.</p>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div id="studentsError" class="text-center py-5 d-none">
                        <lord-icon src="https://cdn.lordicon.com/tdrtiskw.json" trigger="loop"
                            colors="primary:#f06548,secondary:#f7b84b" style="width:100px;height:100px"></lord-icon>
                        <p class="text-danger mt-3 mb-0" id="studentsErrorMessage">Failed to load students.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end view students modal -->
</div>