<div>

    <x-toast-notification />

    <div class="row">
        <div class="col-xxl-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Total Roles</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $this->totalRoles }}">
                                    {{ $this->totalRoles }}
                                </span>
                            </h2>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-info mb-0">
                                    <i class="ri-shield-user-line align-middle"></i> All roles
                                </span>
                            </p>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">
                                    <i class="ri-shield-user-line"></i>
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
                            <p class="fw-medium text-muted mb-0">Total Users</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $this->totalUsers }}">
                                    {{ $this->totalUsers }}
                                </span>
                            </h2>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-success mb-0">
                                    <i class="ri-user-line align-middle"></i> Users
                                </span>
                            </p>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                                    <i class="ri-user-line"></i>
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
            <div class="card" id="rolesList">
                <div class="card-header border-0">
                    <div class="d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">Roles</h5>
                        <div class="flex-shrink-0">
                            <div class="d-flex flex-wrap gap-2">
                                <x-button color="primary" icon="ri-add-line" icon-position="left"
                                    wire:click="openAddModal" wire-target="openAddModal">
                                    Add Role
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
                            <div class="col-xxl-5 col-sm-12">
                                <div class="search-box">
                                    <input type="text" class="form-control search bg-light border-light"
                                        placeholder="Search by role name..." wire:model.live.debounce.300ms="search">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <!--end col-->

                            <div class="col-xxl-3 col-sm-4">
                                <div class="input-light">
                                    <select class="form-control" wire:model.live="guardName" id="guardNameFilter">
                                        <option value="all">All Guards</option>
                                        <option value="web">Web</option>
                                        <option value="api">API</option>
                                    </select>
                                </div>
                            </div>
                            <!--end col-->
                            <div class="col-xxl-1 col-sm-4">
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
                    @if ($selectPage && !$selectAll && $roles->total() > $roles->count())
                        <div class="alert alert-info py-2 mb-3">
                            You have selected <strong>{{ count($selected) }}</strong> roles on this page.
                            <a href="#" wire:click.prevent="selectAllMatching" class="alert-link fw-bold">
                                Select all <strong>{{ $roles->total() }}</strong> roles?
                            </a>
                        </div>
                    @elseif($selectAll)
                        <div class="alert alert-success py-2 mb-3">
                            You have selected all <strong>{{ $roles->total() }}</strong> roles.
                        </div>
                    @endif

                    @if($roles->isEmpty() && $this->hasActiveFilters)
                        {{-- No results found with active filters --}}
                        <div class="noresult">
                            <div class="text-center py-5">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Sorry! No Result Found</h5>
                                <p class="text-muted mb-0">
                                    We've searched through all roles but did not find any roles matching your search
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
                            <table class="table align-middle table-nowrap mb-0" id="rolesTable">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 40px;">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                    wire:model.live="selectPage">
                                            </div>
                                        </th>

                                        <th>Role Name</th>
                                        <th>Guard Name</th>
                                        <th>Users Count</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="list" id="role-list-data">
                                    @forelse ($roles as $role)
                                        <tr wire:key="role-row-{{ $role->id }}">
                                            <th scope="row">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model.live="selected"
                                                        value="{{ $role->id }}">
                                                </div>
                                            </th>

                                            <td class="name fw-medium">
                                                <span class="badge bg-primary-subtle text-primary fs-6">
                                                    {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                                                </span>
                                            </td>
                                            <td class="guard">
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ $role->guard_name }}
                                                </span>
                                            </td>
                                            <td class="users-count">
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-user-line align-middle"></i> {{ $role->users_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td class="created">{{ $role->created_at?->format('d M, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <x-button color="info" icon="ri-edit-line" icon-position="left" size="sm"
                                                        :iconOnly="true" tooltip="Edit Role" tooltip-placement="top"
                                                        wire:click="editRole({{ $role->id }})"
                                                        wire:target="editRole({{ $role->id }})">
                                                    </x-button>
                                                    <x-button color="danger" icon="ri-delete-bin-line" icon-position="left"
                                                        size="sm" :iconOnly="true" tooltip="Delete Role" tooltip-placement="top"
                                                        wire:click="deleteRole({{ $role->id }})"
                                                        wire:target="deleteRole({{ $role->id }})">
                                                    </x-button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No roles found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <x-pagination :paginator="$roles" :show-summary="true" />
                    @endif
                </div>
                <!--end card-body-->
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div>

    <!-- Create/Edit Role Modal -->
    @include('livewire.role.modals.create-role-modal')

    <!-- Delete Role Modal -->
    @include('livewire.role.modals.delete-modal')

    <!-- Delete Multiple Roles Modal -->
    @include('livewire.role.modals.delete-multiple')

</div>