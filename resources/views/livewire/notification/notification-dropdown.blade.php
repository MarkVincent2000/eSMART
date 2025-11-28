<div class="notification-panel-wrapper ms-1 header-item" x-data="{ open: @entangle('isOpen') }"
    @click.away="open = false">
    <!-- Notification Bell Button -->
    <button type="button"
        class="notification-panel-trigger btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
        @click="open = !open" aria-label="Toggle notifications">
        <i class='bx bx-bell fs-22'></i>
        @if($this->unreadCount > 0)
            <span
                class="notification-badge position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                <span class="visually-hidden">unread messages</span>
            </span>
        @endif
    </button>

    <!-- Notification Panel Menu -->
    <div class="notification-panel-menu" x-show="open" x-transition:enter="notification-panel-enter"
        x-transition:enter-start="notification-panel-enter-start" x-transition:enter-end="notification-panel-enter-end"
        x-transition:leave="notification-panel-leave" x-transition:leave-start="notification-panel-leave-start"
        x-transition:leave-end="notification-panel-leave-end" style="display: none;" @click.stop>

        <!-- Panel Header -->
        <div class="notification-panel-header bg-primary bg-pattern rounded-top">
            <div class="p-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 fs-16 fw-semibold text-white">Notifications</h6>
                    </div>
                    <div class="col-auto">
                        @if($this->unreadCount > 0)
                            <span class="badge bg-light text-body fs-13">{{ $this->unreadCount }} New</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Content -->
        <div class="notification-panel-content">
            <div class="notification-panel-scroll">
                @forelse($this->allNotifications as $notification)
                    <div class="notification-item {{ $notification->isUnread() ? 'notification-item-unread' : '' }}">
                        <div class="d-flex">
                            <!-- Notification Icon -->
                            <div class="notification-item-icon me-3 flex-shrink-0">
                                <span
                                    class="avatar-title {{ $notification->type === 'alert' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }} rounded-circle fs-16">
                                    <i
                                        class="{{ $notification->type === 'alert' ? 'bx bx-error-circle' : 'bx bx-badge-check' }}"></i>
                                </span>
                            </div>

                            <!-- Notification Content -->
                            <a href="#" type="button" class="notification-item-link"
                                wire:click.prevent="clickmarkAsRead({{ $notification->id }})">
                                <div class="notification-item-content flex-grow-1">

                                    <h6 class="notification-item-title mt-0 mb-2 lh-base">
                                        {{ $notification->title }}
                                    </h6>

                                    @if($notification->body)
                                        <p class="notification-item-body mb-1 fs-13 text-muted">{{ $notification->body }}</p>
                                    @endif
                                    <p class="notification-item-time mb-0 fs-11 fw-medium text-uppercase text-muted">
                                        <span><i class="mdi mdi-clock-outline"></i>
                                            {{ $notification->created_at->diffForHumans() }}</span>
                                    </p>
                                </div>
                            </a>

                            <!-- Notification Checkbox -->
                            <div class="notification-item-checkbox px-2 fs-15" @click.stop>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" wire:model.live="selected"
                                        value="{{ $notification->id }}" id="notification-check-{{ $notification->id }}">
                                    <label class="form-check-label"
                                        for="notification-check-{{ $notification->id }}"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="notification-empty text-center py-4">
                        <p class="text-muted mb-0">No notifications found</p>
                    </div>
                @endforelse

                @if($this->allNotifications->count() > 0)
                    <div class="notification-view-all my-3 text-center">
                        <a type="button" href="notification.index-notification-page"
                            class="btn btn-soft-success waves-effect waves-light">
                            View All Notifications <i class="ri-arrow-right-line align-middle"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Panel Actions (when items are selected) -->
        @if(count($selected) > 0)
            <div class="notification-panel-actions border-top p-2 bg-light"
                wire:key="notification-actions-{{ count($selected) }}" @click.stop>
                <div class="d-flex text-muted justify-content-center align-items-center flex-wrap gap-2">
                    <span class="notification-actions-text small">
                        Select <span class="text-body fw-semibold">{{ count($selected) }}</span> Result
                    </span>
                    <button type="button" class="btn btn-link link-primary p-0 text-decoration-none"
                        wire:click.prevent="markAllAsRead" @click.stop wire:loading.attr="disabled">
                        <i class="ri-check-line me-1"></i> Mark as Read
                    </button>
                    <button type="button" class="btn btn-link link-danger p-0 text-decoration-none"
                        wire:click.prevent="deleteSelected" @click.stop wire:loading.attr="disabled">
                        <i class="ri-delete-bin-line me-1"></i> Delete
                    </button>
                </div>
            </div>
        @endif
    </div>
    <style>
        /* Notification Panel Wrapper */
        .notification-panel-wrapper {
            position: relative;
        }

        /* Notification Panel Trigger */
        .notification-panel-trigger {
            position: relative;
        }

        /* Notification Badge */
        /* .notification-badge {
            top: 0;
            right: 0;
            transform: translate(45%, -45%);
            min-width: 18px;
            min-height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        } */

        /* Notification Panel Menu */
        .notification-panel-menu {
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 1050;
            min-width: 320px;
            max-width: 360px;
            margin-top: 0rem;
            background-color: #fff;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 0.375rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* Panel Header */
        .notification-panel-header {
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
        }

        /* Panel Content */
        .notification-panel-content {
            position: relative;
        }

        /* Panel Scroll */
        .notification-panel-scroll {
            max-height: 400px;
            overflow-y: auto;
            padding: 0.5rem 0.5rem 0.5rem 0;
        }

        /* Notification Item */
        .notification-item {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 0.375rem;
            transition: background-color 0.2s ease;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .notification-item-unread {
            background-color: #f8f9fa;
        }

        /* Notification Item Icon */
        .notification-item-icon .avatar-title {
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Notification Item Content */
        .notification-item-content {
            min-width: 0;
        }

        .notification-item-link {
            text-decoration: none;
            color: inherit;
        }

        .notification-item-link:hover {
            text-decoration: none;
        }

        .notification-item-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #212529;
        }

        .notification-item-body {
            font-size: 0.8125rem;
        }

        .notification-item-time {
            font-size: 0.6875rem;
        }

        /* Notification Item Checkbox */
        .notification-item-checkbox {
            display: flex;
            align-items: flex-start;
            padding-top: 0.5rem;
        }

        /* Panel Actions */
        .notification-panel-actions {
            border-bottom-left-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }

        .notification-actions-text {
            font-size: 0.875rem;
        }

        /* Empty State */
        .notification-empty {
            padding: 2rem 1rem;
        }

        /* View All */
        .notification-view-all {
            padding: 0 1rem;
        }

        /* Transitions */
        .notification-panel-enter {
            transition: opacity 0.15s ease-out, transform 0.15s ease-out;
        }

        .notification-panel-enter-start {
            opacity: 0;
            transform: translateY(-10px);
        }

        .notification-panel-enter-end {
            opacity: 1;
            transform: translateY(0);
        }

        .notification-panel-leave {
            transition: opacity 0.15s ease-in, transform 0.15s ease-in;
        }

        .notification-panel-leave-start {
            opacity: 1;
            transform: translateY(0);
        }

        .notification-panel-leave-end {
            opacity: 0;
            transform: translateY(-10px);
        }

        /* Scrollbar Styling */
        .notification-panel-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .notification-panel-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .notification-panel-scroll::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .notification-panel-scroll::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .notification-panel-menu {
                min-width: 280px;
                right: 0;
            }
        }

        /* Dark Mode Overrides */
        [data-bs-theme="dark"] .notification-panel-menu {
            background-color: #1f2533;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0.75rem 1.25rem rgba(0, 0, 0, 0.7);
        }

        [data-bs-theme="dark"] .notification-panel-header {
            background-color: #293042;
        }

        [data-bs-theme="dark"] .notification-item {
            background-color: transparent;
        }

        [data-bs-theme="dark"] .notification-item:hover {
            background-color: rgba(255, 255, 255, 0.04);
        }

        [data-bs-theme="dark"] .notification-item-unread {
            background-color: rgba(255, 255, 255, 0.08);
        }

        [data-bs-theme="dark"] .notification-item-title {
            color: #f8f9fc;
        }

        [data-bs-theme="dark"] .notification-item-body {
            color: #ced4da;
        }

        [data-bs-theme="dark"] .notification-item-time {
            color: #9ba7bd;
        }

        [data-bs-theme="dark"] .notification-panel-actions {
            background-color: #1b2130;
            border-top-color: rgba(255, 255, 255, 0.08);
        }

        [data-bs-theme="dark"] .notification-empty {
            color: #adb5bd;
        }
    </style>
</div>