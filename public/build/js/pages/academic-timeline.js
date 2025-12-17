/**
 * Academic Timeline Page JavaScript
 * 
 * This file handles the frontend functionality for the academic timeline page,
 * including semester and quarter management interactions, and modal operations.
 */

(function () {
    'use strict';

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function () {
        initAcademicTimeline();
    });

    /**
     * Initialize Academic Timeline functionality
     */
    function initAcademicTimeline() {
        console.log('Academic Timeline initialized');

        // Load semester data from backend
        loadAllSemesters();

        // Initialize edit semester modal handlers
        initEditSemesterModal();
        // Initialize quarter edit modal handlers
        initEditQuarterModal();
    }

    /**
     * Load all semesters and populate the cards
     */
    async function loadAllSemesters() {
        try {
            const response = await fetch('/academic/semesters', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });

            let data;
            try {
                data = await response.json();
            } catch (e) {
                console.error('Failed to parse response:', e);
                throw new Error('Invalid response from server');
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to fetch semesters');
            }

            // Populate semester cards
            populateSemesterCards(data.data);

        } catch (error) {
            console.error('Error loading semesters:', error);
            showNotification('Failed to load semester data. Please refresh the page.', 'error');
        }
    }

    /**
     * Populate semester cards with data
     * @param {Array} semesters - Array of semester objects
     */
    function populateSemesterCards(semesters) {
        if (!semesters || semesters.length === 0) {
            console.warn('No semesters data available');
            return;
        }

        // Find first and second semester
        let firstSemester = semesters.find(s => s.name.toLowerCase().includes('1st') || s.name.toLowerCase().includes('first'));
        let secondSemester = semesters.find(s => s.name.toLowerCase().includes('2nd') || s.name.toLowerCase().includes('second'));

        // If not found by name, get by position
        if (!firstSemester) {
            firstSemester = semesters[0];
        }
        if (!secondSemester && semesters.length > 1) {
            secondSemester = semesters[1];
        }

        // Populate first semester card
        if (firstSemester) {
            populateSemesterCard('first', firstSemester);
        }

        // Populate second semester card
        if (secondSemester) {
            populateSemesterCard('second', secondSemester);
        }
    }

    /**
     * Populate a single semester card
     * @param {string} type - 'first' or 'second'
     * @param {Object} semester - Semester data object
     */
    function populateSemesterCard(type, semester) {
        const prefix = type === 'first' ? 'first' : 'second';
        const color = type === 'first' ? '#405189' : '#0ab39c';

        // Update header
        const nameElement = document.getElementById(`${prefix}-semester-name`);
        const yearElement = document.getElementById(`${prefix}-semester-year`);
        const statusElement = document.getElementById(`${prefix}-semester-status`);
        const editBtn = document.getElementById(`${prefix}-semester-edit-btn`);

        if (nameElement) nameElement.textContent = semester.name || `${type === 'first' ? '1st' : '2nd'} Semester`;
        if (yearElement) yearElement.textContent = semester.school_year || '2024-2025';

        // Update status badge
        if (statusElement) {
            if (semester.is_active) {
                statusElement.className = 'badge bg-success-subtle text-success';
                statusElement.innerHTML = '<i class="ri-checkbox-circle-fill align-middle me-1"></i>Active';
            } else {
                statusElement.className = 'badge bg-secondary-subtle text-secondary';
                statusElement.innerHTML = '<i class="ri-time-line align-middle me-1"></i>Inactive';
            }
        }

        // Show edit button and set semester ID
        if (editBtn && semester.id) {
            editBtn.setAttribute('data-semester-id', semester.id);
            editBtn.style.display = 'inline-flex';
        }

        // Update duration
        const durationElement = document.getElementById(`${prefix}-semester-duration`);
        if (durationElement && semester.start_date && semester.end_date) {
            const startDate = formatDateDisplay(semester.start_date);
            const endDate = formatDateDisplay(semester.end_date);
            durationElement.textContent = `${startDate} - ${endDate}`;
        } else {
            if (durationElement) durationElement.textContent = 'Not set';
        }

        // Update quarters count
        const quartersCountElement = document.getElementById(`${prefix}-semester-quarters-count`);
        if (quartersCountElement) {
            quartersCountElement.textContent = semester.quarters_count || 0;
        }

        // Populate quarters list
        const quartersContainer = document.getElementById(`${prefix}-semester-quarters`);
        if (quartersContainer && semester.quarters) {
            quartersContainer.innerHTML = '';
            semester.quarters.forEach((quarter, index) => {
                const quarterHtml = createQuarterItem(quarter, color, index + 2);
                quartersContainer.insertAdjacentHTML('beforeend', quarterHtml);
            });
        }

        // Update stats
        const quartersStat = document.getElementById(`${prefix}-semester-stat-quarters`);
        const activeStat = document.getElementById(`${prefix}-semester-stat-active`);
        const daysStat = document.getElementById(`${prefix}-semester-stat-days`);

        if (quartersStat) quartersStat.textContent = semester.quarters_count || 0;
        
        if (activeStat) {
            const activeCount = semester.quarters ? semester.quarters.filter(q => q.is_active).length : 0;
            activeStat.textContent = activeCount;
        }

        if (daysStat && semester.start_date && semester.end_date) {
            const days = calculateDaysBetween(semester.start_date, semester.end_date);
            daysStat.textContent = days;
        } else {
            if (daysStat) daysStat.textContent = '0';
        }
    }

    /**
     * Create quarter item HTML
     * @param {Object} quarter - Quarter data
     * @param {string} color - Color code
     * @param {number} staggerIndex - Stagger animation index
     * @returns {string} HTML string
     */
    function createQuarterItem(quarter, color, staggerIndex) {
        const isActive = quarter.is_active;
        const activeClass = isActive ? 'active' : '';
        const badgeClass = isActive ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary';
        const badgeIcon = isActive ? 'ri-checkbox-circle-fill' : 'ri-time-line';
        const badgeText = isActive ? 'Active' : 'Upcoming';

        return `
            <div class="quarter-item ${activeClass} fade-in stagger-${staggerIndex}" data-quarter="${quarter.id}" data-quarter-name="${quarter.name || ''}" data-quarter-active="${isActive ? 1 : 0}" style="--quarter-color: ${color}; --quarter-color-rgb: ${hexToRgb(color)};">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-semibold">${quarter.name || 'Quarter'}</h6>
                        <p class="text-muted mb-0 small">${quarter.description || 'No description'}</p>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="quarter-badge ${badgeClass}">
                            <i class="${badgeIcon} align-middle me-1"></i>${badgeText}
                        </span>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Format date for display (MMM DD, YYYY)
     * @param {string} dateString - Date string (YYYY-MM-DD)
     * @returns {string} Formatted date
     */
    function formatDateDisplay(dateString) {
        if (!dateString) return '';
        
        try {
            const date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
        } catch (e) {
            return dateString;
        }
    }

    /**
     * Calculate days between two dates
     * @param {string} startDate - Start date string
     * @param {string} endDate - End date string
     * @returns {number} Number of days
     */
    function calculateDaysBetween(startDate, endDate) {
        try {
            const start = new Date(startDate);
            const end = new Date(endDate);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return diffDays;
        } catch (e) {
            return 0;
        }
    }

    /**
     * Convert hex color to RGB
     * @param {string} hex - Hex color code
     * @returns {string} RGB string
     */
    function hexToRgb(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? 
            `${parseInt(result[1], 16)}, ${parseInt(result[2], 16)}, ${parseInt(result[3], 16)}` : 
            '64, 81, 137';
    }

    /**
     * Initialize Edit Semester Modal
     */
    function initEditSemesterModal() {
        const editModal = document.getElementById('editSemesterModal');
        const editButtons = document.querySelectorAll('.edit-semester-btn');
        const editForm = document.getElementById('editSemesterForm');
        const updateBtn = document.getElementById('update-btn');
        const closeModalBtn = document.getElementById('close-modal-btn');

        if (!editModal || !editForm) {
            console.warn('Edit semester modal elements not found');
            return;
        }

        // Handle edit button clicks - open modal and load data
        editButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const semesterId = this.getAttribute('data-semester-id');
                const semesterType = this.getAttribute('data-semester-type'); // 'first' or 'second'
                
                if (!semesterId) {
                    showNotification('Semester ID not found', 'error');
                    return;
                }

                // Store semester ID and type for later use
                editModal.setAttribute('data-current-semester-id', semesterId);
                if (semesterType) {
                    editModal.setAttribute('data-current-semester-type', semesterType);
                }

                // Configure static quarter options based on semester type
                configureQuarterSelectForSemester(semesterType);

                // Open modal using Bootstrap
                let modalInstance = bootstrap.Modal.getInstance(editModal);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(editModal);
                }
                modalInstance.show();

                // Load semester data after modal is shown
                const loadDataHandler = function() {
                    const currentSemesterId = editModal.getAttribute('data-current-semester-id');
                    if (currentSemesterId) {
                        loadSemesterData(currentSemesterId);
                    }
                    // Remove listener after use
                    editModal.removeEventListener('shown.bs.modal', loadDataHandler);
                };
                
                editModal.addEventListener('shown.bs.modal', loadDataHandler, { once: true });
            });
        });

        // Handle form submission via submit event
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            updateSemester();
            return false;
        });

        // Handle update button click
        if (updateBtn) {
            updateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                updateSemester();
            });
        }

        // Handle close button
        if (closeModalBtn) {
            closeModalBtn.addEventListener('click', function() {
                resetForm();
            });
        }

        // Reset form when modal is closed
        editModal.addEventListener('hidden.bs.modal', function() {
            resetForm();
        });

        // Also handle close via backdrop click
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal) {
                resetForm();
            }
        });
    }

    /**
     * Initialize Edit Quarter Modal and click handlers on quarter items
     */
    function initEditQuarterModal() {
        const quarterModal = document.getElementById('editQuarterModal');
        const quarterForm = document.getElementById('editQuarterForm');
        const quarterIdInput = document.getElementById('quarterId');
        const quarterNameInput = document.getElementById('quarter_name');
        const quarterActiveInput = document.getElementById('quarter_is_active');
        const quarterUpdateBtn = document.getElementById('quarter-update-btn');

        if (!quarterModal || !quarterForm) {
            console.warn('Edit quarter modal elements not found');
            return;
        }

        // Delegate click events for dynamically created quarter items
        document.addEventListener('click', function (e) {
            const item = e.target.closest('.quarter-item');
            if (!item) return;

            const quarterId = item.getAttribute('data-quarter');
            const quarterName = item.getAttribute('data-quarter-name') || '';
            const isActive = item.getAttribute('data-quarter-active') === '1';

            if (!quarterId) {
                console.warn('Quarter ID not found on clicked item');
                return;
            }

            // Populate modal fields
            quarterIdInput.value = quarterId;
            quarterNameInput.value = quarterName;
            quarterActiveInput.checked = isActive;

            // Show modal
            let modalInstance = bootstrap.Modal.getInstance(quarterModal);
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(quarterModal);
            }
            modalInstance.show();
        });

        // Handle quarter update
        const handleQuarterUpdate = async () => {
            const quarterId = quarterIdInput.value;
            if (!quarterId) {
                showNotification('Quarter ID is missing.', 'error');
                return;
            }

            const isActive = !!quarterActiveInput.checked;

            try {
                quarterUpdateBtn.disabled = true;
                quarterUpdateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

                const response = await fetch(`/academic/quarter/${quarterId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ is_active: isActive })
                });

                let result;
                try {
                    result = await response.json();
                } catch (e) {
                    console.error('Failed to parse quarter update response:', e);
                    throw new Error('Invalid response from server');
                }

                if (!response.ok) {
                    const errorMessage = result.message || result.error || 'Failed to update quarter';
                    console.error('Quarter update failed:', errorMessage, result);
                    throw new Error(errorMessage);
                }

                // Reload semesters to reflect quarter status changes (only one active per semester)
                await loadAllSemesters();

                showNotification('Quarter status updated successfully!', 'success');

                // Close modal
                const modalInstance = bootstrap.Modal.getInstance(quarterModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            } catch (error) {
                console.error('Error updating quarter:', error);
                showNotification(error.message || 'Failed to update quarter. Please try again.', 'error');
            } finally {
                quarterUpdateBtn.disabled = false;
                quarterUpdateBtn.innerHTML = '<i class="ri-save-line align-middle me-1"></i>Update Quarter';
            }
        };

        quarterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleQuarterUpdate();
            return false;
        });

        quarterUpdateBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleQuarterUpdate();
        });
    }


    /**
     * Load semester data from server
     * @param {number} semesterId - Semester ID
     */
    async function loadSemesterData(semesterId) {
        try {
            const updateBtn = document.getElementById('update-btn');
            if (updateBtn) {
                updateBtn.disabled = true;
                updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Loading...';
            }

            const response = await fetch(`/academic/semester/${semesterId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            });

            let data;
            try {
                data = await response.json();
            } catch (e) {
                console.error('Failed to parse response:', e);
                const text = await response.text();
                console.error('Response text:', text);
                throw new Error('Invalid response from server. Status: ' + response.status);
            }

            if (!response.ok) {
                const errorMsg = data.message || data.error || 'Failed to fetch semester data';
                console.error('Server error:', response.status, errorMsg, data);
                throw new Error(errorMsg);
            }

            if (!data.success) {
                throw new Error(data.message || 'Failed to fetch semester data');
            }

            populateForm(data);

        } catch (error) {
            console.error('Error loading semester data:', error);
            const errorMessage = error.message || 'Failed to load semester data. Please try again.';
            showNotification(errorMessage, 'error');
            
            // Close modal if it's open
            const modal = bootstrap.Modal.getInstance(document.getElementById('editSemesterModal'));
            if (modal) {
                modal.hide();
            }
        } finally {
            const updateBtn = document.getElementById('update-btn');
            if (updateBtn) {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="ri-save-line align-middle me-1"></i>Update Semester';
            }
        }
    }

    /**
     * Populate form with semester data
     * @param {Object} data - Semester data
     */
    function populateForm(data) {
        document.getElementById('semesterId').value = data.id || '';
        document.getElementById('semester_name').value = data.name || '';
        document.getElementById('school_year').value = data.school_year || '';
        
        // Format dates for input fields (YYYY-MM-DD)
        if (data.start_date) {
            const startDate = new Date(data.start_date);
            document.getElementById('start_date').value = formatDateForInput(startDate);
        }
        
        if (data.end_date) {
            const endDate = new Date(data.end_date);
            document.getElementById('end_date').value = formatDateForInput(endDate);
        }

        // Set active status
        const isActiveCheckbox = document.getElementById('is_active');
        if (isActiveCheckbox) {
            isActiveCheckbox.checked = data.is_active == 1 || data.is_active === true;
        }

        // Configure quarters multi-select (vanilla select) from backend quarters
        try {
            const quarterSelectInstance = window['vanillaSelect_edit-quarter-select'];
            if (quarterSelectInstance && Array.isArray(data.quarters)) {
                const options = data.quarters.map(q => ({
                    value: String(q.id),
                    label: q.name || `Quarter ${q.id}`,
                }));

                // Set options
                if (typeof quarterSelectInstance.setOptions === 'function') {
                    quarterSelectInstance.setOptions(options);
                }

                // Default selected: all quarters of this semester
                const defaultSelected = options.map(o => o.value);
                if (typeof quarterSelectInstance.setValue === 'function') {
                    quarterSelectInstance.setValue(defaultSelected);
                }
            }
        } catch (e) {
            console.warn('Failed to configure quarters select from backend data:', e);
        }

        // Clear previous errors
        clearFormErrors();
    }

    /**
     * Format date for input field (YYYY-MM-DD)
     * @param {Date} date - Date object
     * @returns {string} Formatted date string
     */
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    /**
     * Update semester
     */
    async function updateSemester() {
        const form = document.getElementById('editSemesterForm');
        if (!form) {
            console.error('Form not found');
            showNotification('Form not found. Please refresh the page.', 'error');
            return;
        }

        // Get form values directly from inputs
        const semesterIdInput = document.getElementById('semesterId');
        const semesterId = semesterIdInput ? semesterIdInput.value.trim() : null;
        const nameInput = document.getElementById('semester_name');
        const schoolYearInput = document.getElementById('school_year');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const isActiveCheckbox = document.getElementById('is_active');
        const updateBtn = document.getElementById('update-btn');

        // Validate required fields
        if (!semesterId) {
            console.error('Semester ID is missing');
            showNotification('Semester ID is missing. Please try again.', 'error');
            return;
        }

        const name = nameInput ? nameInput.value.trim() : '';
        const schoolYear = schoolYearInput ? schoolYearInput.value.trim() : '';
        const startDate = startDateInput ? startDateInput.value.trim() : '';
        const endDate = endDateInput ? endDateInput.value.trim() : '';
        const isActive = isActiveCheckbox ? isActiveCheckbox.checked : false;

        // Get selected quarter IDs from vanilla select (multiple)
        let selectedQuarterIds = [];
        try {
            const quarterSelectInstance = window['vanillaSelect_edit-quarter-select'];
            if (quarterSelectInstance && typeof quarterSelectInstance.getValue === 'function') {
                const qVal = quarterSelectInstance.getValue();
                if (Array.isArray(qVal)) {
                    selectedQuarterIds = qVal;
                } else if (qVal) {
                    selectedQuarterIds = [qVal];
                }
            }
        } catch (e) {
            console.warn('Unable to read quarter values from vanilla select:', e);
        }

        // Client-side validation (semester name is fixed and cannot be changed)
        if (!schoolYear) {
            showFormError('school_year', 'School year is required');
            schoolYearInput?.focus();
            return;
        }

        if (!startDate) {
            showFormError('start_date', 'Start date is required');
            startDateInput?.focus();
            return;
        }

        if (!endDate) {
            showFormError('end_date', 'End date is required');
            endDateInput?.focus();
            return;
        }

        // Validate dates
        const startDateObj = new Date(startDate);
        const endDateObj = new Date(endDate);

        if (isNaN(startDateObj.getTime())) {
            showFormError('start_date', 'Invalid start date');
            startDateInput?.focus();
            return;
        }

        if (isNaN(endDateObj.getTime())) {
            showFormError('end_date', 'Invalid end date');
            endDateInput?.focus();
            return;
        }

        if (startDateObj > endDateObj) {
            showFormError('end_date', 'End date must be after start date');
            endDateInput?.focus();
            return;
        }

        try {
            // Disable submit button
            if (updateBtn) {
                updateBtn.disabled = true;
                updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
            }

            // Prepare data object
            const data = {
                name: name,
                school_year: schoolYear,
                start_date: startDate,
                end_date: endDate,
                is_active: isActive,
                // Selected quarters for this semester (multiple)
                quarter_ids: selectedQuarterIds
            };

            const response = await fetch(`/academic/semester/${semesterId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            let result;
            try {
                result = await response.json();
            } catch (e) {
                console.error('Failed to parse response:', e);
                throw new Error('Invalid response from server');
            }

            if (!response.ok) {
                // Handle validation errors
                if (response.status === 422 && result.errors) {
                    displayValidationErrors(result.errors);
                    return;
                }
                const errorMessage = result.message || result.error || 'Failed to update semester';
                console.error('Update failed:', errorMessage, result);
                throw new Error(errorMessage);
            }

            // Success
            showNotification('Semester updated successfully!', 'success');
            
            // Close modal
            setTimeout(() => {
                const modalElement = document.getElementById('editSemesterModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
                // Reload semester data instead of full page reload
                loadAllSemesters();
            }, 1000);

        } catch (error) {
            console.error('Error updating semester:', error);
            showNotification(error.message || 'Failed to update semester. Please try again.', 'error');
        } finally {
            // Re-enable submit button
            if (updateBtn) {
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="ri-save-line align-middle me-1"></i>Update Semester';
            }
        }
    }

    /**
     * Display validation errors
     * @param {Object} errors - Validation errors object
     */
    function displayValidationErrors(errors) {
        clearFormErrors();
        
        Object.keys(errors).forEach(field => {
            const errorField = field.replace('.', '_');
            const errorElement = document.getElementById(`${errorField}-error`);
            const inputElement = document.getElementById(errorField) || document.getElementById(field);
            
            if (errorElement) {
                errorElement.textContent = errors[field][0];
                errorElement.style.display = 'block';
            }
            
            if (inputElement) {
                inputElement.classList.add('is-invalid');
            }
        });
    }

    /**
     * Configure static quarter select based on semester type
     * 1st semester -> Quarter 1 & 2
     * 2nd semester -> Quarter 3 & 4
     * 
     * @param {string} semesterType - 'first' or 'second'
     */
    function configureQuarterSelectForSemester(semesterType) {
        const quarterSelectInstance = window['vanillaSelect_edit-quarter-select'];
        if (!quarterSelectInstance || typeof quarterSelectInstance.setOptions !== 'function') {
            console.warn('Quarter vanilla select instance not found');
            return;
        }

        let options;
        let defaultValue;

        if (semesterType === 'first') {
            options = [
                { value: '1', label: '1st Quarter' },
                { value: '2', label: '2nd Quarter' },
            ];
            defaultValue = '1';
        } else if (semesterType === 'second') {
            options = [
                { value: '3', label: '3rd Quarter' },
                { value: '4', label: '4th Quarter' },
            ];
            defaultValue = '3';
        } else {
            // Fallback: all four quarters
            options = [
                { value: '1', label: '1st Quarter' },
                { value: '2', label: '2nd Quarter' },
                { value: '3', label: '3rd Quarter' },
                { value: '4', label: '4th Quarter' },
            ];
            defaultValue = '1';
        }

        // Set options and default value
        try {
            quarterSelectInstance.setOptions(options);
            quarterSelectInstance.setValue(defaultValue);
        } catch (e) {
            console.warn('Failed to configure quarter select:', e);
        }
    }

    /**
     * Show form field error
     * @param {string} field - Field name
     * @param {string} message - Error message
     */
    function showFormError(field, message) {
        const errorElement = document.getElementById(`${field}-error`);
        const inputElement = document.getElementById(field);
        
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
        
        if (inputElement) {
            inputElement.classList.add('is-invalid');
        }
    }

    /**
     * Clear all form errors
     */
    function clearFormErrors() {
        const errorElements = document.querySelectorAll('.invalid-feedback');
        const invalidInputs = document.querySelectorAll('.is-invalid');
        
        errorElements.forEach(el => {
            el.textContent = '';
            el.style.display = 'none';
        });
        
        invalidInputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
    }

    /**
     * Reset form
     */
    function resetForm() {
        const form = document.getElementById('editSemesterForm');
        if (form) {
            form.reset();
            clearFormErrors();
        }
    }

    /**
     * Get CSRF token from meta tag
     * @returns {string} CSRF token
     */
    function getCsrfToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    /**
     * Show notification
     * @param {string} message - Notification message
     * @param {string} type - Notification type (success, error, warning, info)
     */
    function showNotification(message, type = 'info') {
        try {
            // Prefer using the global toast-notification component via CustomEvent
            if (typeof window !== 'undefined' && typeof window.dispatchEvent === 'function') {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: {
                        message: message,
                        type: type || 'info',
                        // you can change default position/duration here if needed
                        position: 'top-right',
                        duration: 3000,
                    }
                }));
                return;
            }
        } catch (e) {
            console.warn('Failed to dispatch toast notification:', e);
        }

        // Absolute fallback if toast JS is not available
        if (typeof alert === 'function') {
            alert(message);
        } else {
            console.log('Notification:', type, message);
        }
    }

    // Export functions
    window.AcademicTimeline = {
        init: initAcademicTimeline,
        loadAllSemesters: loadAllSemesters,
        loadSemesterData: loadSemesterData,
        updateSemester: updateSemester,
        populateSemesterCards: populateSemesterCards
    };

})();
