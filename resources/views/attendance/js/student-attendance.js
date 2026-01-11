/**
 * Student Attendance Management
 * 
 * This script handles all client-side operations for student attendance including:
 * - Viewing student attendance records
 * - Managing attendance status
 * - Time tracking functionality
 * - Student attendance statistics
 */

(function () {
    'use strict';

    // API endpoints
    const API = {
        BASE: '/attendance',
        GET_STUDENT_ATTENDANCES: (id) => `/attendance/view/students?id=${id}`,
        TIME_IN: '/attendance/students/time-in',
        TIME_OUT: '/attendance/students/time-out',
    };
    
    // Get attendance ID from URL query parameter or window variable
    function getAttendanceId() {
        // First try to get from window variable (set by blade template)
        if (window.currentAttendanceId) {
            return window.currentAttendanceId;
        }
        // Fallback to URL query parameter
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id');
    }
    
    // Get student attendance ID from page data (if available)
    function getStudentAttendanceId() {
        // This will be set from the blade template
        return window.currentStudentAttendanceId || null;
    }

    // Get CSRF token
    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            console.error('CSRF token not found in meta tag');
        }
        return token;
    }

    /**
     * Format UTC time string to Manila timezone (Asia/Manila, UTC+8)
     * @param {string} utcTimeString - ISO 8601 UTC time string
     * @param {object} options - Formatting options
     * @returns {string} Formatted time string
     */
    function formatTimeToManila(utcTimeString, options = {}) {
        if (!utcTimeString) return null;
        
        try {
            // Parse the UTC time string (JavaScript Date parses ISO 8601 UTC strings correctly)
            const utcDate = new Date(utcTimeString);
            
            // Check if date is valid
            if (isNaN(utcDate.getTime())) {
                console.error('Invalid date:', utcTimeString);
                return null;
            }
            
            // Format directly using toLocaleTimeString with timeZone option
            // This automatically converts UTC to Manila timezone
            const defaultOptions = {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
                timeZone: 'Asia/Manila',
                ...options
            };
            
            return utcDate.toLocaleTimeString('en-US', defaultOptions);
        } catch (error) {
            console.error('Error formatting time to Manila:', error, utcTimeString);
            return null;
        }
    }

    /**
     * Format UTC date string to Manila timezone
     * @param {string} utcTimeString - ISO 8601 UTC time string
     * @returns {string} Formatted date string (e.g., "Dec 14, 2025")
     */
    function formatDateToManila(utcTimeString) {
        if (!utcTimeString) return null;
        
        try {
            const utcDate = new Date(utcTimeString);
            if (isNaN(utcDate.getTime())) {
                return null;
            }
            
            // Format directly using toLocaleDateString with timeZone option
            // This automatically converts UTC to Manila timezone
            return utcDate.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                timeZone: 'Asia/Manila'
            });
        } catch (error) {
            console.error('Error formatting date to Manila:', error);
            return null;
        }
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    // Time Entries state
    let timeEntriesData = [];
    let filteredTimeEntries = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    // Digital clock state
    let digitalClockInterval = null;
    let currentClockTime = null; // Store current clock time for time in/out

    function initialize() {
        console.log('Student Attendance page initialized');
        
        // Initialize digital clock
        initializeDigitalClock();
        
        // Initialize time tracking display
        initializeTimeTracking();
        
        // Initialize time tracking buttons
        initializeTimeTrackingButtons();
        
        // Load and display students
        loadStudentsList();
        
        // Load time entries
        loadTimeEntries();
        
        // Initialize search for time entries
        initializeTimeEntriesSearch();
        
        // Initialize Flatpickr if needed
        initializeFlatpickr();
        
        // Update time entries tab label on load
        updateTimeEntriesTabLabelOnLoad();
    }
    
    /**
     * Initialize and start the digital clock
     */
    function initializeDigitalClock() {
        const clockTimeElement = document.getElementById('clockTime');
        const clockAmPmElement = document.getElementById('clockAmPm');
        const clockDateElement = document.getElementById('clockDate');
        
        if (!clockTimeElement || !clockAmPmElement || !clockDateElement) {
            return;
        }
        
        // Update clock immediately
        updateDigitalClock();
        
        // Update clock every second
        digitalClockInterval = setInterval(updateDigitalClock, 1000);
    }
    
    /**
     * Update the digital clock display
     */
    function updateDigitalClock() {
        const clockTimeElement = document.getElementById('clockTime');
        const clockAmPmElement = document.getElementById('clockAmPm');
        const clockDateElement = document.getElementById('clockDate');
        
        if (!clockTimeElement || !clockAmPmElement || !clockDateElement) {
            return;
        }
        
        const now = new Date();
        
        // Format time (HH:mm:ss)
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        // Format 12-hour time for display
        const hours12 = now.getHours() % 12 || 12;
        const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
        const timeDisplay = `${String(hours12).padStart(2, '0')}:${minutes}:${seconds}`;
        
        // Format date
        const dateOptions = { month: 'short', day: 'numeric', year: 'numeric' };
        const dateDisplay = now.toLocaleDateString('en-US', dateOptions);
        
        // Update display
        clockTimeElement.textContent = timeDisplay;
        clockAmPmElement.textContent = ampm;
        clockDateElement.textContent = dateDisplay;
        
        // Store current time for time in/out (UTC ISO format)
        currentClockTime = now.toISOString();
        
        // Also store local time components
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const localTimeString = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        
        // Store in window for access
        window.currentClockTime = {
            utc: currentClockTime,
            local: localTimeString,
            timezoneOffset: -now.getTimezoneOffset()
        };
    }
    
    /**
     * Get current clock time for time in/out
     * This captures the exact time from the digital clock at the moment it's called
     * (which is when the user confirms Time In/Out)
     */
    function getCurrentClockTime() {
        // Capture the time at this exact moment (when user confirms)
        // This ensures we get the most accurate time from the digital clock
        const now = new Date();
        
        // Get UTC time in ISO format (this is what the digital clock displays)
        const utcTime = now.toISOString();
        
        // Get local time components (matching what's displayed on the clock)
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        const localTimeString = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        
        // Return the time that matches what's displayed on the digital clock
        return {
            utc: utcTime, // UTC time in ISO 8601 format (for server)
            local: localTimeString, // Local time string (for reference/logging)
            timezoneOffset: -now.getTimezoneOffset() // Timezone offset in minutes
        };
    }
    
    /**
     * Update Time Entries tab label on page load
     */
    function updateTimeEntriesTabLabelOnLoad() {
        // Calculate total duration from all time entries
        if (timeEntriesData.length > 0) {
            const totalMinutes = timeEntriesData.reduce((sum, entry) => {
                return sum + (entry.duration_minutes || 0);
            }, 0);
            if (totalMinutes > 0) {
                updateTimeEntriesTabLabel(totalMinutes);
            }
        }
    }

    /**
     * Initialize time tracking display
     */
    function initializeTimeTracking() {
        if (!window.attendanceData) {
            // No attendance data, show no attendance message
            document.getElementById('noAttendanceMessage').style.display = 'block';
            return;
        }
        
        const attendance = window.attendanceData;
        const studentAttendance = window.studentAttendanceData; // Must exist to show buttons
        const alertContainer = document.getElementById('timeTrackingAlertContainer');
        const timeInBtn = document.getElementById('timeInBtn');
        const timeOutBtn = document.getElementById('timeOutBtn');
        const startTimeElement = document.getElementById('startTime');
        const endTimeElement = document.getElementById('endTime');
        const durationElement = document.getElementById('timeDuration');
        const attendanceTitleElement = document.getElementById('attendanceTitle');
        const noAttendanceMessage = document.getElementById('noAttendanceMessage');
        
        // Clear any existing alerts
        alertContainer.innerHTML = '';
        
        // Check if user has a student attendance record - required to show buttons
        if (!studentAttendance) {
            // No student attendance record, show no attendance message
            if (noAttendanceMessage) {
                noAttendanceMessage.style.display = 'block';
            }
            // Hide buttons
            if (timeInBtn) {
                timeInBtn.style.display = 'none';
                timeInBtn.disabled = true;
            }
            if (timeOutBtn) {
                timeOutBtn.style.display = 'none';
                timeOutBtn.disabled = true;
            }
            return;
        }
        
        // Hide no attendance message if student attendance exists
        if (noAttendanceMessage) {
            noAttendanceMessage.style.display = 'none';
        }
        
        // Format and display times
        let startTime = 'N/A';
        let endTime = 'N/A';
        let duration = 'N/A';
        
        if (attendance.start_time) {
            startTime = formatTimeToManila(attendance.start_time) || 'N/A';
        }
        
        if (attendance.end_time) {
            endTime = formatTimeToManila(attendance.end_time) || 'N/A';
        }
        
        // Calculate duration from attendance start/end times
        if (attendance.start_time && attendance.end_time) {
            const start = new Date(attendance.start_time);
            const end = new Date(attendance.end_time);
            const diffMs = end - start;
            const hours = Math.floor(diffMs / (1000 * 60 * 60));
            const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
            duration = hours > 0 ? `${hours} hrs ${minutes} min` : `${minutes} min`;
        }
        
        // Update display elements
        if (startTimeElement) startTimeElement.textContent = startTime;
        if (endTimeElement) endTimeElement.textContent = endTime;
        if (durationElement) durationElement.textContent = duration;
        if (attendanceTitleElement) attendanceTitleElement.textContent = attendance.category_name;
        
        // Check if attendance date has passed
        const attendanceDate = new Date(attendance.date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        attendanceDate.setHours(0, 0, 0, 0);
        const isDatePassed = attendanceDate < today;
        
        // Check if already time in/out (only if student attendance record exists)
        const hasCheckedIn = studentAttendance && studentAttendance.check_in_time !== null && studentAttendance.check_in_time !== '';
        const hasCheckedOut = studentAttendance && studentAttendance.check_out_time !== null && studentAttendance.check_out_time !== '';
        const status = studentAttendance ? (studentAttendance.status || '') : '';
        
        // Check if user is late
        let isLate = false;
        let lateDuration = '';
        if (attendance.start_time && hasCheckedIn && studentAttendance) {
            // Parse times as UTC (server sends ISO 8601 format with 'Z' suffix)
            const attendanceStart = new Date(attendance.start_time);
            const checkInTime = new Date(studentAttendance.check_in_time);
            if (checkInTime > attendanceStart) {
                isLate = true;
                const lateMs = checkInTime - attendanceStart;
                const lateHours = Math.floor(lateMs / (1000 * 60 * 60));
                const lateMinutes = Math.floor((lateMs % (1000 * 60 * 60)) / (1000 * 60));
                if (lateHours > 0) {
                    lateDuration = `${lateHours} ${lateHours === 1 ? 'hour' : 'hours'}`;
                    if (lateMinutes > 0) {
                        lateDuration += ` ${lateMinutes} ${lateMinutes === 1 ? 'minute' : 'minutes'}`;
                    }
                } else {
                    lateDuration = `${lateMinutes} ${lateMinutes === 1 ? 'minute' : 'minutes'}`;
                }
            }
        } else if (attendance.start_time && !hasCheckedIn) {
            const attendanceStart = new Date(attendance.start_time);
            const now = new Date();
            if (now > attendanceStart) {
                isLate = true;
                const lateMs = now - attendanceStart;
                const lateHours = Math.floor(lateMs / (1000 * 60 * 60));
                const lateMinutes = Math.floor((lateMs % (1000 * 60 * 60)) / (1000 * 60));
                if (lateHours > 0) {
                    lateDuration = `${lateHours} ${lateHours === 1 ? 'hour' : 'hours'}`;
                    if (lateMinutes > 0) {
                        lateDuration += ` ${lateMinutes} ${lateMinutes === 1 ? 'minute' : 'minutes'}`;
                    }
                } else {
                    lateDuration = `${lateMinutes} ${lateMinutes === 1 ? 'minute' : 'minutes'}`;
                }
            }
        }
        
        // Show alerts and handle button states
        if (isDatePassed) {
            // Date has passed - show appropriate alert
            if (status === 'absent' || (!hasCheckedIn && !hasCheckedOut)) {
                // Show absent alert
                alertContainer.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="ri-error-warning-line align-bottom me-2"></i>
                        <strong>Attendance Date Has Passed</strong>
                        <span class="d-block mt-1">You were marked as absent for this attendance session.</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            } else {
                // Show date passed alert
                alertContainer.innerHTML = `
                    <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                        <i class="ri-information-line align-bottom me-2"></i>
                        <strong>Attendance Date Has Passed</strong>
                        <span class="d-block mt-1">Time tracking is no longer available for this session.</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }
            // Disable both buttons
            if (timeInBtn) {
                timeInBtn.style.display = 'none';
                timeInBtn.disabled = true;
            }
            if (timeOutBtn) {
                timeOutBtn.style.display = 'none';
                timeOutBtn.disabled = true;
            }
        } else {
            // Date hasn't passed - show late alert if applicable
            if (isLate && lateDuration) {
                alertContainer.innerHTML = `
                    <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                        <i class="ri-time-warning-line align-bottom me-2"></i>
                        <strong>You are late!</strong>
                        <span class="d-block mt-1">You are ${lateDuration} late.</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            }
            
            // Handle button states based on check-in/out status
            if (hasCheckedIn && !hasCheckedOut) {
                // Already checked in, can only check out
                if (timeInBtn) {
                    timeInBtn.style.display = 'none';
                    timeInBtn.disabled = true;
                }
                if (timeOutBtn) {
                    timeOutBtn.style.display = 'block';
                    timeOutBtn.disabled = false;
                }
            } else if (hasCheckedIn && hasCheckedOut) {
                // Already checked in and out
                if (timeInBtn) {
                    timeInBtn.style.display = 'none';
                    timeInBtn.disabled = true;
                }
                if (timeOutBtn) {
                    timeOutBtn.style.display = 'none';
                    timeOutBtn.disabled = true;
                }
                alertContainer.innerHTML = `
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="ri-checkbox-circle-line align-bottom me-2"></i>
                        <strong>Time Tracking Complete</strong>
                        <span class="d-block mt-1">You have already completed time in and time out for this session.</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
            } else {
                // Not checked in yet, can only check in
                // Only show if student attendance record exists
                if (timeInBtn) {
                    timeInBtn.style.display = 'block';
                    timeInBtn.disabled = false;
                }
                if (timeOutBtn) {
                    timeOutBtn.style.display = 'none';
                    timeOutBtn.disabled = true;
                }
            }
        }
    }

    /**
     * Initialize time tracking buttons (Time In/Time Out)
     */
    function initializeTimeTrackingButtons() {
        const timeInBtn = document.getElementById('timeInBtn');
        const timeOutBtn = document.getElementById('timeOutBtn');
        
        if (timeInBtn) {
            timeInBtn.addEventListener('click', handleTimeIn);
        }
        
        if (timeOutBtn) {
            timeOutBtn.addEventListener('click', handleTimeOut);
        }
    }

    /**
     * Handle Time In button click - Show confirmation modal
     */
    function handleTimeIn() {
        const attendanceId = getAttendanceId();
        const studentAttendanceId = getStudentAttendanceId();
        
        if (!attendanceId) {
            showToast('Error', 'Missing attendance information', 'error');
            return;
        }
        
        if (!studentAttendanceId) {
            showToast('Error', 'You are not enrolled in this attendance session', 'error');
            return;
        }
        
        // Show the Time In confirmation modal
        const timeInModal = new bootstrap.Modal(document.getElementById('timeInModal'));
        timeInModal.show();
        
        // Set up confirm button handler
        const confirmBtn = document.getElementById('confirmTimeInBtn');
        if (confirmBtn) {
            // Remove any existing event listeners by cloning the button
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            newConfirmBtn.addEventListener('click', function() {
                // Get time from digital clock (what user sees on screen)
                const clockTime = getCurrentClockTime();
                
                console.log('Time In - Clock Time UTC:', clockTime.utc, 'Clock Time Local:', clockTime.local, 'Timezone Offset:', clockTime.timezoneOffset);
                timeInModal.hide();
                performTimeIn(attendanceId, studentAttendanceId, clockTime.utc, clockTime.local, clockTime.timezoneOffset);
            });
        }
    }

    /**
     * Handle Time Out button click - Show confirmation modal
     */
    function handleTimeOut() {
        const attendanceId = getAttendanceId();
        const studentAttendanceId = getStudentAttendanceId();
        
        if (!attendanceId) {
            showToast('Error', 'Missing attendance information', 'error');
            return;
        }
        
        if (!studentAttendanceId) {
            showToast('Error', 'Please time in first before timing out', 'error');
            return;
        }
        
        // Show the Time Out confirmation modal
        const timeOutModal = new bootstrap.Modal(document.getElementById('timeOutModal'));
        timeOutModal.show();
        
        // Set up confirm button handler
        const confirmBtn = document.getElementById('confirmTimeOutBtn');
        if (confirmBtn) {
            // Remove any existing event listeners by cloning the button
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            
            newConfirmBtn.addEventListener('click', function() {
                // Get time from digital clock (what user sees on screen)
                const clockTime = getCurrentClockTime();
                
                console.log('Time Out - Clock Time UTC:', clockTime.utc, 'Clock Time Local:', clockTime.local, 'Timezone Offset:', clockTime.timezoneOffset);
                timeOutModal.hide();
                performTimeOut(attendanceId, studentAttendanceId, clockTime.utc, clockTime.local, clockTime.timezoneOffset);
            });
        }
    }

    /**
     * Perform Time In API call
     * @param {number} attendanceId - The attendance session ID
     * @param {number|null} studentAttendanceId - The student attendance record ID (can be null if not enrolled yet)
     * @param {string} clientTime - The client's UTC time in ISO 8601 format (primary)
     * @param {string} localTimeString - The client's local time in YYYY-MM-DD HH:mm:ss format (for reference)
     * @param {number} timezoneOffset - The timezone offset in minutes
     */
    function performTimeIn(attendanceId, studentAttendanceId, clientTime, localTimeString, timezoneOffset) {
        const timeInBtn = document.getElementById('timeInBtn');
        if (timeInBtn) {
            timeInBtn.disabled = true;
            timeInBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
        }
        
        // Use the UTC time passed from the confirmation handler
        // If not provided, get current UTC time as fallback
        if (!clientTime) {
            const now = new Date();
            clientTime = now.toISOString();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            localTimeString = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
            timezoneOffset = -now.getTimezoneOffset();
        }
        
        console.log('Time In - UTC Time (ISO):', clientTime, 'Local Time:', localTimeString, 'Offset:', timezoneOffset);
        
        // Build request body - student_attendance_id is required
        const requestBody = {
            attendance_id: attendanceId,
            student_attendance_id: studentAttendanceId, // Required - must be enrolled
            client_time: clientTime, // Send UTC time as primary (ISO 8601)
            local_time: localTimeString, // Local time for reference
            timezone_offset: timezoneOffset
        };
        
        fetch(API.TIME_IN, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update window variables with new student attendance data
                if (data.data && data.data.student_attendance_id) {
                    window.currentStudentAttendanceId = data.data.student_attendance_id;
                }
                showToast('Success', data.message || 'Time in recorded successfully', 'success');
                // Reload page to update the display
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to record time in');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', error.message || 'Failed to record time in', 'error');
            if (timeInBtn) {
                timeInBtn.disabled = false;
                timeInBtn.innerHTML = '<i class="ri-login-circle-line align-bottom me-1"></i>Time In';
            }
        });
    }

    /**
     * Perform Time Out API call
     * @param {number} attendanceId - The attendance session ID
     * @param {number|null} studentAttendanceId - The student attendance record ID (required for time out)
     * @param {string} clientTime - The client's UTC time in ISO 8601 format (primary)
     * @param {string} localTimeString - The client's local time in YYYY-MM-DD HH:mm:ss format (for reference)
     * @param {number} timezoneOffset - The timezone offset in minutes
     */
    function performTimeOut(attendanceId, studentAttendanceId, clientTime, localTimeString, timezoneOffset) {
        const timeOutBtn = document.getElementById('timeOutBtn');
        if (timeOutBtn) {
            timeOutBtn.disabled = true;
            timeOutBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
        }
        
        // Use the UTC time passed from the confirmation handler
        // If not provided, get current UTC time as fallback
        if (!clientTime) {
            const now = new Date();
            clientTime = now.toISOString();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            localTimeString = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
            timezoneOffset = -now.getTimezoneOffset();
        }
        
        console.log('Time Out - UTC Time (ISO):', clientTime, 'Local Time:', localTimeString, 'Offset:', timezoneOffset);
        
        // Build request body - student_attendance_id is required for time out
        const requestBody = {
            attendance_id: attendanceId,
            client_time: clientTime, // Send UTC time as primary (ISO 8601)
            local_time: localTimeString, // Local time for reference
            timezone_offset: timezoneOffset
        };
        
        // Include student_attendance_id if available
        if (studentAttendanceId) {
            requestBody.student_attendance_id = studentAttendanceId;
        }
        
        fetch(API.TIME_OUT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message || 'Time out recorded successfully', 'success');
                // Reload page to update the display
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(data.message || 'Failed to record time out');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', error.message || 'Failed to record time out', 'error');
            if (timeOutBtn) {
                timeOutBtn.disabled = false;
                timeOutBtn.innerHTML = '<i class="ri-logout-circle-line align-bottom me-1"></i>Time Out';
            }
        });
    }

    /**
     * Load and display students list
     */
    function loadStudentsList() {
        const attendanceId = getAttendanceId();
        const container = document.getElementById('studentsListContainer');
        
        if (!attendanceId) {
            if (container) {
                container.innerHTML = '<div class="text-center py-3"><p class="text-muted mb-0">No attendance session selected.</p></div>';
            }
            return;
        }
        
        if (!container) {
            console.error('studentsListContainer element not found');
            return;
        }
        
        const apiUrl = API.GET_STUDENT_ATTENDANCES(attendanceId);
        console.log('Loading students from:', apiUrl);
        
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(response => {
            console.log('Response status:', response.status, response.statusText);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Error response:', text);
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success && data.data && data.data.student_attendances) {
                console.log('Found', data.data.student_attendances.length, 'students');
                renderStudentsList(data.data.student_attendances);
            } else {
                console.error('Invalid response format:', data);
                throw new Error('Invalid response format');
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            if (container) {
                container.innerHTML = '<div class="text-center py-3"><p class="text-danger mb-0">Failed to load students. Please refresh the page.</p></div>';
            }
        });
    }

    /**
     * Render students list in the container
     */
    function renderStudentsList(students) {
        const container = document.getElementById('studentsListContainer');
        if (!container) return;
        
        if (!students || students.length === 0) {
            container.innerHTML = '<div class="text-center py-3"><p class="text-muted mb-0">No students found.</p></div>';
            return;
        }
        
        let html = '<ul class="list-unstyled vstack gap-2 mb-0">';
        
        students.forEach(function(student) {
            const studentName = escapeHtml(student.student_name || 'N/A');
            const studentId = escapeHtml(student.student_id || 'N/A');
            const status = student.status || 'pending';
            
            // Get image URL or use default
            const imageUrl = student.image_url || '/build/images/users/user-dummy-img.jpg';
            const hasImage = student.image_url && student.image_url !== null;
            
            // Get status badge class
            let badgeClass = 'bg-secondary-subtle text-secondary';
            if (status === 'present') {
                badgeClass = 'bg-success-subtle text-success';
            } else if (status === 'absent') {
                badgeClass = 'bg-danger-subtle text-danger';
            } else if (status === 'late') {
                badgeClass = 'bg-warning-subtle text-warning';
            } else if (status === 'pending') {
                badgeClass = 'bg-info-subtle text-info';
            } else if (status === 'excused') {
                badgeClass = 'bg-primary-subtle text-primary';
            }
            
            // Format check-in time (parse UTC time correctly)
            let checkInTime = '-';
            if (student.check_in_time) {
                // Server sends ISO 8601 format (UTC), convert to Manila timezone
                checkInTime = formatTimeToManila(student.check_in_time) || '-';
            }
            
            // Create avatar HTML - use image if available, otherwise use initial
            let avatarHtml = '';
            if (hasImage) {
                avatarHtml = `<img src="${escapeHtml(imageUrl)}" alt="${escapeHtml(studentName)}" class="avatar-xs rounded-circle material-shadow" onerror="this.onerror=null; this.src='/build/images/users/user-dummy-img.jpg'; this.parentElement.innerHTML='<div class=\\'avatar-title rounded-circle bg-primary-subtle text-primary\\'>${(studentName.charAt(0) || '?').toUpperCase()}</div>';" />`;
            } else {
                avatarHtml = `<div class="avatar-title rounded-circle bg-primary-subtle text-primary">${(studentName.charAt(0) || '?').toUpperCase()}</div>`;
            }
            
            html += `
                <li>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-xs">
                                ${avatarHtml}
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-2">
                            <h6 class="mb-0 fs-13">${studentName}</h6>
                            <p class="text-muted mb-0 fs-11">${studentId}</p>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="badge ${badgeClass} fs-11">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
                            ${checkInTime !== '-' ? `<small class="d-block text-muted mt-1 fs-10">${checkInTime}</small>` : ''}
                        </div>
                    </div>
                </li>
            `;
        });
        
        html += '</ul>';
        container.innerHTML = html;
    }

    /**
     * Update Time Entries tab label with duration
     */
    function updateTimeEntriesTabLabel(durationMinutes) {
        const durationSpan = document.getElementById('timeEntriesTabDuration');
        if (!durationSpan) return;
        
        let durationText = '(0 min)';
        if (durationMinutes && durationMinutes > 0) {
            const hours = Math.floor(durationMinutes / 60);
            const minutes = durationMinutes % 60;
            durationText = hours > 0 ? `(${hours} hrs ${minutes} min)` : `(${minutes} min)`;
        }
        
        durationSpan.textContent = durationText;
    }

    /**
     * Update Time Entries tab label on page load
     */
    function updateTimeEntriesTabLabelOnLoad() {
        // Calculate total duration from all time entries
        if (timeEntriesData.length > 0) {
            const totalMinutes = timeEntriesData.reduce((sum, entry) => {
                return sum + (entry.duration_minutes || 0);
            }, 0);
            if (totalMinutes > 0) {
                updateTimeEntriesTabLabel(totalMinutes);
            }
        }
    }

    /**
     * Load time entries data
     */
    function loadTimeEntries() {
        const attendanceId = getAttendanceId();
        const studentAttendanceId = getStudentAttendanceId();
        
        if (!attendanceId) {
            renderTimeEntriesTable();
            return;
        }
        
        // If we have student attendance ID, we can get the data from the API
        // For now, we'll use the data from getStudentAttendances API
        const apiUrl = API.GET_STUDENT_ATTENDANCES(attendanceId);
        
        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch time entries');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.data && data.data.student_attendances) {
                // Load all student attendances (not just current user)
                // Filter to only show entries that have check_in_time (actual time entries)
                timeEntriesData = data.data.student_attendances.filter(entry => {
                    return entry.check_in_time !== null && entry.check_in_time !== '';
                });
                
                // Apply current filter and render
                filterTimeEntries();
                renderTimeEntriesTable();
                updateTimeEntriesPagination();
                updateTimeEntriesTabLabelOnLoad();
            } else {
                timeEntriesData = [];
                renderTimeEntriesTable();
            }
        })
        .catch(error => {
            console.error('Error loading time entries:', error);
            timeEntriesData = [];
            renderTimeEntriesTable();
        });
    }

    /**
     * Filter time entries based on search query
     */
    function filterTimeEntries() {
        const searchInput = document.getElementById('timeEntriesSearch');
        const searchQuery = searchInput ? searchInput.value.toLowerCase().trim() : '';
        
        if (!searchQuery) {
            filteredTimeEntries = [...timeEntriesData];
        } else {
            filteredTimeEntries = timeEntriesData.filter(entry => {
                const studentName = (entry.student_name || '').toLowerCase();
                const date = entry.check_in_time ? (formatDateToManila(entry.check_in_time) || '').toLowerCase() : '';
                const timeIn = entry.check_in_time ? (formatTimeToManila(entry.check_in_time) || '').toLowerCase() : '';
                const timeOut = entry.check_out_time ? (formatTimeToManila(entry.check_out_time) || '').toLowerCase() : '';
                const status = (entry.status || '').toLowerCase();
                
                return studentName.includes(searchQuery) ||
                       date.includes(searchQuery) ||
                       timeIn.includes(searchQuery) ||
                       timeOut.includes(searchQuery) ||
                       status.includes(searchQuery);
            });
        }
        
        currentPage = 1; // Reset to first page when filtering
    }

    /**
     * Render time entries table
     */
    function renderTimeEntriesTable() {
        const tableBody = document.getElementById('timeEntriesTableBody');
        if (!tableBody) return;
        
        if (filteredTimeEntries.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <p class="text-muted mb-0">No time entries found. Please time in to start tracking.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        // Calculate pagination
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const paginatedEntries = filteredTimeEntries.slice(startIndex, endIndex);
        
        let html = '';
        
        paginatedEntries.forEach(function(entry) {
            const studentName = escapeHtml(entry.student_name || 'N/A');
            const imageUrl = entry.image_url || '/build/images/users/user-dummy-img.jpg';
            
            // Format dates and times
            const checkInDate = entry.check_in_time
                ? formatDateToManila(entry.check_in_time) || '-'
                : '-';
            const checkInTime = entry.check_in_time
                ? formatTimeToManila(entry.check_in_time) || '-'
                : '-';
            const checkOutTime = entry.check_out_time
                ? formatTimeToManila(entry.check_out_time) || '-'
                : '-';
            
            // Format duration
            let durationText = '-';
            if (entry.duration_minutes) {
                const hours = Math.floor(entry.duration_minutes / 60);
                const minutes = entry.duration_minutes % 60;
                durationText = hours > 0 ? `${hours} hrs ${minutes} min` : `${minutes} min`;
            } else if (entry.check_in_time && entry.check_out_time) {
                const checkIn = new Date(entry.check_in_time);
                const checkOut = new Date(entry.check_out_time);
                const diffMs = checkOut - checkIn;
                const hours = Math.floor(diffMs / (1000 * 60 * 60));
                const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                durationText = hours > 0 ? `${hours} hrs ${minutes} min` : `${minutes} min`;
            }
            
            // Status badge
            const status = entry.status || 'pending';
            let badgeClass = 'bg-secondary-subtle text-secondary';
            if (status === 'present') {
                badgeClass = 'bg-success-subtle text-success';
            } else if (status === 'absent') {
                badgeClass = 'bg-danger-subtle text-danger';
            } else if (status === 'late') {
                badgeClass = 'bg-warning-subtle text-warning';
            } else if (status === 'pending') {
                badgeClass = 'bg-info-subtle text-info';
            } else if (status === 'excused') {
                badgeClass = 'bg-primary-subtle text-primary';
            }
            
            html += `
                <tr>
                    <th scope="row">
                        <div class="d-flex align-items-center">
                            <img src="${escapeHtml(imageUrl)}" alt="${studentName}"
                                class="rounded-circle avatar-xxs"
                                onerror="this.onerror=null; this.src='/build/images/users/user-dummy-img.jpg';">
                            <div class="flex-grow-1 ms-2">
                                <span class="fw-medium">${studentName}</span>
                            </div>
                        </div>
                    </th>
                    <td>${escapeHtml(checkInDate)}</td>
                    <td>${escapeHtml(checkInTime)}</td>
                    <td>${escapeHtml(checkOutTime)}</td>
                    <td>${escapeHtml(durationText)}</td>
                    <td>
                        <span class="badge ${badgeClass} fs-11">
                            ${escapeHtml(status.charAt(0).toUpperCase() + status.slice(1))}
                        </span>
                    </td>
                </tr>
            `;
        });
        
        tableBody.innerHTML = html;
    }

    /**
     * Update pagination controls
     */
    function updateTimeEntriesPagination() {
        const paginationContainer = document.getElementById('timeEntriesPaginationList');
        const summaryElement = document.getElementById('timeEntriesSummary');
        
        if (!paginationContainer) return;
        
        const totalItems = filteredTimeEntries.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        const startItem = totalItems === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const endItem = Math.min(currentPage * itemsPerPage, totalItems);
        
        // Update summary
        if (summaryElement) {
            summaryElement.textContent = `Showing ${startItem} to ${endItem} of ${totalItems} entries`;
        }
        
        // Clear pagination
        paginationContainer.innerHTML = '';
        
        if (totalPages <= 1) {
            return; // Don't show pagination if only one page
        }
        
        // Previous button
        const prevDisabled = currentPage === 1 ? 'disabled' : '';
        paginationContainer.innerHTML += `
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}" ${prevDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>
                    <i class="mdi mdi-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                const active = i === currentPage ? 'active' : '';
                paginationContainer.innerHTML += `
                    <li class="page-item ${active}">
                        <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                    </li>
                `;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                paginationContainer.innerHTML += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            }
        }
        
        // Next button
        const nextDisabled = currentPage === totalPages ? 'disabled' : '';
        paginationContainer.innerHTML += `
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}" ${nextDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>
                    <i class="mdi mdi-chevron-right"></i>
                </a>
            </li>
        `;
        
        // Attach event listeners
        paginationContainer.querySelectorAll('a[data-page]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page && page !== currentPage && page >= 1 && page <= totalPages) {
                    currentPage = page;
                    renderTimeEntriesTable();
                    updateTimeEntriesPagination();
                    // Scroll to top of table
                    const table = document.querySelector('#profile-1 .table-responsive');
                    if (table) {
                        table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    }

    /**
     * Initialize search for time entries
     */
    function initializeTimeEntriesSearch() {
        const searchInput = document.getElementById('timeEntriesSearch');
        if (!searchInput) return;
        
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                filterTimeEntries();
                renderTimeEntriesTable();
                updateTimeEntriesPagination();
            }, 300); // Debounce search
        });
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Show toast notification (fallback if needed)
     */
    function showToast(title, message, type) {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type, title);
        } else if (typeof window.dispatchEvent === 'function') {
            window.dispatchEvent(new CustomEvent('show-toast', {
                detail: {
                    title: title,
                    message: message,
                    type: type
                }
            }));
        } else {
            alert(message);
        }
    }

    /**
     * Initialize Flatpickr for date/time inputs
     */
    function initializeFlatpickr() {
        // Wait for Flatpickr to be available
        if (typeof flatpickr === 'undefined') {
            // If Flatpickr is not loaded yet, wait a bit and try again
            setTimeout(initializeFlatpickr, 100);
            return;
        }

        // Initialize Flatpickr on any inputs with data-provider="flatpickr" attribute
        const flatpickrInputs = document.querySelectorAll('[data-provider="flatpickr"]');
        
        flatpickrInputs.forEach(function(input) {
            // Check if already initialized
            if (input._flatpickr) {
                return;
            }

            const options = {
                dateFormat: 'm/d/Y',
                time_24hr: false,
                enableTime: input.hasAttribute('data-time') || input.type === 'time',
                noCalendar: input.type === 'time',
            };

            // Check for additional data attributes
            if (input.hasAttribute('data-date-format')) {
                options.dateFormat = input.getAttribute('data-date-format');
            }

            if (input.hasAttribute('data-range-date')) {
                options.mode = 'range';
            }

            if (input.hasAttribute('data-enable-time')) {
                options.enableTime = true;
            }

            try {
                flatpickr(input, options);
                console.log('Flatpickr initialized on:', input);
            } catch (error) {
                console.warn('Failed to initialize Flatpickr on input:', input, error);
            }
        });

        // Also initialize on any input[type="date"] or input[type="time"] if needed
        const dateTimeInputs = document.querySelectorAll('input[type="date"], input[type="time"]');
        dateTimeInputs.forEach(function(input) {
            if (input._flatpickr || input.hasAttribute('data-provider')) {
                return; // Skip if already initialized or has data-provider
            }

            // Only initialize if it doesn't have native date/time support or if explicitly needed
            // For now, we'll skip native inputs unless they have a specific class or data attribute
            if (input.classList.contains('flatpickr-input') || input.hasAttribute('data-flatpickr')) {
                const options = {
                    dateFormat: input.type === 'date' ? 'm/d/Y' : 'h:i K',
                    time_24hr: false,
                    enableTime: input.type === 'time',
                    noCalendar: input.type === 'time',
                };

                try {
                    flatpickr(input, options);
                } catch (error) {
                    console.warn('Failed to initialize Flatpickr on date/time input:', input, error);
                }
            }
        });
    }

})();
