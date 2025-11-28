<div>
    <x-toast-notification />

    <div class="card">
        <div class="card-header d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h5 class="card-title mb-1">Notification Timeline</h5>
                <p class="text-muted mb-0">{{ $this->unreadCount }} unread · {{ $totalCount }} total</p>
            </div>
            <div class="search-box" style="width: 300px;">
                <input type="text" class="form-control search bg-light border-light"
                    placeholder="Search notifications..." wire:model.live.debounce.300ms="search">
                <i class="ri-search-line search-icon"></i>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">

                <x-button size="sm" variant="soft" wireTarget="markAllAsRead" color="primary"
                    icon="ri-check-double-line" wire:click="markAllAsRead">
                    Mark All as Read
                </x-button>
                <div class="d-flex flex-wrap gap-2">
                    <x-button size="sm" variant="soft" color="secondary" icon="ri-arrow-down-double-line"
                        wireTarget="toggleNotificationAccordion('show')" onclick="toggleNotificationAccordion('show')">
                        Expand All
                    </x-button>
                    <x-button size="sm" variant="soft" color="secondary"
                        wireTarget="toggleNotificationAccordion('hide')" icon="ri-arrow-up-double-line"
                        onclick="toggleNotificationAccordion('hide')">
                        Collapse All
                    </x-button>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if($notifications->isEmpty())
                <div class="text-center py-5">
                    <i class="ri-notification-off-line fs-1 text-muted mb-3 d-block"></i>
                    <h6>No notifications yet</h6>
                    <p class="text-muted mb-0">You're all caught up for now.</p>
                </div>
            @else
                <div class="profile-timeline">
                    <div class="accordion accordion-flush" id="notificationAccordion">
                        @foreach($notifications as $notification)
                            @php
                                $collapseId = 'collapseNotification-' . $notification->id;
                                $headingId = 'headingNotification-' . $notification->id;
                                $icon = match ($notification->type) {
                                    'alert' => 'ri-error-warning-line',
                                    'success' => 'ri-checkbox-circle-line',
                                    'info' => 'ri-information-line',
                                    default => 'ri-notification-3-line'
                                };
                                $avatarClass = $notification->type === 'alert'
                                    ? 'bg-danger rounded-circle text-white'
                                    : 'bg-success rounded-circle text-white';
                            @endphp
                            <div class="accordion-item border-0" wire:key="notification-accordion-{{ $notification->id }}">
                                <div class="accordion-header" id="{{ $headingId }}">
                                    <a class="accordion-button p-2 shadow-none {{ $loop->first ? '' : 'collapsed' }}"
                                        data-bs-toggle="collapse" href="#{{ $collapseId }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}">
                                        <div class="d-flex align-items-center w-100 gap-3">
                                            <div class="flex-shrink-0 avatar-xs">
                                                <div class="avatar-title {{ $avatarClass }} material-shadow">
                                                    <i class="{{ $icon }}"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-1">
                                                <h6 class="fs-15 mb-0 {{ $notification->isUnread() ? 'fw-semibold' : '' }}">
                                                    {{ $notification->title }}
                                                    <span class="fw-normal text-muted d-block fs-13">
                                                        {{ $notification->created_at->translatedFormat('M d, Y · g:i A') }}
                                                    </span>
                                                </h6>
                                            </div>
                                            <span
                                                class="badge rounded-pill {{ $notification->type === 'alert' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                                                {{ ucfirst($notification->type ?? 'info') }}
                                            </span>
                                        </div>
                                    </a>
                                </div>
                                <div id="{{ $collapseId }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                    aria-labelledby="{{ $headingId }}">
                                    <div class="accordion-body ms-2 ps-5 pt-0">
                                        @if($notification->body)
                                            <p class="text-muted mb-2">{{ $notification->body }}</p>
                                        @endif
                                        <p class="text-muted mb-3">
                                            <i class="ri-time-line me-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                        <div class="d-flex flex-wrap gap-3">
                                            @if($notification->isUnread())
                                                <button type="button" class="btn btn-link link-primary p-0 text-decoration-none"
                                                    wire:click="markAsRead({{ $notification->id }})" wire:loading.attr="disabled">
                                                    <i class="ri-check-line me-1"></i>Mark as Read
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-link link-danger p-0 text-decoration-none"
                                                wire:click="deleteNotification({{ $notification->id }})"
                                                wire:loading.attr="disabled">
                                                <i class="ri-delete-bin-line me-1"></i>Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($hasMore)
                        <div class="text-center mt-3">
                            <x-button size="sm" variant="outline" color="secondary" icon="ri-arrow-down-double-line"
                                wire:click="loadMore" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="loadMore">
                                    Load More
                                </span>
                                <span wire:loading wire:target="loadMore">
                                    Loading...
                                </span>
                            </x-button>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <script>
        function toggleNotificationAccordion(action) {
            if (typeof bootstrap === 'undefined') {
                return;
            }
            document.querySelectorAll('#notificationAccordion .accordion-collapse').forEach(function (element) {
                const instance = bootstrap.Collapse.getOrCreateInstance(element, { toggle: false });
                if (action === 'show') {
                    instance.show();
                } else {
                    instance.hide();
                }
            });
        }
    </script>
</div>