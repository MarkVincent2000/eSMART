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
        // Quarter editing via modal has been removed; quarters follow semester status

        // Initialize semester status quick-toggle modal
        initSemesterStatusModal();

        // Initialize reactivation modal for previous semesters table
        initReactivateSemesterModal();
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

            // Populate inactive semesters table
            populateInactiveSemestersTable(data.data);

        } catch (error) {
            console.error('Error loading semesters:', error);
            showNotification('Failed to load semester data. Please refresh the page.', 'error');
        }
    }

    /**
     * Populate semester cards with data
     * @param {Array} semesters - Array of semester objects
     */
    // Keep references to the currently loaded first and second semesters
    let firstSemesterState = null;
    let secondSemesterState = null;

    function populateSemesterCards(semesters) {
        // Always reset cards first so stale data is not shown
        clearSemesterCard('first');
        clearSemesterCard('second');

        if (!semesters || semesters.length === 0) {
            console.warn('No semesters data available');
            firstSemesterState = null;
            secondSemesterState = null;
            return;
        }

        // Prefer semesters explicitly marked as displayable (is_display === true)
        let firstSemester = semesters.find(s => {
            const name = (s.name || '').toLowerCase();
            return s.is_display && (name.includes('1st') || name.includes('first'));
        }) || null;

        let secondSemester = semesters.find(s => {
            const name = (s.name || '').toLowerCase();
            return s.is_display && (name.includes('2nd') || name.includes('second'));
        }) || null;

        // Store in local state for editing
        firstSemesterState = firstSemester || null;
        secondSemesterState = secondSemester || null;

        // Populate first semester card (only if displayable semester exists)
        if (firstSemester) {
            populateSemesterCard('first', firstSemester);
        }

        // Populate second semester card (only if displayable semester exists)
        if (secondSemester) {
            populateSemesterCard('second', secondSemester);
        }
    }

    /**
     * Clear semester card UI when no displayable semester is set.
     * @param {'first'|'second'} type
     */
    function clearSemesterCard(type) {
        const prefix = type === 'first' ? 'first' : 'second';
        const defaultName = type === 'first' ? '1st Semester' : '2nd Semester';

        const nameElement = document.getElementById(`${prefix}-semester-name`);
        const yearElement = document.getElementById(`${prefix}-semester-year`);
        const statusElement = document.getElementById(`${prefix}-semester-status`);
        const durationElement = document.getElementById(`${prefix}-semester-duration`);
        const quartersContainer = document.getElementById(`${prefix}-semester-quarters`);
        const quartersCountElement = document.getElementById(`${prefix}-semester-quarters-count`);
        const quartersStat = document.getElementById(`${prefix}-semester-stat-quarters`);
        const activeStat = document.getElementById(`${prefix}-semester-stat-active`);
        const daysStat = document.getElementById(`${prefix}-semester-stat-days`);

        if (nameElement) nameElement.textContent = defaultName;
        if (yearElement) yearElement.textContent = 'Not set';

        if (statusElement) {
            statusElement.className = 'badge bg-secondary-subtle text-secondary';
            statusElement.innerHTML = '<i class="ri-time-line align-middle me-1"></i>Inactive';
        }

        if (durationElement) durationElement.textContent = 'Not set';
        if (quartersContainer) quartersContainer.innerHTML = '';
        if (quartersCountElement) quartersCountElement.textContent = '0';
        if (quartersStat) quartersStat.textContent = '0';
        if (activeStat) activeStat.textContent = '0';
        if (daysStat) daysStat.textContent = '0';
    }

    /**
     * Populate inactive semesters table (grouped by school year)
     * Shows only inactive semesters; 1 row per school_year where NO semester is active
     * Simple search, sort, and pagination without external libraries.
     */
    let inactiveSemestersData = [];
    let inactiveSemestersFiltered = [];
    let inactiveSemestersPage = 1;
    const inactiveSemestersPageSize = 5;

    function populateInactiveSemestersTable(semesters) {
        const tableBody = document.getElementById('inactiveSemesterTbody');
        if (!tableBody) return;

        // Group semesters by school_year
        const groups = {};
        (semesters || []).forEach(sem => {
            if (!sem) return;
            const year = sem.school_year || 'N/A';
            if (!groups[year]) {
                groups[year] = {
                    school_year: year,
                    all: [],
                };
            }
            groups[year].all.push(sem);
        });

        // Build rows: only years where NO semester is active
        const rows = [];
        Object.values(groups).forEach(group => {
            const anyActive = group.all.some(s => s.is_active);
            if (anyActive) {
                return; // skip this school year entirely
            }

            let first = null;
            let second = null;
            group.all.forEach(sem => {
                const name = (sem.name || '').toLowerCase();
                if (name.includes('1st') || name.includes('first')) {
                    first = sem;
                } else if (name.includes('2nd') || name.includes('second')) {
                    second = sem;
                }
            });

            if (!first && !second) {
                return;
            }

            rows.push({
                school_year: group.school_year,
                first_semester: first ? (first.name || '1st Semester') : '-',
                second_semester: second ? (second.name || '2nd Semester') : '-',
                first_id: first ? first.id : null,
                second_id: second ? second.id : null,
            });
        });

        inactiveSemestersData = rows;
        inactiveSemestersPage = 1;

        // Wire search & sort handlers once
        setupInactiveSemestersControls();

        applyInactiveSemestersFiltersAndRender();
    }

    function setupInactiveSemestersControls() {
        const searchInput = document.getElementById('inactive-semester-search');
        const sortSelect = document.getElementById('inactive-semester-sort');
        const prevBtn = document.getElementById('inactive-semester-prev');
        const nextBtn = document.getElementById('inactive-semester-next');

        if (searchInput && !searchInput._academicTimelineBound) {
            searchInput.addEventListener('input', function () {
                inactiveSemestersPage = 1;
                applyInactiveSemestersFiltersAndRender();
            });
            searchInput._academicTimelineBound = true;
        }

        if (sortSelect && !sortSelect._academicTimelineBound) {
            sortSelect.addEventListener('change', function () {
                inactiveSemestersPage = 1;
                applyInactiveSemestersFiltersAndRender();
            });
            sortSelect._academicTimelineBound = true;
        }

        if (prevBtn && !prevBtn._academicTimelineBound) {
            prevBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (inactiveSemestersPage > 1) {
                    inactiveSemestersPage--;
                    renderInactiveSemestersTable();
                }
            });
            prevBtn._academicTimelineBound = true;
        }

        if (nextBtn && !nextBtn._academicTimelineBound) {
            nextBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const totalPages = Math.ceil((inactiveSemestersFiltered.length || 0) / inactiveSemestersPageSize);
                if (inactiveSemestersPage < totalPages) {
                    inactiveSemestersPage++;
                    renderInactiveSemestersTable();
                }
            });
            nextBtn._academicTimelineBound = true;
        }
    }

    function applyInactiveSemestersFiltersAndRender() {
        const searchInput = document.getElementById('inactive-semester-search');
        const sortSelect = document.getElementById('inactive-semester-sort');

        const searchTerm = (searchInput ? searchInput.value : '').toLowerCase();
        const sortField = (sortSelect ? sortSelect.value : 'school_year') || 'school_year';

        // Filter
        inactiveSemestersFiltered = inactiveSemestersData.filter(row => {
            if (!searchTerm) return true;
            return (
                (row.school_year || '').toLowerCase().includes(searchTerm) ||
                (row.first_semester || '').toLowerCase().includes(searchTerm) ||
                (row.second_semester || '').toLowerCase().includes(searchTerm)
            );
        });

        // Sort ascending by selected field
        inactiveSemestersFiltered.sort((a, b) => {
            const va = (a[sortField] || '').toString().toLowerCase();
            const vb = (b[sortField] || '').toString().toLowerCase();
            if (va < vb) return -1;
            if (va > vb) return 1;
            return 0;
        });

        renderInactiveSemestersTable();
    }

    function renderInactiveSemestersTable() {
        const tableBody = document.getElementById('inactiveSemesterTbody');
        const prevBtn = document.getElementById('inactive-semester-prev');
        const nextBtn = document.getElementById('inactive-semester-next');
        const noResultEl = document.getElementById('inactive-semester-noresult');
        if (!tableBody) return;

        tableBody.innerHTML = '';

        const total = inactiveSemestersFiltered.length;
        const totalPages = Math.max(1, Math.ceil(total / inactiveSemestersPageSize));
        if (inactiveSemestersPage > totalPages) {
            inactiveSemestersPage = totalPages;
        }

        const start = (inactiveSemestersPage - 1) * inactiveSemestersPageSize;
        const end = Math.min(start + inactiveSemestersPageSize, total);

        for (let i = start; i < end; i++) {
            const row = inactiveSemestersFiltered[i];
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${row.school_year}</td>
                <td>${row.first_semester}</td>
                <td>${row.second_semester}</td>
                <td class="text-end">
                    <button type="button"
                        class="btn btn-sm btn-outline-primary inactive-semester-activate-btn"
                        data-school-year="${row.school_year}"
                        data-first-id="${row.first_id || ''}"
                        data-second-id="${row.second_id || ''}">
                        <i class="ri-refresh-line align-middle me-1"></i>Set as Active
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        }

        // Toggle no-result lordicon
        if (noResultEl) {
            noResultEl.style.display = total === 0 ? 'block' : 'none';
        }

        // Update pagination button disabled state
        if (prevBtn) {
            if (inactiveSemestersPage <= 1) {
                prevBtn.classList.add('disabled');
            } else {
                prevBtn.classList.remove('disabled');
            }
        }

        if (nextBtn) {
            if (inactiveSemestersPage >= totalPages || totalPages === 0) {
                nextBtn.classList.add('disabled');
            } else {
                nextBtn.classList.remove('disabled');
            }
        }
    }

    /**
     * Initialize reactivation modal for inactive semesters table
     */
    function initReactivateSemesterModal() {
        const modalEl = document.getElementById('reactivateSemesterModal');
        const form = document.getElementById('reactivateSemesterForm');
        const confirmBtn = document.getElementById('reactivate-semester-confirm-btn');
        const firstIdInput = document.getElementById('reactivate_first_id');
        const secondIdInput = document.getElementById('reactivate_second_id');
        const schoolYearInput = document.getElementById('reactivate_school_year');
        const schoolYearLabel = document.getElementById('reactivate_school_year_label');

        if (!modalEl || !form || !confirmBtn) {
            console.warn('Reactivate semester modal elements not found');
            return;
        }

        // Open modal when clicking action button in inactive semesters table
        if (!document._reactivateSemesterClickBound) {
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.inactive-semester-activate-btn');
                if (!btn) return;

                const firstId = btn.getAttribute('data-first-id') || '';
                const secondId = btn.getAttribute('data-second-id') || '';
                const schoolYear = btn.getAttribute('data-school-year') || '';

                firstIdInput.value = firstId;
                secondIdInput.value = secondId;
                schoolYearInput.value = schoolYear;
                if (schoolYearLabel) schoolYearLabel.textContent = schoolYear || 'N/A';

                let modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (!modalInstance) {
                    modalInstance = new bootstrap.Modal(modalEl);
                }
                modalInstance.show();
            });
            document._reactivateSemesterClickBound = true;
        }

        const handleReactivate = async () => {
            const firstId = firstIdInput.value;
            if (!firstId) {
                showNotification('No 1st Semester found for this school year.', 'error');
                return;
            }

            try {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

                const response = await fetch(`/academic/semester/${firstId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ is_active: true })
                });

                let result;
                try {
                    result = await response.json();
                } catch (e) {
                    console.error('Failed to parse response:', e);
                    throw new Error('Invalid response from server');
                }

                if (!response.ok) {
                    const errorMessage = result.message || result.error || 'Failed to reactivate semesters';
                    console.error('Reactivation failed:', errorMessage, result);
                    throw new Error(errorMessage);
                }

                showNotification('Semesters reactivated successfully!', 'success');

                // Refresh cards and inactive table
                await loadAllSemesters();

                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) {
                    modalInstance.hide();
                }
            } catch (error) {
                console.error('Error reactivating semesters:', error);
                showNotification(error.message || 'Failed to reactivate semesters. Please try again.', 'error');
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="ri-check-line align-middle me-1"></i>Confirm';
            }
        };

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleReactivate();
            return false;
        });

        confirmBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleReactivate();
        });
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

        // Attach data attributes to card for status modal
        const cardElement = document.querySelector(`.semester-card.${type === 'first' ? 'first-semester' : 'second-semester'}`);
        if (cardElement && semester.id) {
            cardElement.setAttribute('data-semester-id', semester.id);
            cardElement.setAttribute('data-semester-name', semester.name || '');
            cardElement.setAttribute('data-semester-year', semester.school_year || '');
            cardElement.setAttribute('data-semester-active', semester.is_active ? '1' : '0');
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
        const editForm = document.getElementById('editSemesterForm');
        const updateBtn = document.getElementById('update-btn');
        const updateBtnText = document.getElementById('update-btn-text');
        const closeModalBtn = document.getElementById('close-modal-btn');
        const createSemesterBtn = document.getElementById('create-semester-btn');
        const editSemestersBtn = document.getElementById('edit-semesters-btn');
        const modalTitleText = document.getElementById('editSemesterModalTitleText');
        const editSection = document.getElementById('edit-semester-section');
        const createSection = document.getElementById('create-semester-section');
        const schoolYearInput = document.getElementById('school_year');

        if (!editModal || !editForm) {
            console.warn('Edit semester modal elements not found');
            return;
        }

        // Keep date pickers constrained to the chosen school year (YYYY-YYYY)
        if (schoolYearInput) {
            const applyLimits = () => {
                const value = (schoolYearInput.value || '').trim();
                updateDateLimitsForSchoolYear(value);
            };
            schoolYearInput.addEventListener('change', applyLimits);
            schoolYearInput.addEventListener('blur', applyLimits);
            schoolYearInput.addEventListener('input', applyLimits);
        }

        // Handle "Create New Semester" button (CREATE BOTH MODE)
        if (createSemesterBtn) {
            createSemesterBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Create mode for BOTH 1st and 2nd semester
                editModal.setAttribute('data-mode', 'create-both');
                editModal.removeAttribute('data-current-semester-id');
                editModal.removeAttribute('data-current-semester-type');

                // Reset form for fresh entry (also clears date limits)
                resetForm();

                // Show create section, hide edit section
                if (editSection) editSection.style.display = 'none';
                if (createSection) createSection.style.display = 'flex';

                // Update modal title/button for create
                if (modalTitleText) {
                    modalTitleText.textContent = 'Create Semester';
                }
                if (updateBtnText) {
                    updateBtnText.textContent = 'Create Semester';
                }

                // Ensure date pickers have no constraints until a valid school year is entered
                updateDateLimitsForSchoolYear('');

                // Show modal (Bootstrap will handle this via data-bs-target)
            });
        }

        // Handle "Edit Semesters" button (EDIT BOTH MODE)
        if (editSemestersBtn) {
            editSemestersBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                if (!firstSemesterState && !secondSemesterState) {
                    showNotification('No semesters available to edit.', 'warning');
                    return;
                }

                editModal.setAttribute('data-mode', 'edit-both');

                // Use school year from first semester if present, otherwise second
                const schoolYearInput = document.getElementById('school_year');
                if (schoolYearInput) {
                    const yearValue = (firstSemesterState && firstSemesterState.school_year) ||
                        (secondSemesterState && secondSemesterState.school_year) ||
                        '';
                    schoolYearInput.value = yearValue;
                    // Apply date limits based on this school year so pickers behave consistently
                    updateDateLimitsForSchoolYear(yearValue);
                }

                // Fill IDs and dates for both semesters
                const firstIdInput = document.getElementById('first_semester_id');
                const secondIdInput = document.getElementById('second_semester_id');
                const firstStartInput = document.getElementById('first_start_date');
                const firstEndInput = document.getElementById('first_end_date');
                const secondStartInput = document.getElementById('second_start_date');
                const secondEndInput = document.getElementById('second_end_date');

                if (firstSemesterState) {
                    if (firstIdInput) firstIdInput.value = firstSemesterState.id || '';
                    if (firstStartInput) firstStartInput.value = firstSemesterState.start_date || '';
                    if (firstEndInput) firstEndInput.value = firstSemesterState.end_date || '';
                }

                if (secondSemesterState) {
                    if (secondIdInput) secondIdInput.value = secondSemesterState.id || '';
                    if (secondStartInput) secondStartInput.value = secondSemesterState.start_date || '';
                    if (secondEndInput) secondEndInput.value = secondSemesterState.end_date || '';
                }

                // Show create (dual) section, hide single-edit section
                if (editSection) editSection.style.display = 'none';
                if (createSection) createSection.style.display = 'flex';

                if (modalTitleText) {
                    modalTitleText.textContent = 'Edit Semesters';
                }
                if (updateBtnText) {
                    updateBtnText.textContent = 'Update Semesters';
                }
            });
        }

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
                // Clear mode when closing
                editModal.removeAttribute('data-mode');
                editModal.removeAttribute('data-current-semester-id');
                editModal.removeAttribute('data-current-semester-type');

                // Default back to edit layout
                if (editSection) editSection.style.display = 'flex';
                if (createSection) createSection.style.display = 'none';
            });
        }

        // Reset form when modal is fully closed (but NOT on backdrop click,
        // since data-bs-backdrop="static" keeps the modal open)
        editModal.addEventListener('hidden.bs.modal', function() {
            resetForm();
            editModal.removeAttribute('data-mode');
            editModal.removeAttribute('data-current-semester-id');
            editModal.removeAttribute('data-current-semester-type');
            if (modalTitleText) {
                modalTitleText.textContent = 'Edit Semester';
            }
            if (updateBtnText) {
                updateBtnText.textContent = 'Update Semester';
            }
            if (editSection) editSection.style.display = 'flex';
            if (createSection) createSection.style.display = 'none';
        });
    }

    /**
     * Initialize Semester Status Modal (for quick active/inactive toggle)
     */
    function initSemesterStatusModal() {
        const statusModal = document.getElementById('semesterStatusModal');
        const statusForm = document.getElementById('semesterStatusForm');
        const statusSaveBtn = document.getElementById('semester-status-save-btn');
        const statusSemesterIdInput = document.getElementById('status_semester_id');
        const statusSemesterName = document.getElementById('status_semester_name');
        const statusSemesterYear = document.getElementById('status_semester_year');
        const statusIsActiveInput = document.getElementById('status_is_active');

        if (!statusModal || !statusForm || !statusSaveBtn) {
            console.warn('Semester status modal elements not found');
            return;
        }

        // Open status modal when clicking on a semester card
        document.addEventListener('click', function (e) {
            const card = e.target.closest('.semester-clickable');
            if (!card) return;

            const semesterId = card.getAttribute('data-semester-id');
            const semesterName = card.getAttribute('data-semester-name') || '';
            const semesterYear = card.getAttribute('data-semester-year') || '';
            const isActive = card.getAttribute('data-semester-active') === '1';

            if (!semesterId) {
                console.warn('Semester ID not found on card');
                return;
            }

            statusSemesterIdInput.value = semesterId;
            if (statusSemesterName) statusSemesterName.textContent = semesterName || 'Semester';
            if (statusSemesterYear) statusSemesterYear.textContent = semesterYear || 'N/A';
            if (statusIsActiveInput) statusIsActiveInput.checked = isActive;

            let modalInstance = bootstrap.Modal.getInstance(statusModal);
            if (!modalInstance) {
                modalInstance = new bootstrap.Modal(statusModal);
            }
            modalInstance.show();
        });

        const handleStatusSave = async () => {
            const semesterId = statusSemesterIdInput.value;
            if (!semesterId) {
                showNotification('Semester ID is missing.', 'error');
                return;
            }

            const isActive = !!statusIsActiveInput.checked;

            try {
                statusSaveBtn.disabled = true;
                statusSaveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';

                const response = await fetch(`/academic/semester/${semesterId}/status`, {
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
                    console.error('Failed to parse response:', e);
                    throw new Error('Invalid response from server');
                }

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        showNotification(result.message || 'Validation failed.', 'error');
                        return;
                    }
                    const errorMessage = result.message || result.error || 'Failed to update semester status';
                    console.error('Status update failed:', errorMessage, result);
                    throw new Error(errorMessage);
                }

                showNotification('Semester status updated successfully!', 'success');

                // Refresh cards
                await loadAllSemesters();

                const modalInstance = bootstrap.Modal.getInstance(statusModal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            } catch (error) {
                console.error('Error updating semester status:', error);
                showNotification(error.message || 'Failed to update semester status. Please try again.', 'error');
            } finally {
                statusSaveBtn.disabled = false;
                statusSaveBtn.innerHTML = '<i class="ri-save-line align-middle me-1"></i>Save Status';
            }
        };

        statusForm.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleStatusSave();
            return false;
        });

        statusSaveBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            handleStatusSave();
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

        // Show semester name as read-only text (1st or 2nd Semester)
        const semesterNameInput = document.getElementById('semester_name');
        if (semesterNameInput) {
            semesterNameInput.value = data.name || '';
        }

        const schoolYearInput = document.getElementById('school_year');
        if (schoolYearInput) {
            schoolYearInput.value = data.school_year || '';
            // Apply date limits based on existing school year
            updateDateLimitsForSchoolYear(schoolYearInput.value);
        }
        
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
        const editModal = document.getElementById('editSemesterModal');
        const mode = editModal ? editModal.getAttribute('data-mode') : 'edit-both';
        const isCreateBoth = mode === 'create-both';
        const isEditBoth = mode === 'edit-both';

        const schoolYearInput = document.getElementById('school_year');
        const updateBtn = document.getElementById('update-btn');
        const updateBtnText = document.getElementById('update-btn-text');

        const schoolYear = schoolYearInput ? schoolYearInput.value.trim() : '';

        // Validate required fields (shared)
        if (!schoolYear) {
            showFormError('school_year', 'School year is required');
            schoolYearInput?.focus();
            return;
        }

        // Validate school year format YYYY-YYYY
        const schoolYearPattern = /^\d{4}-\d{4}$/;
        if (!schoolYearPattern.test(schoolYear)) {
            showFormError('school_year', 'School year must be in the format YYYY-YYYY.');
            schoolYearInput?.focus();
            return;
        }

        // EDIT MODE: validate single semester fields
        // For dual create/edit: validate both 1st and 2nd semester dates
        let firstStart, firstEnd, secondStart, secondEnd;
        if (isCreateBoth) {
            const firstStartInput = document.getElementById('first_start_date');
            const firstEndInput = document.getElementById('first_end_date');
            const secondStartInput = document.getElementById('second_start_date');
            const secondEndInput = document.getElementById('second_end_date');

            firstStart = firstStartInput ? firstStartInput.value.trim() : '';
            firstEnd = firstEndInput ? firstEndInput.value.trim() : '';
            secondStart = secondStartInput ? secondStartInput.value.trim() : '';
            secondEnd = secondEndInput ? secondEndInput.value.trim() : '';

            if (!firstStart) {
                showFormError('first_start_date', '1st Semester start date is required');
                firstStartInput?.focus();
                return;
            }
            if (!firstEnd) {
                showFormError('first_end_date', '1st Semester end date is required');
                firstEndInput?.focus();
                return;
            }
            if (!secondStart) {
                showFormError('second_start_date', '2nd Semester start date is required');
                secondStartInput?.focus();
                return;
            }
            if (!secondEnd) {
                showFormError('second_end_date', '2nd Semester end date is required');
                secondEndInput?.focus();
                return;
            }

            const firstStartObj = new Date(firstStart);
            const firstEndObj = new Date(firstEnd);
            const secondStartObj = new Date(secondStart);
            const secondEndObj = new Date(secondEnd);

            if (isNaN(firstStartObj.getTime())) {
                showFormError('first_start_date', 'Invalid 1st Semester start date');
                firstStartInput?.focus();
                return;
            }
            if (isNaN(firstEndObj.getTime())) {
                showFormError('first_end_date', 'Invalid 1st Semester end date');
                firstEndInput?.focus();
                return;
            }
            if (firstStartObj > firstEndObj) {
                showFormError('first_end_date', '1st Semester end date must be after start date');
                firstEndInput?.focus();
                return;
            }

            if (isNaN(secondStartObj.getTime())) {
                showFormError('second_start_date', 'Invalid 2nd Semester start date');
                secondStartInput?.focus();
                return;
            }
            if (isNaN(secondEndObj.getTime())) {
                showFormError('second_end_date', 'Invalid 2nd Semester end date');
                secondEndInput?.focus();
                return;
            }
            if (secondStartObj > secondEndObj) {
                showFormError('second_end_date', '2nd Semester end date must be after start date');
                secondEndInput?.focus();
                return;
            }
        }

        try {
            // Disable submit button
            if (updateBtn) {
                updateBtn.disabled = true;
                if (updateBtnText) {
                    updateBtnText.textContent = 'Saving...';
                } else {
                    updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                }
            }

        if (isCreateBoth) {
                // Create BOTH 1st and 2nd semester for the given school year
                // 1st Semester will be created as active; 2nd as inactive
                const payloads = [
                    {
                        name: '1st Semester',
                        school_year: schoolYear,
                        start_date: firstStart,
                        end_date: firstEnd,
                        is_active: true,
                    },
                    {
                        name: '2nd Semester',
                        school_year: schoolYear,
                        start_date: secondStart,
                        end_date: secondEnd,
                        is_active: false,
                    }
                ];

                for (let i = 0; i < payloads.length; i++) {
                    const response = await fetch('/academic/semester', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payloads[i])
                    });

                    let result;
                    try {
                        result = await response.json();
                    } catch (e) {
                        console.error('Failed to parse response:', e);
                        throw new Error('Invalid response from server');
                    }

                    if (!response.ok) {
                        if (response.status === 422 && result.errors) {
                            displayValidationErrors(result.errors);
                            return;
                        }
                        const errorMessage = result.message || result.error || 'Failed to create semesters';
                        console.error('Create failed:', errorMessage, result);
                        throw new Error(errorMessage);
                    }
                }

                showNotification('1st and 2nd Semester created successfully!', 'success');
            } else if (isEditBoth) {
                // EDIT BOTH: update 1st and 2nd semester for the given school year
                const firstIdInput = document.getElementById('first_semester_id');
                const secondIdInput = document.getElementById('second_semester_id');
                const firstId = firstIdInput ? firstIdInput.value.trim() : null;
                const secondId = secondIdInput ? secondIdInput.value.trim() : null;

                const editPayloads = [];
                if (firstId) {
                    editPayloads.push({
                        id: firstId,
                        start_date: firstStart,
                        end_date: firstEnd
                    });
                }
                if (secondId) {
                    editPayloads.push({
                        id: secondId,
                        start_date: secondStart,
                        end_date: secondEnd
                    });
                }

                for (let i = 0; i < editPayloads.length; i++) {
                    const payload = editPayloads[i];
                    const response = await fetch(`/academic/semester/${payload.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            school_year: schoolYear,
                            start_date: payload.start_date,
                            end_date: payload.end_date
                        })
                    });

                    let result;
                    try {
                        result = await response.json();
                    } catch (e) {
                        console.error('Failed to parse response:', e);
                        throw new Error('Invalid response from server');
                    }

                    if (!response.ok) {
                        if (response.status === 422 && result.errors) {
                            displayValidationErrors(result.errors);
                            return;
                        }
                        const errorMessage = result.message || result.error || 'Failed to update semesters';
                        console.error('Update failed:', errorMessage, result);
                        throw new Error(errorMessage);
                    }
                }

                showNotification('Semesters updated successfully!', 'success');
            }
            
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
                if (updateBtnText) {
                    if (isCreateBoth) {
                        updateBtnText.textContent = 'Create Semester';
                    } else if (isEditBoth) {
                        updateBtnText.textContent = 'Update Semesters';
                    } else {
                        updateBtnText.textContent = 'Save';
                    }
                } else {
                    updateBtn.innerHTML = '<i class="ri-save-line align-middle me-1"></i>Save';
                }
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
     * @param {string|null} semesterType - 'first', 'second', or null
     */
    function configureQuarterSelectForSemester(semesterType) {
        const quarterSelectInstance = window['vanillaSelect_edit-quarter-select'];
        if (!quarterSelectInstance || typeof quarterSelectInstance.setOptions !== 'function') {
            console.warn('Quarter vanilla select instance not found');
            return;
        }

        let options = [];
        let defaultValue = [];

        if (semesterType === 'first') {
            options = [
                { value: '1', label: '1st Quarter' },
                { value: '2', label: '2nd Quarter' },
            ];
            defaultValue = ['1', '2'];
        } else if (semesterType === 'second') {
            options = [
                { value: '3', label: '3rd Quarter' },
                { value: '4', label: '4th Quarter' },
            ];
            defaultValue = ['3', '4'];
        } else {
            // Fallback: all four quarters (no default selection)
            options = [
                { value: '1', label: '1st Quarter' },
                { value: '2', label: '2nd Quarter' },
                { value: '3', label: '3rd Quarter' },
                { value: '4', label: '4th Quarter' },
            ];
            defaultValue = [];
        }

        // Set options and default value
        try {
            quarterSelectInstance.setOptions(options);
            if (typeof quarterSelectInstance.setValue === 'function') {
                quarterSelectInstance.setValue(defaultValue);
            }
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
     * Constrain all semester date pickers to the given school year (format: YYYY-YYYY).
     * For example "2026-2027" -> min: 2026-01-01, max: 2027-12-31.
     * If the format is invalid, any min/max constraints are cleared.
     */
    function updateDateLimitsForSchoolYear(schoolYear) {
        const pattern = /^\d{4}-\d{4}$/;
        const startInputs = [
            document.getElementById('start_date'),
            document.getElementById('first_start_date'),
            document.getElementById('second_start_date'),
        ];
        const endInputs = [
            document.getElementById('end_date'),
            document.getElementById('first_end_date'),
            document.getElementById('second_end_date'),
        ];

        if (!pattern.test((schoolYear || '').trim())) {
            // Clear limits if invalid format
            [...startInputs, ...endInputs].forEach(input => {
                if (input) {
                    input.removeAttribute('min');
                    input.removeAttribute('max');
                }
            });
            return;
        }

        const [fromStr, toStr] = schoolYear.split('-');
        const fromYear = parseInt(fromStr, 10);
        const toYear = parseInt(toStr, 10);
        if (Number.isNaN(fromYear) || Number.isNaN(toYear)) {
            return;
        }

        const min = `${fromYear}-01-01`;
        const max = `${toYear}-12-31`;

        startInputs.forEach(input => {
            if (input) {
                input.min = min;
                input.max = max;
                // If current value is outside range, clear it
                if (input.value && (input.value < min || input.value > max)) {
                    input.value = '';
                }
            }
        });

        endInputs.forEach(input => {
            if (input) {
                input.min = min;
                input.max = max;
                if (input.value && (input.value < min || input.value > max)) {
                    input.value = '';
                }
            }
        });
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

        // Also clear any min/max limits on date pickers so that the next
        // time the modal is opened, limits will be re-applied from the
        // current school year value.
        const dateInputs = [
            'start_date',
            'end_date',
            'first_start_date',
            'first_end_date',
            'second_start_date',
            'second_end_date',
        ];
        dateInputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.removeAttribute('min');
                input.removeAttribute('max');
            }
        });
    }

    /**
     * Helper: map semester name to type key
     * @param {string} name
     * @returns {string|null} 'first', 'second', or null
     */
    function getSemesterTypeFromName(name) {
        if (!name) return null;
        const lower = name.toLowerCase();
        if (lower.includes('1st') || lower.includes('first')) {
            return 'first';
        }
        if (lower.includes('2nd') || lower.includes('second')) {
            return 'second';
        }
        return null;
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
