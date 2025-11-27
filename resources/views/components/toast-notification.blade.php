{{--
Toast Notification Component

A reusable Blade component for creating toast notifications using Bootstrap Toasts.
Based on the Bootstrap Toast examples from ui-notifications.blade.php

Usage with auto-trigger from Livewire:
Just include the component in your blade file. It will automatically listen for 'show-toast' browser events.
On Livewire component, dispatch like this:
$this->dispatch('show-toast', [
'message' => 'User invited successfully!',
'type' => 'success', // Can be 'success', 'error', 'warning', 'info', 'primary', 'danger'
'title' => 'Success', // Optional title
'position' => 'top-right' // Optional: 'top-left', 'top-center', 'top-right', 'bottom-left', 'bottom-center',
'bottom-right'
]);

Usage with button trigger (for manual testing):
<button type="button" class="btn btn-primary"
    onclick="window.dispatchEvent(new CustomEvent('show-toast', {detail: {message: 'Hello!', type: 'success'}}))">
    Show Toast
</button>
--}}

{{-- Toast Container for top-right (default position) --}}
<div class="toast-container position-fixed top-0 end-0 p-3" id="toast-container-top-right" style="z-index: 9999;">
    <!-- Toasts will be dynamically inserted here -->
</div>

{{-- Toast Container for top-center --}}
<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" id="toast-container-top-center"
    style="z-index: 9999;">
    <!-- Toasts will be dynamically inserted here -->
</div>

{{-- Toast Container for top-left --}}
<div class="toast-container position-fixed top-0 start-0 p-3" id="toast-container-top-left" style="z-index: 9999;">
    <!-- Toasts will be dynamically inserted here -->
</div>

{{-- Toast Container for bottom-right --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container-bottom-right" style="z-index: 9999;">
    <!-- Toasts will be dynamically inserted here -->
</div>

{{-- Toast Container for bottom-center --}}
<div class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3" id="toast-container-bottom-center"
    style="z-index: 9999;">
    <!-- Toasts will be dynamically inserted here -->
</div>

{{-- Toast Container for bottom-left --}}
<div class="toast-container position-fixed bottom-0 start-0 p-3" id="toast-container-bottom-left"
    style="z-index: 9999;">
    <!-- Toasts will be dynamically inserted here -->
</div>

@push('scripts')
    <script>
        // Listen for 'show-toast' browser event and trigger Bootstrap Toast
        window.addEventListener('show-toast', (event) => {
            // Support both array and object payloads
            let payload = event.detail;
            if (Array.isArray(payload)) {
                payload = payload[0] || {};
            }

            let message = payload.message || 'Notification';
            let type = payload.type || 'success';
            let title = payload.title || null;
            let position = payload.position || 'top-right';
            let duration = payload.duration !== undefined ? payload.duration : 3000;
            let showClose = payload.close !== false;

            // Guard against undefined/empty messages
            if (message === undefined || message === null) return;
            if (typeof message !== 'string') {
                try {
                    message = String(message);
                } catch (e) {
                    return;
                }
            }
            message = message.trim();
            if (!message) return;

            // Normalize type for Bootstrap classes
            const typeMap = {
                'success': { bg: 'bg-success', text: 'text-white', icon: 'ri-checkbox-circle-fill' },
                'error': { bg: 'bg-danger', text: 'text-white', icon: 'ri-alert-line' },
                'danger': { bg: 'bg-danger', text: 'text-white', icon: 'ri-alert-line' },
                'warning': { bg: 'bg-warning', text: 'text-dark', icon: 'ri-notification-off-line' },
                'info': { bg: 'bg-info', text: 'text-white', icon: 'ri-information-line' },
                'primary': { bg: 'bg-primary', text: 'text-white', icon: 'ri-user-smile-line' }
            };
            const toastConfig = typeMap[type] || typeMap['success'];

            // Map position to container ID
            const positionMap = {
                'top-left': 'toast-container-top-left',
                'top-center': 'toast-container-top-center',
                'top-right': 'toast-container-top-right',
                'bottom-left': 'toast-container-bottom-left',
                'bottom-center': 'toast-container-bottom-center',
                'bottom-right': 'toast-container-bottom-right'
            };
            const containerId = positionMap[position] || 'toast-container-top-right';
            const container = document.getElementById(containerId);

            if (!container) {
                console.error('Toast container not found:', containerId);
                return;
            }

            // Check if Bootstrap Toast is available
            if (typeof bootstrap === 'undefined' || typeof bootstrap.Toast === 'undefined') {
                console.warn('Bootstrap Toast is not loaded');
                return;
            }

            // Create unique toast ID
            const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

            // Default title based on type if not provided
            if (!title) {
                const titleMap = {
                    'success': 'Success',
                    'error': 'Error',
                    'danger': 'Error',
                    'warning': 'Warning',
                    'info': 'Information',
                    'primary': 'Notification'
                };
                title = titleMap[type] || 'Notification';
            }

            // Determine text color classes for timestamp and close button
            const timeColor = type === 'warning' ? 'text-dark' : 'text-white-50';
            const closeBtnClass = type === 'warning' ? 'btn-close' : 'btn-close btn-close-white';

            // Create toast HTML
            let toastHTML = '<div class="toast ' + toastConfig.bg + ' ' + toastConfig.text + '" role="alert" aria-live="assertive" aria-atomic="true" id="' + toastId + '">';
            toastHTML += '<div class="toast-header ' + toastConfig.bg + ' ' + toastConfig.text + ' border-0">';
            toastHTML += '<i class="' + toastConfig.icon + ' me-2"></i>';
            toastHTML += '<strong class="me-auto">' + title + '</strong>';
            toastHTML += '<small class="' + timeColor + '">Just now</small>';
            if (showClose) {
                toastHTML += '<button type="button" class="' + closeBtnClass + '" data-bs-dismiss="toast" aria-label="Close"></button>';
            }
            toastHTML += '</div>';
            toastHTML += '<div class="toast-body">' + message + '</div>';
            toastHTML += '</div>';

            // Insert toast into container
            container.insertAdjacentHTML('beforeend', toastHTML);

            // Get the toast element and initialize Bootstrap Toast
            const toastElement = document.getElementById(toastId);
            if (toastElement) {
                const toast = new bootstrap.Toast(toastElement, {
                    autohide: duration > 0,
                    delay: duration
                });

                // Show the toast
                toast.show();

                // Remove toast element after it's hidden
                toastElement.addEventListener('hidden.bs.toast', function () {
                    toastElement.remove();
                });
            }
        });
    </script>
@endpush