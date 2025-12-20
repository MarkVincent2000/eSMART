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
        GET_COMMENTS: '/attendance/comments',
        POST_COMMENT: '/attendance/comments',
        UPDATE_COMMENT: (id) => `/attendance/comments/${id}`,
        DELETE_COMMENT: (id) => `/attendance/comments/${id}`,
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
        const id = window.currentStudentAttendanceId;
        // Convert to number if it's a string, or return null if it's actually null/undefined
        if (id === null || id === undefined || id === 'null') {
            return null;
        }
        return parseInt(id) || null;
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
        
        // Initialize comments functionality
        initializeComments();
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

    // ==================== Comments Functionality ====================
    
    // Comments state
    let commentsData = [];
    let selectedFiles = [];
    let replyingToCommentId = null;

    /**
     * Initialize comments functionality
     */
    function initializeComments() {
        // Wait a bit to ensure window variables are set
        setTimeout(() => {
            const studentAttendanceId = getStudentAttendanceId();
            const attendanceId = getAttendanceId();
            const isCreator = window.currentUserId && window.attendanceCreatorId && window.currentUserId === window.attendanceCreatorId;
            const container = document.getElementById('commentsContainer');
            
            if (!container) {
                console.error('Comments container not found');
                return;
            }
            
            console.log('Initializing comments. Student Attendance ID:', studentAttendanceId);
            console.log('Attendance ID:', attendanceId);
            console.log('Is Creator:', isCreator);
            console.log('Window currentStudentAttendanceId:', window.currentStudentAttendanceId);
            
            // Allow loading comments if user has studentAttendanceId OR is the creator
            if (!studentAttendanceId && !isCreator) {
                console.warn('No student attendance ID found and user is not the creator. Comments will not load.');
                container.innerHTML = 
                    '<div class="text-center py-4"><p class="text-muted mb-0">No attendance session selected. You may need to be enrolled in this attendance session to view comments.</p></div>';
                // Still setup the form in case user gets enrolled later
                setupCommentForm();
                setupFileAttachment();
                return;
            }

            // Load comments
            loadComments();

            // Setup comment form
            setupCommentForm();

            // Setup file attachment button
            setupFileAttachment();
        }, 100);
    }

    /**
     * Load comments from API
     */
    function loadComments() {
        const studentAttendanceId = getStudentAttendanceId();
        const attendanceId = getAttendanceId();
        const isCreator = window.currentUserId && window.attendanceCreatorId && window.currentUserId === window.attendanceCreatorId;
        const container = document.getElementById('commentsContainer');
        
        if (!container) {
            console.error('Comments container not found');
            return;
        }

        // Build API URL - use attendance_id if creator, otherwise use student_attendance_id
        let apiUrl;
        if (isCreator && attendanceId && !studentAttendanceId) {
            // Creator viewing all comments from attendance session
            apiUrl = `${API.GET_COMMENTS}?attendance_id=${attendanceId}`;
        } else if (studentAttendanceId) {
            // Regular user viewing their own comments
            apiUrl = `${API.GET_COMMENTS}?student_attendance_id=${studentAttendanceId}`;
        } else {
            console.warn('Cannot load comments: No student attendance ID and not creator');
            container.innerHTML = '<div class="text-center py-4"><p class="text-muted mb-0">No attendance session selected.</p></div>';
            return;
        }

        console.log('Loading comments from:', apiUrl);

        fetch(apiUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
        .then(response => {
            console.log('Comments API response status:', response.status, response.statusText);
            if (!response.ok) {
                // Try to parse as JSON first, then fallback to text
                return response.json().then(err => {
                    console.error('Comments API error response (JSON):', err);
                    throw new Error(err.message || `HTTP ${response.status}: ${response.statusText}`);
                }).catch(() => {
                    return response.text().then(text => {
                        console.error('Comments API error response (text):', text);
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    });
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Comments API response data:', data);
            if (data.success && data.data && data.data.comments) {
                commentsData = data.data.comments;
                console.log('Loaded', commentsData.length, 'comments');
                renderComments(commentsData);
            } else {
                console.log('No comments found or invalid response format', data);
                container.innerHTML = '<div class="text-center py-4"><p class="text-muted mb-0">No comments yet. Be the first to comment!</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading comments:', error);
            container.innerHTML = '<div class="text-center py-4"><p class="text-danger mb-0">Failed to load comments: ' + error.message + '</p></div>';
        });
    }

    /**
     * Render comments in the container
     */
    function renderComments(comments) {
        const container = document.getElementById('commentsContainer');
        if (!container) return;

        if (!comments || comments.length === 0) {
            container.innerHTML = '<div class="text-center py-4"><p class="text-muted mb-0">No comments yet. Be the first to comment!</p></div>';
            return;
        }

        let html = '';
        comments.forEach(comment => {
            html += renderComment(comment);
        });

        container.innerHTML = html;

        // Reinitialize simplebar if needed
        if (typeof SimpleBar !== 'undefined') {
            const simplebarElement = container.closest('[data-simplebar]');
            if (simplebarElement && !simplebarElement.SimpleBar) {
                new SimpleBar(simplebarElement);
            }
        }
    }

    /**
     * Render a single comment (with replies)
     */
    function renderComment(comment, isReply = false) {
        const marginClass = isReply ? 'mt-3' : 'mb-4';
        // All replies get same single-level indentation (ms-3), regardless of nesting depth
        const indentClass = isReply ? 'ms-3' : '';
        
        // Check if current user owns this comment OR is the attendance creator
        // Convert to numbers for comparison to avoid type mismatch issues
        const currentUserId = parseInt(window.currentUserId) || null;
        const creatorId = parseInt(window.attendanceCreatorId) || null;
        const commentUserId = parseInt(comment.user.id) || null;
        
        const isOwner = currentUserId && commentUserId && currentUserId === commentUserId;
        const isCreator = currentUserId && creatorId && currentUserId === creatorId;
        const canManage = isOwner || isCreator;
        
        let html = `
            <div class="d-flex ${marginClass} ${indentClass}" data-comment-id="${comment.id}">
                <div class="flex-shrink-0">
                    <img src="${escapeHtml(comment.user.avatar_url)}" alt="${escapeHtml(comment.user.name)}"
                        class="avatar-xs rounded-circle material-shadow"
                        onerror="this.onerror=null; this.src='/build/images/users/user-dummy-img.jpg';" />
                </div>
                <div class="flex-grow-1 ms-3 position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="fs-13 mb-1">
                                <a href="javascript:void(0);">${escapeHtml(comment.user.name)}</a>
                                <small class="text-muted">${escapeHtml(comment.created_at_formatted)}</small>
                            </h5>
                        </div>
                        ${canManage ? `
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-more-2-fill fs-16"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item edit-comment-btn" href="javascript:void(0);" data-comment-id="${comment.id}">
                                    <i class="ri-edit-line align-bottom me-2"></i> Edit
                                </a></li>
                                <li><a class="dropdown-item delete-comment-btn text-danger" href="javascript:void(0);" data-comment-id="${comment.id}">
                                    <i class="ri-delete-bin-line align-bottom me-2"></i> Delete
                                </a></li>
                            </ul>
                        </div>
                        ` : ''}
                    </div>
                    <div class="comment-body-container" id="commentBody_${comment.id}">
                        <p class="text-muted mb-2">${escapeHtml(comment.body)}</p>
                    </div>
                    <div class="comment-edit-container" id="commentEdit_${comment.id}" style="display: none;">
                        <textarea class="form-control form-control-sm mb-2" id="commentEditTextarea_${comment.id}" rows="3">${escapeHtml(comment.body)}</textarea>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary btn-sm comment-save-btn" data-comment-id="${comment.id}">
                                <i class="ri-save-line align-bottom me-1"></i> Save
                            </button>
                            <button class="btn btn-light btn-sm comment-cancel-edit-btn" data-comment-id="${comment.id}">
                                <i class="ri-close-line align-bottom me-1"></i> Cancel
                            </button>
                        </div>
                    </div>
        `;

        // Render attachments if any
        if (comment.attachments && comment.attachments.length > 0) {
            html += '<div class="row g-2 mb-2">';
            comment.attachments.forEach(attachment => {
                if (attachment.is_image) {
                    html += `
                        <div class="col-lg-1 col-sm-2 col-6">
                            <img src="${escapeHtml(attachment.file_url)}" alt="${escapeHtml(attachment.original_name)}"
                                class="img-fluid rounded" style="cursor: pointer;"
                                onclick="window.open('${escapeHtml(attachment.file_url)}', '_blank')">
                        </div>
                    `;
                } else {
                    html += `
                        <div class="col-lg-1 col-sm-2 col-6">
                            <div class="border rounded p-2 text-center" style="cursor: pointer;"
                                onclick="window.open('${escapeHtml(attachment.file_url)}', '_blank')">
                                <i class="ri-file-line fs-20"></i>
                                <small class="d-block text-truncate" style="max-width: 60px;" title="${escapeHtml(attachment.original_name)}">
                                    ${escapeHtml(attachment.original_name)}
                                </small>
                            </div>
                        </div>
                    `;
                }
            });
            html += '</div>';
        }

        // Reply button and inline reply form (available for all comments, including nested replies)
        html += `
            <div class="mt-2">
                <a href="javascript:void(0);" class="badge text-muted bg-light reply-btn" data-comment-id="${comment.id}">
                    <i class="mdi mdi-reply"></i> Reply
                </a>
            </div>
            <!-- Inline Reply Form -->
            <div class="reply-form-container mt-3" id="replyForm_${comment.id}" style="display: none;">
                <div class="d-flex align-items-start gap-2">
                    <div class="flex-shrink-0">
                        <img src="${escapeHtml(window.currentUserAvatar || '/build/images/users/user-dummy-img.jpg')}" alt="You"
                            class="avatar-xs rounded-circle material-shadow"
                            onerror="this.onerror=null; this.src='/build/images/users/user-dummy-img.jpg';" />
                    </div>
                    <div class="flex-grow-1">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm reply-input" 
                                id="replyInput_${comment.id}" 
                                placeholder="Write a reply..."
                                data-comment-id="${comment.id}">
                            <button class="btn btn-primary btn-sm reply-submit-btn" 
                                type="button" 
                                data-comment-id="${comment.id}"
                                title="Post reply">
                                <i class="ri-send-plane-line"></i>
                            </button>
                            <button class="btn btn-light btn-sm reply-cancel-btn" 
                                type="button" 
                                data-comment-id="${comment.id}"
                                title="Cancel">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        html += `
                </div>
            </div>
        `;

        // Render replies AFTER closing the parent comment div
        // This ensures all replies have the same single indent level
        if (comment.replies && comment.replies.length > 0) {
            comment.replies.forEach(reply => {
                // All replies get same single indent, regardless of nesting depth
                html += renderComment(reply, true);
            });
        }

        return html;
    }

    /**
     * Setup comment form handlers
     */
    function setupCommentForm() {
        const form = document.getElementById('commentForm');
        const textarea = document.getElementById('commentTextarea');
        const postBtn = document.getElementById('postCommentBtn');

        if (!form || !textarea || !postBtn) {
            console.warn('Comment form elements not found', { form: !!form, textarea: !!textarea, postBtn: !!postBtn });
            return;
        }

        console.log('Setting up comment form handlers');

        // Prevent form submission
        form.onsubmit = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Form submit prevented, calling postComment');
            postComment();
            return false;
        };

        // Handle button click instead of form submit
        postBtn.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Post comment button clicked');
            postComment();
            return false;
        };

        // Handle Enter key in textarea (Ctrl+Enter to submit)
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.ctrlKey) {
                e.preventDefault();
                postComment();
            }
        });

        // Handle reply button clicks (use event delegation on document)
        // This is already set up, but ensure it's not duplicated
        if (!document.commentReplyHandlerSet) {
            document.addEventListener('click', function(e) {
                // Handle reply button click
                if (e.target.closest('.reply-btn')) {
                    e.preventDefault();
                    const replyBtn = e.target.closest('.reply-btn');
                    const commentId = parseInt(replyBtn.getAttribute('data-comment-id'));
                    startReply(commentId);
                }
                // Handle reply submit button click
                if (e.target.closest('.reply-submit-btn')) {
                    e.preventDefault();
                    const submitBtn = e.target.closest('.reply-submit-btn');
                    const commentId = parseInt(submitBtn.getAttribute('data-comment-id'));
                    postReply(commentId);
                }
                // Handle reply cancel button click
                if (e.target.closest('.reply-cancel-btn')) {
                    e.preventDefault();
                    const cancelBtn = e.target.closest('.reply-cancel-btn');
                    const commentId = parseInt(cancelBtn.getAttribute('data-comment-id'));
                    cancelReply(commentId);
                }
                // Handle edit comment button click
                if (e.target.closest('.edit-comment-btn')) {
                    e.preventDefault();
                    const editBtn = e.target.closest('.edit-comment-btn');
                    const commentId = parseInt(editBtn.getAttribute('data-comment-id'));
                    startEditComment(commentId);
                }
                // Handle cancel edit button click
                if (e.target.closest('.comment-cancel-edit-btn')) {
                    e.preventDefault();
                    const cancelBtn = e.target.closest('.comment-cancel-edit-btn');
                    const commentId = parseInt(cancelBtn.getAttribute('data-comment-id'));
                    cancelEditComment(commentId);
                }
                // Handle save comment button click
                if (e.target.closest('.comment-save-btn')) {
                    e.preventDefault();
                    const saveBtn = e.target.closest('.comment-save-btn');
                    const commentId = parseInt(saveBtn.getAttribute('data-comment-id'));
                    saveEditComment(commentId);
                }
                // Handle delete comment button click
                if (e.target.closest('.delete-comment-btn')) {
                    e.preventDefault();
                    const deleteBtn = e.target.closest('.delete-comment-btn');
                    const commentId = parseInt(deleteBtn.getAttribute('data-comment-id'));
                    deleteComment(commentId);
                }
            });

            // Handle Enter key in reply inputs
            document.addEventListener('keydown', function(e) {
                if (e.target.classList.contains('reply-input')) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        const commentId = parseInt(e.target.getAttribute('data-comment-id'));
                        postReply(commentId);
                    }
                }
            });

            document.commentReplyHandlerSet = true;
        }
    }

    /**
     * Start replying to a comment - Show inline reply form
     */
    function startReply(commentId) {
        replyingToCommentId = commentId;
        const replyForm = document.getElementById(`replyForm_${commentId}`);
        const replyInput = document.getElementById(`replyInput_${commentId}`);
        const comment = findCommentById(commentId);
        
        if (replyForm && replyInput && comment) {
            // Show the reply form
            replyForm.style.display = 'block';
            // Pre-fill with @username mention
            replyInput.value = `@${comment.user.name} `;
            // Focus on the input and move cursor to end
            replyInput.focus();
            replyInput.setSelectionRange(replyInput.value.length, replyInput.value.length);
            // Scroll to the reply form
            replyForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else if (comment) {
            // Fallback: use main textarea if inline form not found
            const textarea = document.getElementById('commentTextarea');
            if (textarea) {
                textarea.value = `@${comment.user.name} `;
                textarea.focus();
                textarea.setSelectionRange(textarea.value.length, textarea.value.length);
                textarea.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    /**
     * Cancel reply - Hide inline reply form
     */
    function cancelReply(commentId) {
        const replyForm = document.getElementById(`replyForm_${commentId}`);
        const replyInput = document.getElementById(`replyInput_${commentId}`);
        
        if (replyForm) {
            replyForm.style.display = 'none';
        }
        if (replyInput) {
            replyInput.value = '';
        }
        
        if (replyingToCommentId === commentId) {
            replyingToCommentId = null;
        }
    }

    /**
     * Post reply from inline reply form
     */
    function postReply(commentId) {
        const replyInput = document.getElementById(`replyInput_${commentId}`);
        
        if (!replyInput) {
            console.error('Reply input not found for comment:', commentId);
            return;
        }

        const body = replyInput.value.trim();
        if (!body) {
            showToast('Error', 'Please enter a reply', 'error');
            return;
        }

        const studentAttendanceId = getStudentAttendanceId();
        const attendanceId = getAttendanceId();
        const isCreator = window.currentUserId && window.attendanceCreatorId && parseInt(window.currentUserId) === parseInt(window.attendanceCreatorId);
        
        // Allow posting if user has studentAttendanceId OR is the creator
        if (!studentAttendanceId && !isCreator) {
            showToast('Error', 'No attendance session selected', 'error');
            return;
        }

        const submitBtn = document.querySelector(`.reply-submit-btn[data-comment-id="${commentId}"]`);
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        }

        // Create FormData for file uploads
        const formData = new FormData();
        if (isCreator && attendanceId && !studentAttendanceId) {
            // Creator posting via attendance_id
            formData.append('attendance_id', attendanceId);
        } else {
            // Regular user posting via student_attendance_id
            formData.append('student_attendance_id', studentAttendanceId);
        }
        formData.append('body', body);
        formData.append('parent_id', commentId);

        console.log('Posting reply:', { studentAttendanceId, attendanceId, isCreator, body, parent_id: commentId });

        fetch(API.POST_COMMENT, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            console.log('Post reply response status:', response.status);
            if (!response.ok) {
                return response.json().then(err => {
                    console.error('Post reply error response:', err);
                    throw new Error(err.message || err.errors || `HTTP ${response.status}: ${response.statusText}`);
                }).catch(() => {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Post reply success response:', data);
            if (data.success) {
                // Clear the reply input
                replyInput.value = '';
                // Hide the reply form
                cancelReply(commentId);
                // Reload comments to show the new reply
                loadComments();
            } else {
                throw new Error(data.message || 'Failed to post reply');
            }
        })
        .catch(error => {
            console.error('Error posting reply:', error);
            showToast('Error', error.message || 'Failed to post reply', 'error');
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ri-send-plane-line"></i>';
            }
        });
    }

    /**
     * Find comment by ID (including in replies)
     */
    function findCommentById(id, comments = commentsData) {
        for (let comment of comments) {
            if (comment.id === id) {
                return comment;
            }
            if (comment.replies && comment.replies.length > 0) {
                const found = findCommentById(id, comment.replies);
                if (found) return found;
            }
        }
        return null;
    }

    /**
     * Post a new comment or reply
     */
    function postComment() {
        console.log('postComment() called');
        
        const studentAttendanceId = getStudentAttendanceId();
        const attendanceId = getAttendanceId();
        const isCreator = window.currentUserId && window.attendanceCreatorId && parseInt(window.currentUserId) === parseInt(window.attendanceCreatorId);
        const textarea = document.getElementById('commentTextarea');
        const postBtn = document.getElementById('postCommentBtn');
        
        if (!postBtn) {
            console.error('Post comment button not found');
            showToast('Error', 'Post button not found', 'error');
            return;
        }
        
        const spinner = postBtn.querySelector('.spinner-border');

        // Allow posting if user has studentAttendanceId OR is the creator
        if (!studentAttendanceId && !isCreator) {
            console.warn('No student attendance ID and not creator');
            showToast('Error', 'No attendance session selected', 'error');
            return;
        }

        if (!textarea) {
            console.error('Textarea not found');
            showToast('Error', 'Comment textarea not found', 'error');
            return;
        }

        const body = textarea.value.trim();
        if (!body) {
            console.warn('Empty comment body');
            showToast('Error', 'Please enter a comment', 'error');
            return;
        }

        console.log('Posting comment:', { studentAttendanceId, attendanceId, isCreator, body, replyingToCommentId, filesCount: selectedFiles.length });

        // Disable button and show spinner
        postBtn.disabled = true;
        if (spinner) spinner.classList.remove('d-none');

        // Create FormData for file uploads
        const formData = new FormData();
        if (isCreator && attendanceId && !studentAttendanceId) {
            // Creator posting via attendance_id
            formData.append('attendance_id', attendanceId);
        } else {
            // Regular user posting via student_attendance_id
            formData.append('student_attendance_id', studentAttendanceId);
        }
        formData.append('body', body);
        
        if (replyingToCommentId) {
            formData.append('parent_id', replyingToCommentId);
        }

        // Add files
        selectedFiles.forEach((file, index) => {
            formData.append(`attachments[${index}]`, file);
        });

        console.log('Sending POST request to:', API.POST_COMMENT);

        fetch(API.POST_COMMENT, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            console.log('Post comment response status:', response.status);
            // Check if response is ok
            if (!response.ok) {
                // If response is not ok, try to parse error message
                return response.json().then(err => {
                    console.error('Post comment error response:', err);
                    throw new Error(err.message || err.errors || `HTTP ${response.status}: ${response.statusText}`);
                }).catch(() => {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Post comment success response:', data);
            if (data.success) {
                showToast('Success', 'Comment posted successfully', 'success');
                textarea.value = '';
                selectedFiles = [];
                replyingToCommentId = null;
                updateAttachmentsPreview();
                loadComments(); // Reload comments
            } else {
                throw new Error(data.message || 'Failed to post comment');
            }
        })
        .catch(error => {
            console.error('Error posting comment:', error);
            showToast('Error', error.message || 'Failed to post comment', 'error');
        })
        .finally(() => {
            postBtn.disabled = false;
            if (spinner) spinner.classList.add('d-none');
        });
    }

    /**
     * Setup file attachment functionality
     */
    function setupFileAttachment() {
        const attachmentBtn = document.getElementById('commentAttachmentBtn');
        const fileInput = document.getElementById('commentFileInput');

        if (!attachmentBtn || !fileInput) return;

        attachmentBtn.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            // Validate file count
            if (selectedFiles.length + files.length > 5) {
                showToast('Error', 'Maximum 5 files allowed', 'error');
                return;
            }

            // Validate file sizes and types
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 
                                 'application/pdf', 'application/msword', 
                                 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                 'application/vnd.ms-excel',
                                 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                 'text/plain'];

            files.forEach(file => {
                if (file.size > maxSize) {
                    showToast('Error', `File ${file.name} is too large. Maximum size is 5MB.`, 'error');
                    return;
                }
                if (!allowedTypes.includes(file.type)) {
                    showToast('Error', `File ${file.name} is not a supported file type.`, 'error');
                    return;
                }
                selectedFiles.push(file);
            });

            updateAttachmentsPreview();
        });
    }

    /**
     * Update attachments preview
     */
    function updateAttachmentsPreview() {
        const previewContainer = document.getElementById('commentAttachmentsPreview');
        if (!previewContainer) return;

        previewContainer.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const fileDiv = document.createElement('div');
            fileDiv.className = 'position-relative d-inline-block';
            fileDiv.style.cssText = 'max-width: 100px;';

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    fileDiv.innerHTML = `
                        <img src="${e.target.result}" alt="${escapeHtml(file.name)}" 
                            class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                            onclick="removeAttachment(${index})" style="padding: 2px 6px; font-size: 10px;">
                            <i class="ri-close-line"></i>
                        </button>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                fileDiv.innerHTML = `
                    <div class="border rounded p-2 text-center" style="width: 100px; height: 100px;">
                        <i class="ri-file-line fs-20"></i>
                        <small class="d-block text-truncate" style="max-width: 90px;" title="${escapeHtml(file.name)}">
                            ${escapeHtml(file.name)}
                        </small>
                        <button type="button" class="btn btn-sm btn-danger mt-1" 
                            onclick="removeAttachment(${index})" style="padding: 2px 6px; font-size: 10px;">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                `;
            }

            previewContainer.appendChild(fileDiv);
        });
    }

    /**
     * Remove attachment from selection
     */
    window.removeAttachment = function(index) {
        selectedFiles.splice(index, 1);
        updateAttachmentsPreview();
        
        // Reset file input
        const fileInput = document.getElementById('commentFileInput');
        if (fileInput) {
            fileInput.value = '';
        }
    };

    /**
     * Start editing a comment - Show textarea
     */
    function startEditComment(commentId) {
        const commentBody = document.getElementById(`commentBody_${commentId}`);
        const commentEdit = document.getElementById(`commentEdit_${commentId}`);
        const commentTextarea = document.getElementById(`commentEditTextarea_${commentId}`);
        
        if (commentBody && commentEdit && commentTextarea) {
            // Hide comment body, show edit form
            commentBody.style.display = 'none';
            commentEdit.style.display = 'block';
            // Focus on textarea
            commentTextarea.focus();
            commentTextarea.setSelectionRange(commentTextarea.value.length, commentTextarea.value.length);
        }
    }

    /**
     * Cancel editing a comment - Hide textarea, show comment body
     */
    function cancelEditComment(commentId) {
        const commentBody = document.getElementById(`commentBody_${commentId}`);
        const commentEdit = document.getElementById(`commentEdit_${commentId}`);
        const commentTextarea = document.getElementById(`commentEditTextarea_${commentId}`);
        
        if (commentBody && commentEdit && commentTextarea) {
            // Hide edit form, show comment body
            commentEdit.style.display = 'none';
            commentBody.style.display = 'block';
            // Reset textarea to original value
            const comment = findCommentById(commentId);
            if (comment) {
                commentTextarea.value = comment.body;
            }
        }
    }

    /**
     * Save edited comment
     */
    function saveEditComment(commentId) {
        const commentTextarea = document.getElementById(`commentEditTextarea_${commentId}`);
        const saveBtn = document.querySelector(`.comment-save-btn[data-comment-id="${commentId}"]`);
        
        if (!commentTextarea) {
            console.error('Comment textarea not found for comment:', commentId);
            return;
        }

        const body = commentTextarea.value.trim();
        if (!body) {
            showToast('Error', 'Please enter a comment', 'error');
            return;
        }

        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        }

        console.log('Updating comment:', { commentId, body });

        fetch(API.UPDATE_COMMENT(commentId), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ body })
        })
        .then(response => {
            console.log('Update comment response status:', response.status);
            if (!response.ok) {
                return response.json().then(err => {
                    console.error('Update comment error response:', err);
                    throw new Error(err.message || err.errors || `HTTP ${response.status}: ${response.statusText}`);
                }).catch(() => {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Update comment success response:', data);
            if (data.success) {
                showToast('Success', 'Comment updated successfully', 'success');
                // Reload comments to show updated comment
                loadComments();
            } else {
                throw new Error(data.message || 'Failed to update comment');
            }
        })
        .catch(error => {
            console.error('Error updating comment:', error);
            showToast('Error', error.message || 'Failed to update comment', 'error');
        })
        .finally(() => {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="ri-save-line align-bottom me-1"></i> Save';
            }
        });
    }

    /**
     * Delete a comment
     */
    function deleteComment(commentId) {
        // Confirm deletion with SweetAlert
        Swal.fire({
            title: 'Delete Comment?',
            text: 'Are you sure you want to delete this comment? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f06548',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('Deleting comment:', commentId);

                fetch(API.DELETE_COMMENT(commentId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Delete comment response status:', response.status);
                    if (!response.ok) {
                        return response.json().then(err => {
                            console.error('Delete comment error response:', err);
                            throw new Error(err.message || err.errors || `HTTP ${response.status}: ${response.statusText}`);
                        }).catch(() => {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Delete comment success response:', data);
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Comment has been deleted successfully.',
                            icon: 'success',
                            confirmButtonColor: '#405189',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        // Reload comments to remove deleted comment
                        loadComments();
                    } else {
                        throw new Error(data.message || 'Failed to delete comment');
                    }
                })
                .catch(error => {
                    console.error('Error deleting comment:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'Failed to delete comment',
                        icon: 'error',
                        confirmButtonColor: '#405189'
                    });
                });
            }
        });
    }

})();
