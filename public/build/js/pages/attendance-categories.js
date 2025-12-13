/**
 * Attendance Category Management
 * 
 * This script handles all client-side operations for attendance categories including:
 * - Creating new categories
 * - Editing existing categories
 * - Deleting categories
 * - Activating/Deactivating categories
 * - Reordering categories
 */

(function () {
    'use strict';

    // Permission check - default to false if not set
    const canManageAttendance = window.canManageAttendance || false;

    // Global storage for all categories data (for search functionality)
    let allCategoriesData = [];

    // API endpoints
    const API = {
        BASE: '/attendance/categories',
        INDEX: '/attendance/categories',
        STORE: '/attendance/categories',
        UPDATE: (id) => `/attendance/categories/${id}`,
        DELETE: (id) => `/attendance/categories/${id}`,
        ACTIVATE: (id) => `/attendance/categories/${id}/activate`,
        DEACTIVATE: (id) => `/attendance/categories/${id}/deactivate`,
        ACTIVE: '/attendance/categories/active',
    };

    // Get CSRF token
    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            console.error('CSRF token not found in meta tag');
        }
        return token;
    }

    // Category Form Handler
    const categoryForm = document.getElementById('categoryForm');
    const editCategoryForm = document.getElementById('editCategoryForm');

    if (categoryForm) {
        categoryForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('addNewCategory');
            const buttonText = submitBtn.querySelector('.button-text');
            const buttonSpinner = submitBtn.querySelector('.button-spinner');

            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                description: formData.get('description') || null,
                display_order: parseInt(formData.get('display_order')) || 0,
                is_active: document.getElementById('categoryIsActive').checked,
            };

            // Show loading state
            setButtonLoading(submitBtn, buttonText, buttonSpinner, true);

            try {
                const token = getCsrfToken();
                if (!token) {
                    showToast('Error', 'CSRF token not found. Please refresh the page.', 'error');
                    setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
                    return;
                }

                const response = await fetch(API.STORE, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Show success message
                    showToast('Success', result.message || 'Category created successfully', 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createCategoryModal'));
                    modal.hide();

                    // Reset form
                    categoryForm.reset();

                    // Reload categories
                    loadCategories();
                } else {
                    // Show error message
                    if (response.status === 419) {
                        showToast('Session Expired', 'Your session has expired. Please refresh the page and try again.', 'error');
                    } else if (result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join('<br>');
                        showToast('Validation Error', errorMessages, 'error');
                    } else {
                        showToast('Error', result.message || 'Failed to create category', 'error');
                    }
                }
            } catch (error) {
                console.error('Error creating category:', error);
                showToast('Error', 'An error occurred while creating the category. Please check the console for details.', 'error');
            } finally {
                // Hide loading state
                setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
            }
        });
    }

    // Edit Category Form Handler
    if (editCategoryForm) {
        editCategoryForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const submitBtn = document.getElementById('updateCategory');
            const buttonText = submitBtn.querySelector('.button-text');
            const buttonSpinner = submitBtn.querySelector('.button-spinner');

            const categoryId = document.getElementById('editCategoryId').value;
            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                description: formData.get('description') || null,
                display_order: parseInt(formData.get('display_order')) || 0,
                is_active: document.getElementById('editCategoryIsActive').checked,
            };

            // Show loading state
            setButtonLoading(submitBtn, buttonText, buttonSpinner, true);

            try {
                const token = getCsrfToken();
                if (!token) {
                    showToast('Error', 'CSRF token not found. Please refresh the page.', 'error');
                    setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
                    return;
                }

                const response = await fetch(API.UPDATE(categoryId), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Show success message
                    showToast('Success', result.message || 'Category updated successfully', 'success');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editCategoryModal'));
                    modal.hide();

                    // Reload categories
                    loadCategories();
                } else {
                    // Show error message
                    if (response.status === 419) {
                        showToast('Session Expired', 'Your session has expired. Please refresh the page and try again.', 'error');
                    } else if (result.errors) {
                        const errorMessages = Object.values(result.errors).flat().join('<br>');
                        showToast('Validation Error', errorMessages, 'error');
                    } else {
                        showToast('Error', result.message || 'Failed to update category', 'error');
                    }
                }
            } catch (error) {
                console.error('Error updating category:', error);
                showToast('Error', 'An error occurred while updating the category. Please check the console for details.', 'error');
            } finally {
                // Hide loading state
                setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
            }
        });
    }


    // Load categories
    async function loadCategories() {
        const kanbanboard = document.getElementById('kanbanboard');
        if (!kanbanboard) {
            console.error('Kanbanboard element not found');
            return;
        }

        // Show loading state
        kanbanboard.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading categories...</p>
            </div>
        `;

        try {
            const response = await fetch(API.INDEX, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success && result.data) {
                // Store all categories data for search
                allCategoriesData = result.data;
                displayCategories(result.data);
            } else {
                kanbanboard.innerHTML = `
                    <div class="col-12">
                        <div class="card border-0 shadow-none">
                            <div class="card-body text-center py-5">
                                <div class="mb-4">
                                    <lord-icon src="https://cdn.lordicon.com/nocovwne.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:120px;height:120px"></lord-icon>
                                </div>
                                <h5 class="mb-3">No Categories Found</h5>
                                <p class="text-muted mb-4">
                                    You haven't created any attendance categories yet.<br>
                                    Categories help you organize different types of attendance sessions.
                                </p>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Create Your First Category
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            kanbanboard.innerHTML = `
                <div class="col-12">
                    <div class="card border-0 shadow-none">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <lord-icon src="https://cdn.lordicon.com/tdrtiskw.json" trigger="loop" colors="primary:#f06548,secondary:#f7b84b" style="width:120px;height:120px"></lord-icon>
                            </div>
                            <h5 class="mb-3 text-danger">Failed to Load Categories</h5>
                            <p class="text-muted mb-4">
                                We couldn't load the categories. Please check your connection and try again.
                            </p>
                            <button class="btn btn-primary" onclick="location.reload()">
                                <i class="ri-refresh-line align-bottom me-1"></i> Refresh Page
                            </button>
                        </div>
                    </div>
                </div>
            `;
            showToast('Error', 'Failed to load categories', 'error');
        }
    }

    // Display categories in the UI
    function displayCategories(categories) {
        const kanbanboard = document.getElementById('kanbanboard');
        if (!kanbanboard) {
            console.error('Kanbanboard element not found');
            return;
        }

        // Clear existing content
        kanbanboard.innerHTML = '';

        // Check if categories exist
        if (!categories || categories.length === 0) {
            kanbanboard.innerHTML = `
                <div class="col-12">
                    <div class="card border-0 shadow-none">
                        <div class="card-body text-center py-5">
                            <div class="mb-4">
                                <lord-icon src="https://cdn.lordicon.com/nocovwne.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:120px;height:120px"></lord-icon>
                            </div>
                            <h5 class="mb-3">No Categories Found</h5>
                            <p class="text-muted mb-4">
                                You haven't created any attendance categories yet.<br>
                                Categories help you organize different types of attendance sessions.
                            </p>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                                <i class="ri-add-line align-bottom me-1"></i> Create Your First Category
                            </button>
                        </div>
                    </div>
                </div>
            `;
            return;
        }

        // Create a column for each category
        categories.forEach((category, index) => {
            const categoryColumn = createCategoryColumn(category);
            kanbanboard.appendChild(categoryColumn);
        });

        console.log('✓ Categories loaded successfully:', categories.length);
        
        // Show success toast on first load
        if (!window.categoriesLoadedOnce) {
            window.categoriesLoadedOnce = true;
            showToast('Success', `Loaded ${categories.length} categories`, 'success');
        }
    }

    // Create a category column
    function createCategoryColumn(category) {
        const tasksListDiv = document.createElement('div');
        tasksListDiv.className = 'tasks-list';
        tasksListDiv.setAttribute('data-category-id', category.id);

        // Get attendances count
        const attendancesCount = category.attendances ? category.attendances.length : 0;

        tasksListDiv.innerHTML = `
            <div class="d-flex mb-3">
                <div class="flex-grow-1">
                    <h6 class="fs-14 text-uppercase fw-semibold mb-0">
                        ${escapeHtml(category.name)} 
                        <small class="badge ${category.is_active ? 'bg-success' : 'bg-secondary'} align-bottom ms-1 totaltask-badge">${attendancesCount}</small>
                    </h6>
                    ${category.description ? `<small class="text-muted">${escapeHtml(category.description)}</small>` : ''}
                </div>
                ${canManageAttendance ? `
                    <div class="flex-shrink-0">
                        <div class="dropdown card-header-dropdown">
                            <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="fw-medium text-muted fs-12">Actions<i class="mdi mdi-chevron-down ms-1"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="javascript:void(0);" onclick="editCategory(${category.id})">
                                    <i class="ri-edit-2-line align-bottom me-2 text-muted"></i> Edit Category
                                </a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="deleteCategory(${category.id})">
                                    <i class="ri-delete-bin-5-line align-bottom me-2 text-muted"></i> Delete Category
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="toggleCategoryStatus(${category.id}, ${category.is_active})">
                                    <i class="ri-${category.is_active ? 'close' : 'check'}-circle-line align-bottom me-2 text-muted"></i> 
                                    ${category.is_active ? 'Deactivate' : 'Activate'}
                                </a>
                            </div>
                        </div>
                    </div>
                ` : ''}
            </div>
            <div data-simplebar class="tasks-wrapper px-3 mx-n3">
                <div id="category-${category.id}-attendances" class="tasks">
                    ${renderAttendances(category.attendances || [])}
                </div>
            </div>
            ${canManageAttendance ? `
                <div class="my-3">
                    <button class="btn btn-soft-info w-100" onclick="addAttendance(${category.id})">
                        <i class="ri-add-line align-bottom me-1"></i> Add Attendance
                    </button>
                </div>
            ` : ''}
        `;

        return tasksListDiv;
    }

    // Render attendances for a category
    function renderAttendances(attendances) {
        if (!attendances || attendances.length === 0) {
            return `
                <div class="text-center py-4">
                    <lord-icon src="https://cdn.lordicon.com/wloilxuq.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:80px;height:80px"></lord-icon>
                    <p class="text-muted mt-3 mb-0">No attendances yet</p>
                    <small class="text-muted">Click "Add Attendance" to get started</small>
                </div>
            `;
        }

        return attendances.map(attendance => renderAttendanceCard(attendance)).join('');
    }

    // Render a single attendance card
    function renderAttendanceCard(attendance) {
        // Format date
        const date = attendance.date ? new Date(attendance.date).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric', 
            year: 'numeric' 
        }) : 'N/A';

        // Format time range
        let timeRange = '';
        if (attendance.start_time && attendance.end_time) {
            const startTime = new Date(attendance.start_time).toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            const endTime = new Date(attendance.end_time).toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
            timeRange = `${startTime} - ${endTime}`;
        } else if (attendance.start_time) {
            timeRange = new Date(attendance.start_time).toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
        }

        // Get sections info
        const sectionsInfo = attendance.sections && attendance.sections.length > 0
            ? attendance.sections.map(s => escapeHtml(s.name || `Section ${s.id}`)).join(', ')
            : 'No sections';

        // Get semester info
        const semesterInfo = attendance.semester 
            ? escapeHtml(attendance.semester.name || 'N/A')
            : 'N/A';

        // Status badge
        const statusBadge = attendance.is_active 
            ? '<span class="badge bg-success-subtle text-success">Active</span>'
            : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';

        // Type badge
        const typeBadge = attendance.attendance_type 
            ? `<span class="badge bg-info-subtle text-info">${escapeHtml(attendance.attendance_type.charAt(0).toUpperCase() + attendance.attendance_type.slice(1))}</span>`
            : '';

        return `
            <div class="card tasks-box" data-attendance-id="${attendance.id}">
                <div class="card-body">
                    <div class="d-flex mb-2">
                        <div class="flex-grow-1">
                            <h6 class="fs-15 mb-1">
                                <a href="javascript:void(0);" class="text-body">${escapeHtml(attendance.title || 'Untitled')}</a>
                            </h6>
                            <p class="text-muted mb-2 fs-12">
                                <i class="ri-calendar-event-line align-bottom me-1"></i> ${date}
                                ${timeRange ? `<br><i class="ri-time-line align-bottom me-1"></i> ${timeRange}` : ''}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="text-muted" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="ri-more-fill fs-16"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="viewAttendance(${attendance.id})">
                                        <i class="ri-eye-line align-bottom me-2 text-muted"></i> View
                                    </a>
                                     ${canManageAttendance ? `
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="viewStudents(${attendance.id})">
                                        <i class="ri-group-line align-bottom me-2 text-muted"></i> Students
                                    </a>
                                   
                                       
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="editAttendance(${attendance.id})">
                                            <i class="ri-edit-2-line align-bottom me-2 text-muted"></i> Edit
                                        </a>
                                         <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" href="javascript:void(0);" onclick="deleteAttendance(${attendance.id})">
                                            <i class="ri-delete-bin-5-line align-bottom me-2 text-muted"></i> Delete
                                        </a>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                    ${attendance.description ? `<p class="text-muted mb-2 fs-13">${escapeHtml(attendance.description)}</p>` : ''}
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="d-flex gap-1 flex-wrap">
                                ${statusBadge}
                                ${typeBadge}
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="ri-book-open-line align-bottom me-1"></i> ${semesterInfo}
                                    <br>
                                    <i class="ri-group-line align-bottom me-1"></i> ${sectionsInfo}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Utility: Escape HTML to prevent XSS
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }

    // Store selected category ID for attendance creation
    let selectedCategoryId = null;

    // Flatpickr instances
    let flatpickrDateInstance = null;
    let flatpickrStartTimeInstance = null;
    let flatpickrEndTimeInstance = null;

    // Store current attendance ID for editing
    let currentAttendanceId = null;

    // Add attendance function - opens modal
    window.addAttendance = function(categoryId) {
        console.log('Add attendance for category:', categoryId);
        selectedCategoryId = categoryId;
        currentAttendanceId = null; // Reset edit mode
        
        // Reset form
        resetAttendanceForm();
        
        // Load form data before showing modal
        loadAttendanceFormData();
        
        // Update modal title and button
        updateModalForCreate();
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('createAttendanceModal'));
        modal.show();

        // Initialize flatpickr after modal is shown
        setTimeout(() => {
            initializeFlatpickr();
        }, 100);
    };

    // Update modal for create mode
    function updateModalForCreate() {
        const modalLabel = document.getElementById('createAttendanceModalLabel');
        const submitBtn = document.getElementById('submitAttendanceBtn');
        const buttonText = submitBtn.querySelector('.button-text');
        
        if (modalLabel) modalLabel.textContent = 'Create Attendance Session';
        if (buttonText) buttonText.textContent = 'Create Attendance';
    }

    // Update modal for edit mode
    function updateModalForEdit() {
        const modalLabel = document.getElementById('createAttendanceModalLabel');
        const submitBtn = document.getElementById('submitAttendanceBtn');
        const buttonText = submitBtn.querySelector('.button-text');
        
        if (modalLabel) modalLabel.textContent = 'Edit Attendance Session';
        if (buttonText) buttonText.textContent = 'Update Attendance';
    }

    // Reset attendance form
    function resetAttendanceForm() {
        const form = document.getElementById('createAttendanceForm');
        if (form) {
            form.reset();
        }
        document.getElementById('attendanceId').value = '';
        clearFlatpickrValues();
        clearFormErrors();
        
        // Reset "Add All Sections" toggle
        const addAllSectionsToggle = document.getElementById('addAllSections');
        if (addAllSectionsToggle) {
            addAllSectionsToggle.checked = false;
        }
        
        // Clear section select
        const vanillaSectionSelect = window['vanillaSelect_attendanceSection'];
        if (vanillaSectionSelect) {
            vanillaSectionSelect.setValue([]);
            vanillaSectionSelect.enable();
        }
    }

    // Load attendance data for editing
    async function loadAttendanceForEdit(attendanceId) {
        try {
            const token = getCsrfToken();
            const response = await fetch(`/attendance/${attendanceId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load attendance data');
            }

            const result = await response.json();
            
            if (result.success && result.data && result.data.attendance) {
                const attendance = result.data.attendance;
                
                // Set attendance ID
                currentAttendanceId = attendance.id;
                document.getElementById('attendanceId').value = attendance.id;
                
                // Set category ID
                selectedCategoryId = attendance.category_id;
                
                // Populate form fields
                document.getElementById('attendanceTitle').value = attendance.title || '';
                document.getElementById('attendanceDescription').value = attendance.description || '';
                
                // Set attendance type
                const typeSelect = document.getElementById('attendanceType');
                if (typeSelect && attendance.attendance_type) {
                    typeSelect.value = attendance.attendance_type;
                }
                
                // Set semester
                const semesterSelect = document.getElementById('attendanceSemester');
                if (semesterSelect && attendance.semester_id) {
                    semesterSelect.value = attendance.semester_id;
                }
                
                // Set sections
                const vanillaSectionSelect = window['vanillaSelect_attendanceSection'];
                if (vanillaSectionSelect && attendance.sections && attendance.sections.length > 0) {
                    const sectionIds = attendance.sections.map(s => s.id);
                    vanillaSectionSelect.setValue(sectionIds);
                }
                
                // Set date - convert from Y-m-d to Date object for Flatpickr
                if (attendance.date) {
                    // Parse the date string (format: "Y-m-d" or "Y-m-d H:i:s")
                    const dateStr = attendance.date.split(' ')[0]; // Get just the date part
                    const dateParts = dateStr.split('-');
                    if (dateParts.length === 3) {
                        const year = parseInt(dateParts[0], 10);
                        const month = parseInt(dateParts[1], 10) - 1; // JavaScript months are 0-indexed
                        const day = parseInt(dateParts[2], 10);
                        const dateObj = new Date(year, month, day);
                        
                        if (flatpickrDateInstance) {
                            // Use Date object for Flatpickr
                            flatpickrDateInstance.setDate(dateObj, false);
                        } else {
                            // Fallback: format as m/d/Y string
                            const formattedDate = `${month + 1}/${day}/${year}`;
                            document.getElementById('attendanceDate').value = formattedDate;
                        }
                    }
                }
                
                // Set start time - convert from 24-hour to 12-hour format
                if (attendance.start_time) {
                    // Parse the datetime string (format: "Y-m-d H:i:s")
                    const startTimeStr = attendance.start_time;
                    const timeMatch = startTimeStr.match(/(\d{2}):(\d{2}):(\d{2})/);
                    if (timeMatch) {
                        const hours = parseInt(timeMatch[1], 10);
                        const minutes = parseInt(timeMatch[2], 10);
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        const hours12 = hours % 12 || 12;
                        const formattedTime = `${hours12}:${minutes.toString().padStart(2, '0')} ${ampm}`;
                        
                        if (flatpickrStartTimeInstance) {
                            flatpickrStartTimeInstance.setDate(formattedTime, false);
                        } else {
                            document.getElementById('attendanceStartTime').value = formattedTime;
                        }
                    }
                }
                
                // Set end time - convert from 24-hour to 12-hour format
                if (attendance.end_time) {
                    // Parse the datetime string (format: "Y-m-d H:i:s")
                    const endTimeStr = attendance.end_time;
                    const timeMatch = endTimeStr.match(/(\d{2}):(\d{2}):(\d{2})/);
                    if (timeMatch) {
                        const hours = parseInt(timeMatch[1], 10);
                        const minutes = parseInt(timeMatch[2], 10);
                        const ampm = hours >= 12 ? 'PM' : 'AM';
                        const hours12 = hours % 12 || 12;
                        const formattedTime = `${hours12}:${minutes.toString().padStart(2, '0')} ${ampm}`;
                        
                        if (flatpickrEndTimeInstance) {
                            flatpickrEndTimeInstance.setDate(formattedTime, false);
                        } else {
                            document.getElementById('attendanceEndTime').value = formattedTime;
                        }
                    }
                }
                
                // Set location
                document.getElementById('attendanceLocation').value = attendance.location || '';
                
                // Set is_active
                document.getElementById('attendanceIsActive').checked = attendance.is_active !== false;
                
                return true;
            } else {
                throw new Error('Invalid attendance data');
            }
        } catch (error) {
            console.error('Error loading attendance for edit:', error);
            showToast('Error', 'Failed to load attendance data: ' + error.message, 'error');
            return false;
        }
    }

    // Initialize Flatpickr for attendance form
    function initializeFlatpickr() {
        // Date picker - Format: m/d/yyyy
        const dateInput = document.getElementById('attendanceDate');
        if (dateInput && !flatpickrDateInstance) {
            flatpickrDateInstance = flatpickr(dateInput, {
                dateFormat: "m/d/Y",
                defaultDate: new Date(),
            });
        }

        // Start time picker - Format: 12-hour with AM/PM
        const startTimeInput = document.getElementById('attendanceStartTime');
        if (startTimeInput && !flatpickrStartTimeInstance) {
            flatpickrStartTimeInstance = flatpickr(startTimeInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K",
                time_24hr: false,
            });
        }

        // End time picker - Format: 12-hour with AM/PM
        const endTimeInput = document.getElementById('attendanceEndTime');
        if (endTimeInput && !flatpickrEndTimeInstance) {
            flatpickrEndTimeInstance = flatpickr(endTimeInput, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i K",
                time_24hr: false,
            });
        }
    }

    // Clear Flatpickr values
    function clearFlatpickrValues() {
        if (flatpickrDateInstance) {
            flatpickrDateInstance.clear();
        }
        if (flatpickrStartTimeInstance) {
            flatpickrStartTimeInstance.clear();
        }
        if (flatpickrEndTimeInstance) {
            flatpickrEndTimeInstance.clear();
        }
    }

    // Load form data for attendance creation
    async function loadAttendanceFormData() {
        try {
            console.log('Loading form data...');
            const response = await fetch('/attendance/form-data', {
                headers: {
                    'Accept': 'application/json',
                },
            });

            console.log('Form data response status:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Form data error response:', errorText);
                throw new Error(`Failed to load form data: ${response.status}`);
            }

            const result = await response.json();
            console.log('Form data result:', result);

            if (result.success) {
                const data = result.data;
                console.log('Semesters:', data.semesters);
                console.log('Sections:', data.sections);

                // Populate semesters
                const semesterSelect = document.getElementById('attendanceSemester');
                if (semesterSelect) {
                    if (data.semesters && data.semesters.length > 0) {
                        semesterSelect.innerHTML = '<option value="" disabled selected>Select Semester</option>';
                        data.semesters.forEach(semester => {
                            const option = document.createElement('option');
                            option.value = semester.value;
                            option.textContent = semester.label;
                            semesterSelect.appendChild(option);
                        });
                        console.log('✓ Semesters populated:', data.semesters.length);
                    } else {
                        semesterSelect.innerHTML = '<option value="" disabled selected>No active semesters available</option>';
                        console.warn('⚠ No semesters found');
                    }
                } else {
                    console.error('❌ Semester select element not found');
                }

                // Populate sections using vanilla select component
                const vanillaSectionSelect = window['vanillaSelect_attendanceSection'];
                if (vanillaSectionSelect) {
                    if (data.sections && data.sections.length > 0) {
                        vanillaSectionSelect.setOptions(data.sections);
                        console.log('✓ Sections populated:', data.sections.length);
                        
                        // Store sections globally for "Add All" functionality
                        window.attendanceSections = data.sections;
                    } else {
                        vanillaSectionSelect.setOptions([{ value: '', label: 'No active sections available' }]);
                        console.warn('⚠ No sections found');
                        window.attendanceSections = [];
                    }
                } else {
                    console.error('❌ Vanilla select component not found. Waiting for component initialization...');
                    // Retry after a short delay in case component hasn't initialized yet
                    setTimeout(() => {
                        const retrySelect = window['vanillaSelect_attendanceSection'];
                        if (retrySelect && data.sections) {
                            if (data.sections.length > 0) {
                                retrySelect.setOptions(data.sections);
                                console.log('✓ Sections populated (retry):', data.sections.length);
                                window.attendanceSections = data.sections;
                            } else {
                                retrySelect.setOptions([{ value: '', label: 'No active sections available' }]);
                                window.attendanceSections = [];
                            }
                        }
                    }, 500);
                }
            } else {
                console.error('Form data request not successful:', result);
                showToast('Error', result.message || 'Failed to load form data', 'error');
            }
        } catch (error) {
            console.error('Error loading form data:', error);
            showToast('Error', 'Failed to load form data: ' + error.message, 'error');
        }
    }

    // Display validation errors on form fields
    function displayFormErrors(errors) {
        // Clear all previous errors first
        clearFormErrors();
        
        // Iterate through errors and display them
        Object.keys(errors).forEach(field => {
            const errorElement = document.getElementById(`error-${field}`);
            const inputElement = document.querySelector(`[name="${field}"]`);
            
            if (errorElement) {
                // Set error message
                const errorMessages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                errorElement.textContent = errorMessages[0]; // Show first error message
                errorElement.style.display = 'block';
                
                // Add invalid class to input
                if (inputElement) {
                    inputElement.classList.add('is-invalid');
                    
                    // For vanilla select component, we need to handle it differently
                    if (field === 'section_ids' && window['vanillaSelect_attendanceSection']) {
                        const vanillaSelectContainer = document.querySelector('#attendanceSection').closest('.vanilla-select-wrapper') || 
                                                       document.querySelector('#attendanceSection').parentElement;
                        if (vanillaSelectContainer) {
                            vanillaSelectContainer.classList.add('is-invalid');
                        }
                    }
                }
            }
        });
    }

    // Clear all form errors
    function clearFormErrors() {
        // Clear all error messages
        document.querySelectorAll('#createAttendanceForm .invalid-feedback').forEach(errorEl => {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        });
        
        // Remove invalid class from all inputs in the form
        const form = document.getElementById('createAttendanceForm');
        if (form) {
            form.querySelectorAll('.is-invalid').forEach(inputEl => {
                inputEl.classList.remove('is-invalid');
            });
        }
        
        // Clear vanilla select error state
        const vanillaSelectContainer = document.querySelector('#attendanceSection')?.closest('.vanilla-select-wrapper') || 
                                       document.querySelector('#attendanceSection')?.parentElement;
        if (vanillaSelectContainer) {
            vanillaSelectContainer.classList.remove('is-invalid');
        }
    }

    // Handle attendance form submission
    const createAttendanceForm = document.getElementById('createAttendanceForm');
    if (createAttendanceForm) {
        createAttendanceForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitAttendanceBtn');
            const buttonText = submitBtn.querySelector('.button-text');
            const buttonSpinner = submitBtn.querySelector('.button-spinner');

            // Show loading state
            setButtonLoading(submitBtn, buttonText, buttonSpinner, true);

            try {
                const token = getCsrfToken();
                if (!token) {
                    showToast('Error', 'CSRF token not found. Please refresh the page.', 'error');
                    setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
                    return;
                }

                const formData = new FormData(this);
                
                // Get date and time values from inputs (flatpickr sets these)
                const dateValue = document.getElementById('attendanceDate').value;
                const startTimeValue = document.getElementById('attendanceStartTime').value;
                const endTimeValue = document.getElementById('attendanceEndTime').value;
                
                // Convert date from m/d/Y to Y-m-d format for API
                let apiDateValue = dateValue;
                if (dateValue) {
                    // Parse m/d/Y format and convert to Y-m-d
                    const dateParts = dateValue.split('/');
                    if (dateParts.length === 3) {
                        const month = dateParts[0].padStart(2, '0');
                        const day = dateParts[1].padStart(2, '0');
                        const year = dateParts[2];
                        apiDateValue = `${year}-${month}-${day}`;
                    }
                }
                
                // Convert 12-hour time format to 24-hour format for API
                function convertTo24Hour(time12h) {
                    if (!time12h) return null;
                    
                    // Parse format: "h:mm AM" or "h:mm PM"
                    const timeParts = time12h.split(' ');
                    if (timeParts.length !== 2) return time12h; // Return as-is if format is unexpected
                    
                    const time = timeParts[0];
                    const ampm = timeParts[1].toUpperCase();
                    const [hours, minutes] = time.split(':');
                    
                    let hour24 = parseInt(hours, 10);
                    if (ampm === 'PM' && hour24 !== 12) {
                        hour24 += 12;
                    } else if (ampm === 'AM' && hour24 === 12) {
                        hour24 = 0;
                    }
                    
                    return `${hour24.toString().padStart(2, '0')}:${minutes}`;
                }
                
                const startTime24h = convertTo24Hour(startTimeValue);
                const endTime24h = convertTo24Hour(endTimeValue);
                
                // Get section_ids from vanilla select component (supports multiple)
                let sectionIds = [];
                const vanillaSectionSelect = window['vanillaSelect_attendanceSection'];
                if (vanillaSectionSelect) {
                    const selectedValues = vanillaSectionSelect.getValue();
                    sectionIds = Array.isArray(selectedValues) ? selectedValues : (selectedValues ? [selectedValues] : []);
                    // Filter out empty values and convert to integers
                    sectionIds = sectionIds
                        .filter(id => id && id !== '' && id !== null)
                        .map(id => parseInt(id, 10))
                        .filter(id => !isNaN(id) && id > 0);
                }
                
                // Check if "Add All Sections" is enabled
                const addAllSectionsCheckbox = document.getElementById('addAllSections');
                const addAllSections = addAllSectionsCheckbox ? addAllSectionsCheckbox.checked : false;
                
                console.log('Section IDs before submission:', sectionIds);
                console.log('Add All Sections:', addAllSections);
                
                // Validate sections before submission
                if (!addAllSections && sectionIds.length === 0) {
                    showToast('Validation Error', 'Please select at least one section or enable "Add All Sections"', 'error');
                    setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
                    return;
                }
                
                // Validate that at least one of start_time, end_time, or location is provided
                const locationValue = formData.get('location')?.trim() || '';
                if (!startTime24h && !endTime24h && !locationValue) {
                    displayFormErrors({
                        'start_time': 'At least one of Start Time, End Time, or Location must be provided.',
                        'end_time': 'At least one of Start Time, End Time, or Location must be provided.',
                        'location': 'At least one of Start Time, End Time, or Location must be provided.'
                    });
                    showToast('Validation Error', 'At least one of Start Time, End Time, or Location must be provided.', 'error');
                    setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
                    return;
                }
                
                // Prepare data object
                const data = {
                    title: formData.get('title'),
                    description: formData.get('description'),
                    category_id: selectedCategoryId ? parseInt(selectedCategoryId, 10) : null, // Use the category from the button clicked
                    attendance_type: formData.get('attendance_type') || null,
                    semester_id: formData.get('semester_id') ? parseInt(formData.get('semester_id'), 10) : null,
                    section_ids: sectionIds,
                    add_all_sections: addAllSections,
                    date: apiDateValue,
                    start_time: startTime24h ? `${apiDateValue} ${startTime24h}:00` : null,
                    end_time: endTime24h ? `${apiDateValue} ${endTime24h}:00` : null,
                    location: formData.get('location') || null,
                    is_active: document.getElementById('attendanceIsActive').checked
                };

                // Determine if we're creating or updating
                const isEditMode = currentAttendanceId !== null;
                const url = isEditMode ? `/attendance/${currentAttendanceId}` : '/attendance';
                
                // Laravel requires _method override for PUT/PATCH requests
                if (isEditMode) {
                    data._method = 'PUT';
                }

                const response = await fetch(url, {
                    method: isEditMode ? 'POST' : 'POST', // Use POST with _method override for PUT
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data),
                });

                let result;
                try {
                    result = await response.json();
                } catch (parseError) {
                    console.error('Error parsing response:', parseError);
                    const errorText = await response.text();
                    console.error('Response text:', errorText);
                    showToast('Error', 'Server error occurred. Please check the console for details.', 'error');
                    setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
                    return;
                }

                if (response.ok && result.success) {
                    const attendanceId = result.data?.id || currentAttendanceId;
                    
                    // After creating attendance (not updating), create student attendances
                    if (!isEditMode && attendanceId) {
                        try {
                            const studentResponse = await fetch(`/attendance/${attendanceId}/students`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json',
                                },
                            });

                            // Check if response is JSON
                            const contentType = studentResponse.headers.get('content-type');
                            let studentResult;
                            
                            if (contentType && contentType.includes('application/json')) {
                                studentResult = await studentResponse.json();
                                
                                if (studentResponse.ok && studentResult.success) {
                                    showToast('Success', result.message + ' ' + studentResult.message, 'success');
                                } else {
                                    showToast('Success', result.message || 'Attendance created successfully', 'success');
                                    if (studentResult.message) {
                                        showToast('Info', studentResult.message, 'info');
                                    }
                                }
                            } else {
                                // Response is not JSON (likely a dd() output or HTML error page)
                                const textResponse = await studentResponse.text();
                                console.error('Non-JSON response from student attendance endpoint:', textResponse);
                                console.error('Response status:', studentResponse.status);
                                showToast('Success', result.message || 'Attendance created successfully', 'success');
                                showToast('Warning', 'Student attendance endpoint returned non-JSON response. Check console for details.', 'warning');
                            }
                        } catch (studentError) {
                            console.error('Error creating student attendances:', studentError);
                            console.error('Error details:', {
                                message: studentError.message,
                                stack: studentError.stack
                            });
                            showToast('Success', result.message || 'Attendance created successfully', 'success');
                            showToast('Warning', 'Attendance created but failed to add students: ' + studentError.message, 'warning');
                        }
                    } else {
                        showToast('Success', result.message || (isEditMode ? 'Attendance updated successfully' : 'Attendance created successfully'), 'success');
                    }
                    
                    // Close modal
                    const modalElement = document.getElementById('createAttendanceModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }

                    // Reset form and clear flatpickr
                    resetAttendanceForm();
                    currentAttendanceId = null;
                    selectedCategoryId = null;

                    // Reload categories to show updated attendance
                    loadCategories();
                } else {
                    // Handle validation errors
                    if (result.errors) {
                        // Display form field errors
                        displayFormErrors(result.errors);
                        // Also show toast with error messages
                        const errorMessages = Object.values(result.errors).flat().join('<br>');
                        showToast('Validation Error', errorMessages, 'error');
                    } else {
                        showToast('Error', result.message || 'Failed to create attendance', 'error');
                    }
                }
            } catch (error) {
                console.error('Error creating attendance:', error);
                showToast('Error', 'An error occurred while creating the attendance', 'error');
            } finally {
                // Hide loading state
                setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
            }
        });
    }

    // Handle "Add All Sections" toggle
    const addAllSectionsToggle = document.getElementById('addAllSections');
    if (addAllSectionsToggle) {
        addAllSectionsToggle.addEventListener('change', function() {
            const vanillaSectionSelect = window['vanillaSelect_attendanceSection'];
            const isChecked = this.checked;
            
            if (vanillaSectionSelect && window.attendanceSections) {
                if (isChecked) {
                    // Select all sections
                    const allSectionIds = window.attendanceSections
                        .filter(section => section.value !== '')
                        .map(section => section.value);
                    
                    if (allSectionIds.length > 0) {
                        vanillaSectionSelect.setValue(allSectionIds);
                        vanillaSectionSelect.disable();
                        showToast('Info', `All ${allSectionIds.length} sections selected`, 'info');
                    }
                } else {
                    // Clear selections and enable dropdown
                    vanillaSectionSelect.setValue([]);
                    vanillaSectionSelect.enable();
                }
            }
        });
    }

    // Handle modal close event to clear form and flatpickr
    const createAttendanceModal = document.getElementById('createAttendanceModal');
    if (createAttendanceModal) {
        createAttendanceModal.addEventListener('hidden.bs.modal', function () {
            resetAttendanceForm();
            currentAttendanceId = null;
            selectedCategoryId = null;
            updateModalForCreate(); // Reset to create mode
        });
    }

    // Store category ID for deletion
    let categoryToDelete = null;

    // Delete category - shows confirmation modal
    window.deleteCategory = function (categoryId) {
        categoryToDelete = categoryId;
        const modal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
        modal.show();
    };

    // Handle delete confirmation
    const confirmDeleteBtn = document.getElementById('confirm-delete-category');
    if (confirmDeleteBtn) {
        const buttonText = confirmDeleteBtn.querySelector('.button-text');
        const buttonSpinner = confirmDeleteBtn.querySelector('.button-spinner');

        confirmDeleteBtn.addEventListener('click', async function() {
            if (!categoryToDelete) return;

            // Show loading state
            setButtonLoading(confirmDeleteBtn, buttonText, buttonSpinner, true);

            try {
                const token = getCsrfToken();
                if (!token) {
                    showToast('Error', 'CSRF token not found. Please refresh the page.', 'error');
                    setButtonLoading(confirmDeleteBtn, buttonText, buttonSpinner, false);
                    return;
                }

                const response = await fetch(API.DELETE(categoryToDelete), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                });

                // Check if response is OK before parsing JSON
                if (!response.ok) {
                    // Handle HTTP errors
                    let errorMessage = 'Failed to delete category';
                    try {
                        const result = await response.json();
                        errorMessage = result.message || errorMessage;
                    } catch (parseError) {
                        errorMessage = `HTTP ${response.status}: ${response.statusText}`;
                    }
                    
                    showToast('Error', errorMessage, 'error');
                    setButtonLoading(confirmDeleteBtn, buttonText, buttonSpinner, false);
                    return;
                }

                const result = await response.json();

                // Close modal
                const modalElement = document.getElementById('deleteCategoryModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }

                if (result.success) {
                    showToast('Success', result.message || 'Category deleted successfully', 'success');
                    loadCategories();
                } else {
                    showToast('Error', result.message || 'Failed to delete category', 'error');
                }
            } catch (error) {
                console.error('Error deleting category:', error);
                showToast('Error', 'An error occurred while deleting the category', 'error');
            } finally {
                categoryToDelete = null;
                // Hide loading state
                setButtonLoading(confirmDeleteBtn, buttonText, buttonSpinner, false);
            }
        });
    }

    // Toggle category status
    window.toggleCategoryStatus = async function (categoryId, isActive) {
        const endpoint = isActive ? API.DEACTIVATE(categoryId) : API.ACTIVATE(categoryId);

        try {
            const token = getCsrfToken();
            if (!token) {
                showToast('Error', 'CSRF token not found. Please refresh the page.', 'error');
                return;
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();

            if (result.success) {
                showToast('Success', result.message, 'success');
                loadCategories();
            } else {
                showToast('Error', result.message || 'Failed to update category status', 'error');
            }
        } catch (error) {
            console.error('Error toggling category status:', error);
            showToast('Error', 'An error occurred while updating the category', 'error');
        }
    };

    // Edit category
    window.editCategory = async function (categoryId) {
        try {
            const response = await fetch(`${API.BASE}/${categoryId}`, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();

            if (result.success) {
                const category = result.data.category;

                // Populate form
                document.getElementById('editCategoryId').value = category.id;
                document.getElementById('editCategoryName').value = category.name;
                document.getElementById('editCategoryDescription').value = category.description || '';
                document.getElementById('editCategoryDisplayOrder').value = category.display_order || 0;
                document.getElementById('editCategoryIsActive').checked = category.is_active;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                modal.show();
            }
        } catch (error) {
            console.error('Error loading category:', error);
            showToast('Error', 'Failed to load category details', 'error');
        }
    };

    // Utility: Show toast notification using custom toast component
    function showToast(title, message, type = 'info') {
        // Dispatch custom event for toast notification component
        window.dispatchEvent(new CustomEvent('show-toast', {
            detail: {
                title: title,
                message: message,
                type: type,
                position: 'top-right',
                duration: 4000
            }
        }));
    }

    // Utility: Set button loading state
    function setButtonLoading(button, textElement, spinnerElement, isLoading) {
        if (isLoading) {
            button.disabled = true;
            textElement.classList.add('d-none');
            spinnerElement.classList.remove('d-none');
        } else {
            button.disabled = false;
            textElement.classList.remove('d-none');
            spinnerElement.classList.add('d-none');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function () {
        console.log('📋 Attendance Categories Module Initialized');
        console.log('Current path:', window.location.pathname);

        // Load categories if on attendance page
        if (window.location.pathname.includes('/attendance')) {
            console.log('✓ On attendance page, loading categories...');
            loadCategories();
        } else {
            console.log('ℹ️ Not on attendance page, skipping category load');
        }

        // Reset form when modal is closed
        const createModal = document.getElementById('createCategoryModal');
        if (createModal) {
            createModal.addEventListener('hidden.bs.modal', function () {
                categoryForm?.reset();
                // Reset button loading state if needed
                const submitBtn = document.getElementById('addNewCategory');
                if (submitBtn) {
                    const buttonText = submitBtn.querySelector('.button-text');
                    const buttonSpinner = submitBtn.querySelector('.button-spinner');
                    if (buttonText && buttonSpinner) {
                        setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
                    }
                }
            });
        }

        const editModal = document.getElementById('editCategoryModal');
        if (editModal) {
            editModal.addEventListener('hidden.bs.modal', function () {
                editCategoryForm?.reset();
                // Reset button loading state if needed
                const submitBtn = document.getElementById('updateCategory');
                if (submitBtn) {
                    const buttonText = submitBtn.querySelector('.button-text');
                    const buttonSpinner = submitBtn.querySelector('.button-spinner');
                    if (buttonText && buttonSpinner) {
                        setButtonLoading(submitBtn, buttonText, buttonSpinner, false);
                    }
                }
            });
        }
    });

    // Add event listeners to clear errors when user starts typing/selecting
    if (createAttendanceForm) {
        // Clear errors when user interacts with form fields
        createAttendanceForm.addEventListener('input', function(e) {
            const fieldName = e.target.name;
            if (fieldName) {
                const errorElement = document.getElementById(`error-${fieldName}`);
                if (errorElement && e.target.classList.contains('is-invalid')) {
                    e.target.classList.remove('is-invalid');
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                }
            }
        });
        
        createAttendanceForm.addEventListener('change', function(e) {
            const fieldName = e.target.name;
            if (fieldName) {
                const errorElement = document.getElementById(`error-${fieldName}`);
                if (errorElement && e.target.classList.contains('is-invalid')) {
                    e.target.classList.remove('is-invalid');
                    errorElement.textContent = '';
                    errorElement.style.display = 'none';
                }
            }
        });
    }

    // Expose loadCategories for manual refresh
    window.reloadAttendanceCategories = loadCategories;
    // Attendance action functions
    window.viewAttendance = function(attendanceId) {
        // TODO: Implement view attendance modal or page
        showToast('Info', 'View attendance functionality coming soon', 'info');
        console.log('View attendance:', attendanceId);
    };

    // Store all students data for search functionality
    let allStudentsData = [];
    let allStudentsStats = {};
    
    // Store selected student attendance IDs
    let selectedStudentAttendances = new Set();
    
    // Pagination state
    let currentStudentsPage = 1;
    const studentsPerPage = 10;
    let currentStudentsData = []; // Currently displayed students (after search/filter)

    // View students for an attendance session
    window.viewStudents = async function(attendanceId) {
        console.log('View students for attendance:', attendanceId);
        
        // Store current attendance ID for reloading after approve/disapprove
        window.currentViewingAttendanceId = attendanceId;
        
        // Show modal
        const viewStudentsModal = document.getElementById('viewStudentsModal');
        if (!viewStudentsModal) {
            console.error('View students modal not found');
            return;
        }
        const modal = new bootstrap.Modal(viewStudentsModal);
        modal.show();
        
        // Reset states with null checks
        const studentsLoading = document.getElementById('studentsLoading');
        const studentsContent = document.getElementById('studentsContent');
        const studentsError = document.getElementById('studentsError');
        const studentsTableBody = document.getElementById('studentsTableBody');
        const studentsStats = document.getElementById('studentsStats');
        
        if (studentsLoading) studentsLoading.classList.remove('d-none');
        if (studentsContent) studentsContent.classList.add('d-none');
        if (studentsError) studentsError.classList.add('d-none');
        if (studentsTableBody) studentsTableBody.innerHTML = '';
        if (studentsStats) studentsStats.innerHTML = '';
        const searchInput = document.getElementById('studentsSearchInput');
        const clearSearchBtn = document.getElementById('clearStudentsSearch');
        const searchResults = document.getElementById('studentsSearchResults');
        const noSearchResults = document.getElementById('studentsNoSearchResults');
        const selectAllCheckbox = document.getElementById('selectAllStudents');
        const bulkActionsContainer = document.getElementById('bulkActionsContainer');
        
        if (searchInput) searchInput.value = '';
        if (clearSearchBtn) clearSearchBtn.style.display = 'none';
        if (searchResults) searchResults.textContent = '';
        if (noSearchResults) noSearchResults.classList.add('d-none');
        
        // Reset bulk actions
        selectedStudentAttendances.clear();
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        if (bulkActionsContainer) bulkActionsContainer.style.display = 'none';
        updateBulkActionButtons();
        
        // Reset pagination
        currentStudentsPage = 1;
        currentStudentsData = [];
        
        try {
            const token = getCsrfToken();
            const response = await fetch(`/attendance/${attendanceId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to load attendance data');
            }

            const result = await response.json();
            
            if (result.success && result.data && result.data.attendance) {
                const attendance = result.data.attendance;
                // Handle both snake_case and camelCase
                const students = attendance.student_attendances || attendance.studentAttendances || [];
                const stats = result.data.stats || {};
                
                // Store students data globally for search
                allStudentsData = students;
                allStudentsStats = stats;
                
                // Hide loading
                document.getElementById('studentsLoading').classList.add('d-none');
                
                if (students.length === 0) {
                    // Show empty state
                    document.getElementById('studentsEmpty').classList.remove('d-none');
                    document.getElementById('studentsContent').classList.remove('d-none');
                } else {
                    // Hide empty state
                    document.getElementById('studentsEmpty').classList.add('d-none');
                    
                    // Render stats
                    renderStudentsStats(stats, students.length);
                    
                    // Set current students data and reset pagination
                    currentStudentsData = students;
                    currentStudentsPage = 1;
                    
                    // Render students table with pagination
                    renderStudentsTableWithPagination();
                    
                    // Show content
                    document.getElementById('studentsContent').classList.remove('d-none');
                    
                    // Initialize search functionality
                    initializeStudentsSearch();
                    
                    // Initialize bulk actions
                    initializeBulkActions();
                }
            } else {
                throw new Error('Invalid attendance data');
            }
        } catch (error) {
            console.error('Error loading students:', error);
            
            // Hide loading
            document.getElementById('studentsLoading').classList.add('d-none');
            
            // Show error
            document.getElementById('studentsError').classList.remove('d-none');
            document.getElementById('studentsErrorMessage').textContent = 'Failed to load students: ' + error.message;
        }
    };

    // Initialize search functionality
    function initializeStudentsSearch() {
        const searchInput = document.getElementById('studentsSearchInput');
        const clearBtn = document.getElementById('clearStudentsSearch');
        const searchResults = document.getElementById('studentsSearchResults');
        
        if (!searchInput) return;
        
        // Search on input
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim().toLowerCase();
            
            if (query.length > 0) {
                clearBtn.style.display = 'block';
                filterStudents(query);
            } else {
                clearBtn.style.display = 'none';
                // Show all students
                currentStudentsData = allStudentsData;
                currentStudentsPage = 1;
                renderStudentsTableWithPagination();
                searchResults.textContent = '';
                document.getElementById('studentsNoSearchResults').classList.add('d-none');
                
                // Update select all checkbox
                updateSelectAllCheckbox();
            }
        });
        
        // Clear search
        clearBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            const tableContainer = document.querySelector('#studentsContent .table-responsive');
            if (tableContainer) {
                tableContainer.style.display = 'block';
            }
            currentStudentsData = allStudentsData;
            currentStudentsPage = 1;
            renderStudentsTableWithPagination();
            searchResults.textContent = '';
            document.getElementById('studentsNoSearchResults').classList.add('d-none');
        });
    }

    // Filter students based on search query
    function filterStudents(query) {
        const filtered = allStudentsData.filter(studentAttendance => {
            const student = studentAttendance.user || {};
            const studentInfo = student.student_info || student.studentInfo || {};
            const studentName = student.name || 
                               `${(student.first_name || student.firstName || '')} ${(student.last_name || student.lastName || '')}`.trim() || 
                               '';
            const studentId = studentInfo.student_id || 
                             studentInfo.studentId || 
                             studentInfo.id_number || 
                             studentInfo.idNumber ||
                             studentInfo.student_number ||
                             studentInfo.studentNumber ||
                             student.id ||
                             '';
            const status = (studentAttendance.status || '').toLowerCase();
            const remarks = (studentAttendance.remarks || studentAttendance.notes || '').toLowerCase();
            
            // Search in name, ID, status, and remarks
            return studentName.toLowerCase().includes(query) ||
                   studentId.toLowerCase().includes(query) ||
                   status.includes(query) ||
                   remarks.includes(query);
        });
        
        // Update search results count
        const searchResults = document.getElementById('studentsSearchResults');
        const tableContainer = document.querySelector('#studentsContent .table-responsive');
        const noSearchResults = document.getElementById('studentsNoSearchResults');
        
        if (filtered.length === 0) {
            searchResults.textContent = 'No results found';
            searchResults.className = 'text-danger';
            if (tableContainer) {
                tableContainer.style.display = 'none';
            }
            if (noSearchResults) {
                noSearchResults.classList.remove('d-none');
            }
        } else {
            searchResults.textContent = `Found ${filtered.length} of ${allStudentsData.length} student(s)`;
            searchResults.className = 'text-muted';
            if (tableContainer) {
                tableContainer.style.display = 'block';
            }
            if (noSearchResults) {
                noSearchResults.classList.add('d-none');
            }
        }
        
        // Update current students data and reset to page 1
        currentStudentsData = filtered;
        currentStudentsPage = 1;
        
        // Render filtered results with pagination
        renderStudentsTableWithPagination();
        
        // Update select all checkbox after filtering
        updateSelectAllCheckbox();
    }

    // Render students statistics
    function renderStudentsStats(stats, totalStudents) {
        const statsHtml = `
            <div class="col-md-3">
                <div class="card border border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-primary-subtle rounded">
                                    <div class="avatar-title bg-primary-subtle text-primary rounded fs-20">
                                        <i class="ri-group-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-0">Total Students</p>
                                <h5 class="mb-0">${totalStudents}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-success-subtle rounded">
                                    <div class="avatar-title bg-success-subtle text-success rounded fs-20">
                                        <i class="ri-checkbox-circle-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-0">Present</p>
                                <h5 class="mb-0 text-success">${stats.present_count || 0}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border border-danger">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-danger-subtle rounded">
                                    <div class="avatar-title bg-danger-subtle text-danger rounded fs-20">
                                        <i class="ri-close-circle-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-0">Absent</p>
                                <h5 class="mb-0 text-danger">${stats.absent_count || 0}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm bg-warning-subtle rounded">
                                    <div class="avatar-title bg-warning-subtle text-warning rounded fs-20">
                                        <i class="ri-time-line"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="text-muted mb-0">Late/Other</p>
                                <h5 class="mb-0 text-warning">${(totalStudents - (stats.present_count || 0) - (stats.absent_count || 0))}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('studentsStats').innerHTML = statsHtml;
    }

    // Render students table with pagination
    function renderStudentsTableWithPagination() {
        if (!currentStudentsData || currentStudentsData.length === 0) {
            const tbody = document.getElementById('studentsTableBody');
            if (tbody) tbody.innerHTML = '';
            renderStudentsPagination(0, 0);
            return;
        }

        // Calculate pagination
        const totalPages = Math.ceil(currentStudentsData.length / studentsPerPage);
        const startIndex = (currentStudentsPage - 1) * studentsPerPage;
        const endIndex = Math.min(startIndex + studentsPerPage, currentStudentsData.length);
        const paginatedStudents = currentStudentsData.slice(startIndex, endIndex);

        // Render table with paginated data
        renderStudentsTable(paginatedStudents, startIndex);

        // Render pagination controls
        renderStudentsPagination(currentStudentsData.length, totalPages);
    }

    // Render students table
    function renderStudentsTable(students, startIndex = 0) {
        const tbody = document.getElementById('studentsTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';
        
        students.forEach((studentAttendance, index) => {
            const rowIndex = startIndex + index + 1;
            // Get student attendance ID
            const studentAttendanceId = studentAttendance.id;
            
            const student = studentAttendance.user || {};
            // Handle both snake_case and camelCase
            const studentInfo = student.student_info || student.studentInfo || {};
            const studentName = student.name || 
                               `${(student.first_name || student.firstName || '')} ${(student.last_name || student.lastName || '')}`.trim() || 
                               'N/A';
            // Try multiple possible field names for student ID
            const studentId = studentInfo.student_id || 
                             studentInfo.studentId || 
                             studentInfo.id_number || 
                             studentInfo.idNumber ||
                             studentInfo.student_number ||
                             studentInfo.studentNumber ||
                             student.id ||
                             'N/A';
            
            // Format status badge
            const studentStatus = studentAttendance.status || 'absent';
            let statusBadge = '';
            switch(studentStatus.toLowerCase()) {
                case 'present':
                    statusBadge = '<span class="badge bg-success">Present</span>';
                    break;
                case 'absent':
                    statusBadge = '<span class="badge bg-danger">Absent</span>';
                    break;
                case 'late':
                    statusBadge = '<span class="badge bg-warning">Late</span>';
                    break;
                case 'excused':
                    statusBadge = '<span class="badge bg-info">Excused</span>';
                    break;
                case 'partial':
                    statusBadge = '<span class="badge bg-secondary">Partial</span>';
                    break;
                case 'leave':
                    statusBadge = '<span class="badge bg-primary">Leave</span>';
                    break;
                default:
                    statusBadge = `<span class="badge bg-secondary">${escapeHtml(studentStatus)}</span>`;
            }
            
            // Format check in time
            let checkInTime = '-';
            if (studentAttendance.check_in_time) {
                const checkIn = new Date(studentAttendance.check_in_time);
                checkInTime = checkIn.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }
            
            // Format check out time
            let checkOutTime = '-';
            if (studentAttendance.check_out_time) {
                const checkOut = new Date(studentAttendance.check_out_time);
                checkOutTime = checkOut.toLocaleString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }
            
            // Format duration
            let duration = '-';
            if (studentAttendance.duration_minutes) {
                const hours = Math.floor(studentAttendance.duration_minutes / 60);
                const minutes = studentAttendance.duration_minutes % 60;
                if (hours > 0) {
                    duration = `${hours}h ${minutes}m`;
                } else {
                    duration = `${minutes}m`;
                }
            }
            
            // Format remarks
            const remarks = studentAttendance.remarks || studentAttendance.notes || '-';
            
            // Get user image
            const photoPath = student.photo_path || student.avatar || '';
            let userImageUrl = '';
            if (photoPath) {
                // If it's an external URL (starts with http), use as-is
                if (photoPath.startsWith('http://') || photoPath.startsWith('https://')) {
                    userImageUrl = photoPath;
                } else {
                    // Otherwise, prepend storage/ and use asset path
                    userImageUrl = `/storage/${photoPath}`;
                }
            } else {
                // Use default image
                userImageUrl = '/build/images/users/user-dummy-img.jpg';
            }
            
            // Create avatar HTML - use image if available, otherwise use initial
            let avatarHtml = '';
            if (photoPath) {
                avatarHtml = `<img src="${escapeHtml(userImageUrl)}" alt="${escapeHtml(studentName)}" class="avatar-xs rounded-circle" onerror="this.onerror=null; this.src='/build/images/users/user-dummy-img.jpg';" />`;
            } else {
                avatarHtml = `<div class="avatar-title rounded-circle bg-primary-subtle text-primary">
                    ${(studentName.charAt(0) || '?').toUpperCase()}
                </div>`;
            }
            
            // Check if this row should be selectable (pending and has check-in)
            const hasCheckIn = studentAttendance.check_in_time ? true : false;
            const isApproved = studentAttendance.approved_at ? true : false;
            const attendanceStatus = (studentAttendance.status || '').toLowerCase();
            const isPending = attendanceStatus === 'pending' || (!isApproved && hasCheckIn);
            const isSelectable = isPending && hasCheckIn;
            
            const row = `
                <tr data-student-attendance-id="${studentAttendanceId}">
                    <td>
                        ${isSelectable ? `
                            <div class="form-check">
                                <input class="form-check-input student-checkbox" type="checkbox" 
                                    value="${studentAttendanceId}" 
                                    id="studentCheckbox_${studentAttendanceId}"
                                    onchange="handleStudentCheckboxChange(${studentAttendanceId})"
                                    ${selectedStudentAttendances.has(studentAttendanceId) ? 'checked' : ''}>
                            </div>
                        ` : '<span class="text-muted">-</span>'}
                    </td>
                    <td>${rowIndex}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-xs">
                                    ${avatarHtml}
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <span class="fw-medium">${escapeHtml(studentName)}</span>
                            </div>
                        </div>
                    </td>
                    <td>${escapeHtml(studentId)}</td>
                    <td>${statusBadge}</td>
                    <td><small class="text-muted">${checkInTime}</small></td>
                    <td><small class="text-muted">${checkOutTime}</small></td>
                    <td><small class="text-muted">${duration}</small></td>
                    <td><small class="text-muted">${escapeHtml(remarks)}</small></td>
                    <td>
                        ${renderStudentAttendanceActions(studentAttendance)}
                    </td>
                </tr>
            `;
            
            tbody.innerHTML += row;
        });
    }

    // Render pagination controls
    function renderStudentsPagination(totalItems, totalPages = 0) {
        const paginationContainer = document.getElementById('studentsPaginationContainer');
        const paginationInfo = document.getElementById('studentsPaginationInfo');
        const pagination = document.getElementById('studentsPagination');
        
        if (!paginationContainer || !pagination || !paginationInfo) return;
        
        if (totalItems === 0 || totalPages === 0) {
            paginationContainer.style.display = 'none';
            paginationInfo.textContent = '0-0 of 0';
            pagination.innerHTML = '';
            return;
        }
        
        paginationContainer.style.display = 'flex';
        
        // Calculate display range
        const startIndex = (currentStudentsPage - 1) * studentsPerPage + 1;
        const endIndex = Math.min(currentStudentsPage * studentsPerPage, totalItems);
        paginationInfo.textContent = `${startIndex}-${endIndex} of ${totalItems}`;
        
        // Clear existing pagination
        pagination.innerHTML = '';
        
        if (totalPages <= 1) {
            // Show pagination info but no page controls if only one page
            return;
        }
        
        // Previous button
        const prevDisabled = currentStudentsPage === 1 ? 'disabled' : '';
        pagination.innerHTML += `
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="javascript:void(0);" onclick="changeStudentsPage(${currentStudentsPage - 1})" ${prevDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>
                    <i class="ri-arrow-left-s-line"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        let startPage = Math.max(1, currentStudentsPage - 2);
        let endPage = Math.min(totalPages, currentStudentsPage + 2);
        
        // Adjust if we're near the start
        if (currentStudentsPage <= 3) {
            endPage = Math.min(5, totalPages);
        }
        
        // Adjust if we're near the end
        if (currentStudentsPage >= totalPages - 2) {
            startPage = Math.max(1, totalPages - 4);
        }
        
        // First page
        if (startPage > 1) {
            pagination.innerHTML += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0);" onclick="changeStudentsPage(1)">1</a>
                </li>
            `;
            if (startPage > 2) {
                pagination.innerHTML += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
        }
        
        // Page number buttons
        for (let i = startPage; i <= endPage; i++) {
            const active = i === currentStudentsPage ? 'active' : '';
            pagination.innerHTML += `
                <li class="page-item ${active}">
                    <a class="page-link" href="javascript:void(0);" onclick="changeStudentsPage(${i})">${i}</a>
                </li>
            `;
        }
        
        // Last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                pagination.innerHTML += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
            pagination.innerHTML += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0);" onclick="changeStudentsPage(${totalPages})">${totalPages}</a>
                </li>
            `;
        }
        
        // Next button
        const nextDisabled = currentStudentsPage === totalPages ? 'disabled' : '';
        pagination.innerHTML += `
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="javascript:void(0);" onclick="changeStudentsPage(${currentStudentsPage + 1})" ${nextDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>
                    <i class="ri-arrow-right-s-line"></i>
                </a>
            </li>
        `;
    }

    // Change students page
    window.changeStudentsPage = function(page) {
        const totalPages = Math.ceil(currentStudentsData.length / studentsPerPage);
        
        if (page < 1 || page > totalPages) {
            return;
        }
        
        currentStudentsPage = page;
        renderStudentsTableWithPagination();
        
        // Update select all checkbox
        updateSelectAllCheckbox();
        
        // Scroll to top of table
        const tableContainer = document.querySelector('#studentsContent .table-responsive');
        if (tableContainer) {
            tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // Render action buttons for student attendance
    function renderStudentAttendanceActions(studentAttendance) {
        const studentAttendanceId = studentAttendance.id;
        const hasCheckIn = studentAttendance.check_in_time ? true : false;
        const isApproved = studentAttendance.approved_at ? true : false;
        const actionStatus = (studentAttendance.status || '').toLowerCase();
        
        // Show approve/disapprove buttons if: status is "pending" AND has check-in time
        // OR if not approved and has check-in time (pending approval)
        const isPending = actionStatus === 'pending' || (!isApproved && hasCheckIn);
        
        // Show buttons only if pending and has check-in time
        if (isPending && hasCheckIn) {
            return `
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-success" onclick="approveStudentAttendance(${studentAttendanceId})" title="Approve">
                        <i class="ri-check-line"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="disapproveStudentAttendance(${studentAttendanceId})" title="Disapprove">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            `;
        }
        
        // Show disapprove button if approved
        if (isApproved) {
            return `
                <div class="d-flex gap-1 align-items-center">
                    <span class="badge bg-success">Approved</span>
                    <button class="btn btn-sm btn-danger" onclick="disapproveStudentAttendance(${studentAttendanceId})" title="Disapprove">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            `;
        }
        
        return '<span class="text-muted">-</span>';
    }

    // Approve student attendance
    window.approveStudentAttendance = function(studentAttendanceId) {
        Swal.fire({
            title: 'Approve Attendance?',
            text: 'Are you sure you want to approve this student attendance?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ab39c',
            cancelButtonColor: '#f06548',
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const token = getCsrfToken();
                    const response = await fetch(`/attendance/students/${studentAttendanceId}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        Swal.fire({
                            title: 'Approved!',
                            text: result.message || 'Student attendance has been approved.',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        });
                        
                        // Reload students data
                        // Get current attendance ID from modal or store it
                        const currentAttendanceId = window.currentViewingAttendanceId;
                        if (currentAttendanceId) {
                            // Reload the students list
                            await viewStudents(currentAttendanceId);
                        }
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'Failed to approve student attendance.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                } catch (error) {
                    console.error('Error approving student attendance:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while approving the attendance.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
            }
        });
    };

    // Disapprove student attendance
    window.disapproveStudentAttendance = function(studentAttendanceId) {
        Swal.fire({
            title: 'Disapprove Attendance?',
            text: 'Are you sure you want to disapprove this student attendance?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Disapprove',
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const token = getCsrfToken();
                    const response = await fetch(`/attendance/students/${studentAttendanceId}/disapprove`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        Swal.fire({
                            title: 'Disapproved!',
                            text: result.message || 'Student attendance has been disapproved.',
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        });
                        
                        // Reload students data
                        const currentAttendanceId = window.currentViewingAttendanceId;
                        if (currentAttendanceId) {
                            // Reload the students list
                            await viewStudents(currentAttendanceId);
                        }
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.message || 'Failed to disapprove student attendance.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                } catch (error) {
                    console.error('Error disapproving student attendance:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while disapproving the attendance.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
            }
        });
    };

    // Initialize bulk actions functionality
    function initializeBulkActions() {
        // Select all checkbox - only selects items on current page
        const selectAllCheckbox = document.getElementById('selectAllStudents');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                const checkboxes = document.querySelectorAll('.student-checkbox');
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = isChecked;
                    const studentId = parseInt(checkbox.value, 10);
                    if (isChecked) {
                        selectedStudentAttendances.add(studentId);
                    } else {
                        selectedStudentAttendances.delete(studentId);
                    }
                });
                
                updateBulkActionButtons();
                updateSelectAllCheckbox();
            });
        }
    }

    // Handle individual checkbox change
    window.handleStudentCheckboxChange = function(studentAttendanceId) {
        const checkbox = document.getElementById(`studentCheckbox_${studentAttendanceId}`);
        
        if (checkbox && checkbox.checked) {
            selectedStudentAttendances.add(studentAttendanceId);
        } else {
            selectedStudentAttendances.delete(studentAttendanceId);
        }
        
        // Update select all checkbox state
        updateSelectAllCheckbox();
        updateBulkActionButtons();
    };

    // Update select all checkbox state
    function updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('selectAllStudents');
        const checkboxes = document.querySelectorAll('.student-checkbox:not(:disabled)');
        const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        
        if (selectAllCheckbox && checkboxes.length > 0) {
            // Check if all visible checkboxes are checked
            const allVisibleChecked = Array.from(checkboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allVisibleChecked && checkboxes.length > 0;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        } else if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    // Update bulk action buttons state
    function updateBulkActionButtons() {
        const selectedCount = selectedStudentAttendances.size;
        const bulkActionsContainer = document.getElementById('bulkActionsContainer');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkApproveBtn = document.getElementById('bulkApproveBtn');
        const bulkDisapproveBtn = document.getElementById('bulkDisapproveBtn');
        
        if (selectedCountSpan) {
            selectedCountSpan.textContent = `${selectedCount} selected`;
        }
        
        if (bulkActionsContainer) {
            if (selectedCount > 0) {
                bulkActionsContainer.style.display = 'flex';
            } else {
                bulkActionsContainer.style.display = 'none';
            }
        }
        
        // Check if selected items are eligible for approve/disapprove
        const eligibleForApprove = getEligibleForApprove();
        const eligibleForDisapprove = getEligibleForDisapprove();
        
        if (bulkApproveBtn) {
            bulkApproveBtn.disabled = eligibleForApprove.length === 0;
        }
        
        if (bulkDisapproveBtn) {
            bulkDisapproveBtn.disabled = eligibleForDisapprove.length === 0;
        }
    }

    // Get selected items eligible for approve (pending and has check-in)
    function getEligibleForApprove() {
        const eligible = [];
        selectedStudentAttendances.forEach(id => {
            const studentAttendance = allStudentsData.find(sa => sa.id === id);
            if (studentAttendance) {
                const hasCheckIn = studentAttendance.check_in_time ? true : false;
                const isApproved = studentAttendance.approved_at ? true : false;
                const status = (studentAttendance.status || '').toLowerCase();
                const isPending = status === 'pending' || (!isApproved && hasCheckIn);
                
                if (isPending && hasCheckIn) {
                    eligible.push(id);
                }
            }
        });
        return eligible;
    }

    // Get selected items eligible for disapprove (approved)
    function getEligibleForDisapprove() {
        const eligible = [];
        selectedStudentAttendances.forEach(id => {
            const studentAttendance = allStudentsData.find(sa => sa.id === id);
            if (studentAttendance && studentAttendance.approved_at) {
                eligible.push(id);
            }
        });
        return eligible;
    }

    // Bulk approve students
    window.bulkApproveStudents = function() {
        const eligibleIds = getEligibleForApprove();
        
        if (eligibleIds.length === 0) {
            Swal.fire({
                title: 'No Eligible Items',
                text: 'No selected students are eligible for approval. They must have check-in time and be pending approval.',
                icon: 'warning',
                confirmButtonColor: '#0ab39c'
            });
            return;
        }
        
        Swal.fire({
            title: 'Approve Selected Attendances?',
            text: `Are you sure you want to approve ${eligibleIds.length} student attendance(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ab39c',
            cancelButtonColor: '#f06548',
            confirmButtonText: `Yes, Approve ${eligibleIds.length}`,
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const token = getCsrfToken();
                    const response = await fetch('/attendance/students/bulk-approve', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            ids: eligibleIds
                        }),
                    });

                    const responseData = await response.json();

                    if (response.ok && responseData.success) {
                        Swal.fire({
                            title: 'Approved!',
                            text: responseData.message || `Successfully approved ${eligibleIds.length} attendance(s).`,
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        });
                        
                        // Clear selection
                        selectedStudentAttendances.clear();
                        document.getElementById('selectAllStudents').checked = false;
                        
                        // Reload students data
                        const currentAttendanceId = window.currentViewingAttendanceId;
                        if (currentAttendanceId) {
                            await viewStudents(currentAttendanceId);
                        }
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: responseData.message || 'Failed to approve student attendances.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                } catch (error) {
                    console.error('Error bulk approving student attendances:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while approving the attendances.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
            }
        });
    };

    // Bulk disapprove students
    window.bulkDisapproveStudents = function() {
        const eligibleIds = getEligibleForDisapprove();
        
        if (eligibleIds.length === 0) {
            Swal.fire({
                title: 'No Eligible Items',
                text: 'No selected students are eligible for disapproval. They must be approved.',
                icon: 'warning',
                confirmButtonColor: '#0ab39c'
            });
            return;
        }
        
        Swal.fire({
            title: 'Disapprove Selected Attendances?',
            text: `Are you sure you want to disapprove ${eligibleIds.length} student attendance(s)?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, Disapprove ${eligibleIds.length}`,
            cancelButtonText: 'Cancel'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const token = getCsrfToken();
                    const response = await fetch('/attendance/students/bulk-disapprove', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            ids: eligibleIds
                        }),
                    });

                    const responseData = await response.json();

                    if (response.ok && responseData.success) {
                        Swal.fire({
                            title: 'Disapproved!',
                            text: responseData.message || `Successfully disapproved ${eligibleIds.length} attendance(s).`,
                            icon: 'success',
                            confirmButtonColor: '#0ab39c'
                        });
                        
                        // Clear selection
                        selectedStudentAttendances.clear();
                        document.getElementById('selectAllStudents').checked = false;
                        
                        // Reload students data
                        const currentAttendanceId = window.currentViewingAttendanceId;
                        if (currentAttendanceId) {
                            await viewStudents(currentAttendanceId);
                        }
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: responseData.message || 'Failed to disapprove student attendances.',
                            icon: 'error',
                            confirmButtonColor: '#f06548'
                        });
                    }
                } catch (error) {
                    console.error('Error bulk disapproving student attendances:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while disapproving the attendances.',
                        icon: 'error',
                        confirmButtonColor: '#f06548'
                    });
                }
            }
        });
    };

    window.editAttendance = async function(attendanceId) {
        console.log('Edit attendance:', attendanceId);
        currentAttendanceId = attendanceId;
        
        // Load form data first
        await loadAttendanceFormData();
        
        // Update modal for edit mode
        updateModalForEdit();
        
        // Show modal first
        const modal = new bootstrap.Modal(document.getElementById('createAttendanceModal'));
        modal.show();
        
        // Initialize flatpickr after modal is shown
        setTimeout(() => {
            initializeFlatpickr();
            
            // Load attendance data for editing after flatpickr is initialized
            setTimeout(() => {
                loadAttendanceForEdit(attendanceId);
            }, 50);
        }, 100);
    };

    // Store attendance ID for deletion
    let attendanceToDelete = null;

    window.deleteAttendance = function(attendanceId) {
        attendanceToDelete = attendanceId;
        const modal = new bootstrap.Modal(document.getElementById('deleteAttendanceModal'));
        modal.show();
    };

    // Handle delete attendance confirmation
    const confirmDeleteAttendanceBtn = document.getElementById('confirm-delete-attendance');
    if (confirmDeleteAttendanceBtn) {
        const buttonText = confirmDeleteAttendanceBtn.querySelector('.button-text');
        const buttonSpinner = confirmDeleteAttendanceBtn.querySelector('.button-spinner');

        confirmDeleteAttendanceBtn.addEventListener('click', async function() {
            if (!attendanceToDelete) return;

            // Show loading state
            setButtonLoading(confirmDeleteAttendanceBtn, buttonText, buttonSpinner, true);

            try {
                const token = getCsrfToken();
                if (!token) {
                    showToast('Error', 'CSRF token not found. Please refresh the page.', 'error');
                    setButtonLoading(confirmDeleteAttendanceBtn, buttonText, buttonSpinner, false);
                    return;
                }

                const response = await fetch(`/attendance/${attendanceToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                    },
                });

                const result = await response.json();

                if (result.success) {
                    showToast('Success', result.message || 'Attendance deleted successfully', 'success');
                    
                    // Close modal
                    const modalElement = document.getElementById('deleteAttendanceModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                    
                    loadCategories(); // Reload to update the display
                } else {
                    showToast('Error', result.message || 'Failed to delete attendance', 'error');
                }
            } catch (error) {
                console.error('Error deleting attendance:', error);
                showToast('Error', 'An error occurred while deleting the attendance', 'error');
            } finally {
                attendanceToDelete = null;
                // Hide loading state
                setButtonLoading(confirmDeleteAttendanceBtn, buttonText, buttonSpinner, false);
            }
        });
    }

    // Initialize modal accessibility handlers
    function initializeModalAccessibility() {
        // List of all modal IDs
        const modalIds = [
            'createAttendanceModal',
            'createCategoryModal',
            'editCategoryModal',
            'deleteCategoryModal',
            'deleteAttendanceModal',
            'viewStudentsModal'
        ];

        modalIds.forEach(modalId => {
            const modalElement = document.getElementById(modalId);
            if (modalElement) {
                // Ensure aria-hidden is properly set when modal is shown
                modalElement.addEventListener('shown.bs.modal', function() {
                    this.setAttribute('aria-hidden', 'false');
                    // Remove any focus from elements that might be hidden
                    const focusedElement = document.activeElement;
                    if (focusedElement && !this.contains(focusedElement)) {
                        // Focus should be on the modal, Bootstrap handles this
                        const firstFocusable = this.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
                        if (firstFocusable) {
                            firstFocusable.focus();
                        }
                    }
                });

                // Ensure aria-hidden is properly set when modal is hidden
                modalElement.addEventListener('hidden.bs.modal', function() {
                    this.setAttribute('aria-hidden', 'true');
                });

                // Handle show event to ensure proper state
                modalElement.addEventListener('show.bs.modal', function() {
                    this.removeAttribute('aria-hidden');
                });
            }
        });
    }

    // Initialize modal accessibility on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeModalAccessibility);
    } else {
        initializeModalAccessibility();
    }

    // Search functionality for categories and attendances
    function initializeSearch() {
        const searchInput = document.getElementById('search-task-options');
        if (!searchInput) return;

        // Debounce function to limit search frequency
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(this.value.trim());
            }, 300); // Wait 300ms after user stops typing
        });

        // Clear search on Escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                performSearch('');
            }
        });
    }

    // Perform search on categories and attendances (exposed globally for clear button)
    window.performSearch = function(query) {
        if (!allCategoriesData || allCategoriesData.length === 0) {
            return; // No data to search
        }

        const searchQuery = query.toLowerCase().trim();

        // If search is empty, show all categories
        if (!searchQuery) {
            displayCategories(allCategoriesData);
            return;
        }

        // Filter categories and attendances
        const filteredCategories = allCategoriesData.map(category => {
            // Check if category matches
            const categoryMatches = 
                category.name.toLowerCase().includes(searchQuery) ||
                (category.description && category.description.toLowerCase().includes(searchQuery));

            // Filter attendances within this category
            const filteredAttendances = (category.attendances || []).filter(attendance => {
                // Search in attendance fields
                const dateMatch = attendance.date ? 
                    new Date(attendance.date).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric', 
                        year: 'numeric' 
                    }).toLowerCase().includes(searchQuery) : false;

                const locationMatch = attendance.location ? 
                    attendance.location.toLowerCase().includes(searchQuery) : false;

                const typeMatch = attendance.type ? 
                    attendance.type.toLowerCase().includes(searchQuery) : false;

                const remarksMatch = attendance.remarks ? 
                    attendance.remarks.toLowerCase().includes(searchQuery) : false;

                const notesMatch = attendance.notes ? 
                    attendance.notes.toLowerCase().includes(searchQuery) : false;

                // Format time for search
                let timeMatch = false;
                if (attendance.start_time) {
                    const startTime = new Date(attendance.start_time).toLocaleTimeString('en-US', { 
                        hour: 'numeric', 
                        minute: '2-digit',
                        hour12: true 
                    }).toLowerCase();
                    timeMatch = startTime.includes(searchQuery);
                }
                if (!timeMatch && attendance.end_time) {
                    const endTime = new Date(attendance.end_time).toLocaleTimeString('en-US', { 
                        hour: 'numeric', 
                        minute: '2-digit',
                        hour12: true 
                    }).toLowerCase();
                    timeMatch = endTime.includes(searchQuery);
                }

                return dateMatch || locationMatch || typeMatch || remarksMatch || notesMatch || timeMatch;
            });

            // If category matches or has matching attendances, include it
            if (categoryMatches || filteredAttendances.length > 0) {
                return {
                    ...category,
                    attendances: categoryMatches ? category.attendances : filteredAttendances
                };
            }

            return null;
        }).filter(category => category !== null); // Remove null entries

        // Display filtered results
        if (filteredCategories.length === 0) {
            // Show empty state for no search results
            const kanbanboard = document.getElementById('kanbanboard');
            if (kanbanboard) {
                kanbanboard.innerHTML = `
                    <div class="col-12">
                        <div class="card border-0 shadow-none">
                            <div class="card-body text-center py-5">
                                <div class="mb-4">
                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#405189,secondary:#0ab39c" style="width:120px;height:120px"></lord-icon>
                                </div>
                                <h5 class="mb-3">No Results Found</h5>
                                <p class="text-muted mb-4">
                                    No categories or attendances match your search "<strong>${escapeHtml(query)}</strong>".<br>
                                    Try searching with different keywords.
                                </p>
                                <button class="btn btn-soft-primary" onclick="document.getElementById('search-task-options').value = ''; performSearch('');">
                                    <i class="ri-close-line align-bottom me-1"></i> Clear Search
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }
        } else {
            displayCategories(filteredCategories);
        }
    };

    // Initialize search on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSearch);
    } else {
        initializeSearch();
    }

    })();
