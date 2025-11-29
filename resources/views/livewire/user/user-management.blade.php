<div>

    <x-toast-notification />


    <div class="row"
        wire:key="stats-cards-{{ $this->totalUsers }}-{{ $this->totalActiveUsers }}-{{ $this->totalInactiveUsers }}">
        <div class="col-xxl-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Total Users</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $this->totalUsers }}">
                                    {{ $this->totalUsers }}
                                </span>
                            </h2>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-info mb-0">
                                    <i class="ri-user-line align-middle"></i> All users
                                </span>
                            </p>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">
                                    <i class="ri-user-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div> <!-- end card-->
        </div>
        <!--end col-->
        <div class="col-xxl-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Active Users</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $this->totalActiveUsers }}">
                                    {{ $this->totalActiveUsers }}
                                </span>
                            </h2>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-success mb-0">
                                    <i class="ri-checkbox-circle-line align-middle"></i> Active
                                </span>
                            </p>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                                    <i class="ri-checkbox-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div>
        </div>
        <!--end col-->
        <div class="col-xxl-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Inactive Users</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $this->totalInactiveUsers }}">
                                    {{ $this->totalInactiveUsers }}
                                </span>
                            </h2>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-secondary mb-0">
                                    <i class="ri-close-circle-line align-middle"></i> Inactive
                                </span>
                            </p>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-secondary-subtle text-secondary rounded-circle fs-4">
                                    <i class="ri-close-circle-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div>
        </div>
        <!--end col-->
        <div class="col-xxl-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Active Percentage</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value"
                                    data-target="{{ $this->totalUsers > 0 ? round(($this->totalActiveUsers / $this->totalUsers) * 100, 1) : 0 }}">
                                    {{ $this->totalUsers > 0 ? round(($this->totalActiveUsers / $this->totalUsers) * 100, 1) : 0 }}
                                </span>%
                            </h2>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-primary mb-0">
                                    <i class="ri-percent-line align-middle"></i> Of total users
                                </span>
                            </p>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle text-info rounded-circle fs-4">
                                    <i class="ri-bar-chart-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div>
        </div>
        <!--end col-->
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card" id="usersList">
                <div class="card-header border-0">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Users</h5>
                        <div class="flex-shrink-0">
                            <div class="d-flex flex-wrap gap-2">
                                <x-button color="primary" icon="ri-add-line" icon-position="left"
                                    wire:click="openInviteModal" wire-target="openInviteModal">
                                    Invite User
                                </x-button>
                                @if (!empty($selected))
                                    <x-button color="danger" icon="ri-delete-bin-line" icon-position="left"
                                        wire:click="deleteMultiple" wire-target="deleteMultiple">
                                        Delete ({{ count($selected) }})
                                    </x-button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body border border-dashed border-end-0 border-start-0">
                    <form wire:submit.prevent>
                        <div class="row g-3">
                            <div class="col-xxl-4 col-sm-12">
                                <div class="search-box">
                                    <input type="text" class="form-control search bg-light border-light"
                                        placeholder="Search by name or email..."
                                        wire:model.live.debounce.300ms="search">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <!--end col-->

                            <div class="col-xxl-3 ms-auto col-sm-4">
                                <input type="text" class="form-control bg-light border-light" id="user-date-filter"
                                    placeholder="Select date range" x-data="{
                                        init() {
                                            const fp = flatpickr(this.$el, {
                                                mode: 'range',
                                                dateFormat: 'd M, Y',
                                                onChange: (selectedDates, dateStr, instance) => {
                                                    if (selectedDates.length === 2) {
                                                        @this.set('dateFrom', selectedDates[0].toISOString().split('T')[0]);
                                                        @this.set('dateTo', selectedDates[1].toISOString().split('T')[0]);
                                                    } else if (selectedDates.length === 0) {
                                                        @this.set('dateFrom', null);
                                                        @this.set('dateTo', null);
                                                    }
                                                }
                                            });
                                            // Clear on reset
                                            Livewire.hook('message.processed', (message, component) => {
                                                if (@this.dateFrom === null && @this.dateTo === null) {
                                                    fp.clear();
                                                }
                                            });
                                        }
                                    }">
                            </div>
                            <!--end col-->

                            <div class="col-xxl-2 col-sm-4">
                                <div class="input-light">
                                    <select class="form-control" wire:model.live="status" id="userStatusFilter">
                                        <option value="all">All Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-2 col-sm-4">
                                <x-button color="primary" icon="ri-equalizer-fill" icon-position="left"
                                    wire:click="resetFilters" wire-target="resetFilters">
                                    Reset
                                </x-button>
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </form>
                </div>

                <div class="card-body">
                    @if ($selectPage && !$selectAll && $users->total() > $users->count())
                        <div class="alert alert-info py-2 mb-3">
                            You have selected <strong>{{ count($selected) }}</strong> users on this page.
                            <a href="#" wire:click.prevent="selectAllMatching" class="alert-link fw-bold">
                                Select all <strong>{{ $users->total() }}</strong> users?
                            </a>
                        </div>
                    @elseif($selectAll)
                        <div class="alert alert-success py-2 mb-3">
                            You have selected all <strong>{{ $users->total() }}</strong> users.
                        </div>
                    @endif

                    @if($users->isEmpty() && $this->hasActiveFilters)
                        {{-- No results found with active filters --}}
                        <div class="noresult">
                            <div class="text-center py-5">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Sorry! No Result Found</h5>
                                <p class="text-muted mb-0">
                                    We've searched through all users but did not find any users matching your search
                                    criteria.
                                </p>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary" wire:click="resetFilters">
                                        <i class="ri-refresh-line me-1 align-bottom"></i>
                                        Reset Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="table-responsive table-card mb-4">
                            <table class="table align-middle table-nowrap mb-0" id="usersTable">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    wire:model.live="selectPage">
                                            </div>
                                        </th>

                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="list" id="user-list-data">
                                    @forelse ($users as $user)
                                        <tr wire:key="user-row-{{ $user->id }}">
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model.live="selected"
                                                        value="{{ $user->id }}">
                                                </div>
                                            </th>

                                            <td class="name fw-medium">
                                                <ul class="list-group">


                                                    @php
                                                        $nameParts = array_filter([
                                                            $user->first_name,
                                                            $user->middle_name,
                                                            $user->last_name
                                                        ]);
                                                        $fullName = implode(' ', $nameParts);
                                                        if (!empty($user->name_extension)) {
                                                            $fullName .= ', ' . $user->name_extension;
                                                        }

                                                        $photoPath = $user->photo_path
                                                            ? (str_starts_with($user->photo_path, 'http') ? $user->photo_path : asset('storage/' . $user->photo_path))
                                                            : asset('build/images/users/user-dummy-img.jpg');

                                                    @endphp


                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <img wire:lazy loading="lazy" src="{{ $photoPath }}" alt=""
                                                                class="avatar-xs rounded-circle">
                                                        </div>
                                                        <div class="flex-grow-1 ms-2">
                                                            {{ $fullName ?: ($user->name ?? '-') }}
                                                        </div>
                                                    </div>

                                            </td>
                                            <td class="email">{{ $user->email }}</td>
                                            <td class="roles">
                                                @if($user->roles->isNotEmpty())
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($user->roles as $role)
                                                            <span class="badge bg-primary-subtle text-primary">
                                                                {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">No roles</span>
                                                @endif
                                            </td>
                                            <td class="status">
                                                <span
                                                    class="badge {{ $user->active_status ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                    {{ $user->active_status ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="created">{{ $user->created_at?->format('d M, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <x-button color="info" icon="ri-edit-line" icon-position="left" size="sm"
                                                        :iconOnly="true" tooltip="Edit User" tooltip-placement="top"
                                                        wire:click="editUser({{ $user->id }})"
                                                        wireTarget="editUser({{ $user->id }})">
                                                    </x-button>
                                                    <x-button color="danger" icon="ri-delete-bin-line" icon-position="left"
                                                        size="sm" :iconOnly="true" tooltip="Delete User" tooltip-placement="top"
                                                        wire:click="deleteUser({{ $user->id }})"
                                                        wireTarget="deleteUser({{ $user->id }})">
                                                    </x-button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No users found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <x-pagination :paginator="$users" :show-summary="true" />
                    @endif
                </div>
                <!--end card-body-->
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div>

    <!-- Invite User Modal -->
    @include('livewire.user.modals.invite-user-modal')

    <!-- Delete User Modal -->
    @include('livewire.user.modals.delete-modal')

    <!-- Delete Multiple Users Modal -->
    @include('livewire.user.modals.delete-multiple')

    <!-- Toast Notification Component (Listens for 'show-toast' browser events) -->

</div>