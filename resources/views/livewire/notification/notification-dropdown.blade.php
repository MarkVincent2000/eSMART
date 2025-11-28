<div class="dropdown topbar-head-dropdown ms-1 header-item">
    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle"
        id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
        aria-haspopup="true" aria-expanded="false">
        <i class='bx bx-bell fs-22'></i>
        @if($this->unreadCount > 0)
            <span class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                <span class="visually-hidden">unread messages</span>
            </span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
        aria-labelledby="page-header-notifications-dropdown">

        <div class="dropdown-head bg-primary bg-pattern rounded-top">
            <div class="p-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 fs-16 fw-semibold text-white"> Notifications </h6>
                    </div>
                    <div class="col-auto dropdown-tabs">
                        @if($this->unreadCount > 0)
                            <span class="badge bg-light text-body fs-13"> {{ $this->unreadCount }} New</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <div class="tab-content position-relative">
            <div class="tab-pane fade show active py-2 ps-2" id="all-noti-tab" role="tabpanel">
                <div style="max-height: 300px; overflow-y: auto;" class="pe-2">
                    @forelse($this->allNotifications as $notification)
                        <div
                            class="text-reset notification-item d-block dropdown-item position-relative {{ $notification->isUnread() ? 'bg-light' : '' }}">
                            <div class="d-flex">
                                <div class="avatar-xs me-3 flex-shrink-0">
                                    <span
                                        class="avatar-title  {{ $notification->type === 'alert' ? 'bg-danger-subtle text-danger' : 'bg-info-subtle text-info' }} rounded-circle fs-16">
                                        <i
                                            class="{{ $notification->type === 'alert' ? 'bx bx-error-circle' : 'bx bx-badge-check' }}"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <a href="#!" class="stretched-link" wire:click="markAsRead({{ $notification->id }})">
                                        <h6 class="mt-0 mb-2 lh-base">
                                            {{ $notification->title }}
                                        </h6>
                                    </a>
                                    @if($notification->body)
                                        <p class="mb-1 fs-13 text-muted">{{ $notification->body }}</p>
                                    @endif
                                    <p class="mb-0 fs-11 fw-medium text-uppercase text-muted">
                                        <span><i class="mdi mdi-clock-outline"></i>
                                            {{ $notification->created_at->diffForHumans() }}</span>
                                    </p>
                                </div>
                                <div class="px-2 fs-15">
                                    <div class="form-check notification-check" style="position: relative; z-index: 2;">
                                        <input class="form-check-input" type="checkbox" wire:model.live="selected"
                                            value="{{ $notification->id }}" id="notification-check-{{ $notification->id }}"
                                            onclick="event.stopPropagation()">
                                        <label class="form-check-label" for="notification-check-{{ $notification->id }}"
                                            onclick="event.stopPropagation()"></label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No notifications found</p>
                        </div>
                    @endforelse

                    @if($this->allNotifications->count() > 0)
                        <div class="my-3 text-center view-all">
                            <button type="button" class="btn btn-soft-success waves-effect waves-light">
                                View All Notifications <i class="ri-arrow-right-line align-middle"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        @php
            $selectedCount = count($this->selected ?? []);
        @endphp

        @if($selectedCount > 0)
            <div class="notification-actions border-top p-2 bg-light" wire:key="notification-actions-{{ $selectedCount }}">
                <div class="d-flex text-muted justify-content-center align-items-center flex-wrap gap-2"
                    onclick="event.stopPropagation()">
                    <span class="small">Select <span class="text-body fw-semibold">{{ $selectedCount }}</span> Result</span>
                    <button type="button" class="btn btn-link link-primary p-0 text-decoration-none"
                        wire:click="markAllAsRead" wire:loading.attr="disabled">
                        <i class="ri-check-line me-1"></i> Mark as Read
                    </button>
                    <button type="button" class="btn btn-link link-danger p-0 text-decoration-none"
                        wire:click="deleteSelected"
                        wire:confirm="Are you sure you want to delete {{ $selectedCount }} notification(s)?"
                        wire:loading.attr="disabled">
                        <i class="ri-delete-bin-line me-1"></i> Delete
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>