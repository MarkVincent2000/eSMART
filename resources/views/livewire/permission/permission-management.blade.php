<div>
    <x-toast-notification />

    <div class="row">
        <div class="col-xxl-3 col-sm-6">
            <div class="card card-animate">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="fw-medium text-muted mb-0">Total Permissions</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $this->totalPermissions }}">
                                    {{ $this->totalPermissions }}
                                </span>
                            </h2>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-info mb-0">
                                    <i class="ri-shield-check-line align-middle"></i> All permissions
                                </span>
                            </p>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle text-primary rounded-circle fs-4">
                                    <i class="ri-shield-check-line"></i>
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
                            <p class="fw-medium text-muted mb-0">Total Roles</p>
                            <h2 class="mt-4 ff-secondary fw-semibold">
                                <span class="counter-value" data-target="{{ $this->totalRoles }}">
                                    {{ $this->totalRoles }}
                                </span>
                            </h2>
                            <p class="mb-0 text-muted">
                                <span class="badge bg-light text-success mb-0">
                                    <i class="ri-shield-user-line align-middle"></i> Roles
                                </span>
                            </p>
                        </div>
                        <div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-4">
                                    <i class="ri-shield-user-line"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div><!-- end card body -->
            </div>
        </div>
        <!--end col-->
    </div>


    <div class="row g-4 mb-3">
        <div class="col-sm-auto">
            <div>
                <x-button color="success" icon="ri-add-line" icon-position="left" wire:click="openAddModal"
                    wire-target="openAddModal">
                    Add Permission
                </x-button>
            </div>
        </div>
        <div class="col-sm-auto ms-sm-auto">
            <div class="d-flex gap-2 flex-wrap">
                <div class="search-box">
                    <input type="text" class="form-control" placeholder="Search permissions..."
                        wire:model.live.debounce.300ms="search" style="min-width: 200px;">
                    <i class="ri-search-line search-icon"></i>
                </div>

                <select class="form-control" wire:model.live="guardName" style="width: auto; min-width: 150px;">
                    <option value="all">All Guards</option>
                    <option value="web">Web</option>
                    <option value="api">API</option>
                </select>

                @if($this->hasActiveFilters)
                    <x-button color="primary" icon="ri-equalizer-fill" icon-position="left" wire:click="resetFilters"
                        wire-target="resetFilters">
                        Reset
                    </x-button>
                @endif
            </div>
        </div>
    </div>

    @if($permissions->isEmpty() && $this->hasActiveFilters)
        <div class="noresult">
            <div class="text-center py-5">
                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                    colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                </lord-icon>
                <h5 class="mt-2">Sorry! No Result Found</h5>
                <p class="text-muted mb-0">
                    We've searched through all permissions but did not find any permissions matching your search
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
        <div class="row">
            @forelse($permissions as $permission)
                <div class="col-xxl-3 col-sm-6">
                    <div class="card card-height-100">
                        <div class="card-body">
                            <div class="d-flex flex-column h-100">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-4">
                                            Created {{ $permission->created_at?->diffForHumans() ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="dropdown">
                                            <button class="btn btn-soft-secondary btn-sm dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        wire:click="editPermission({{ $permission->id }})"
                                                        wire:target="editPermission({{ $permission->id }})">
                                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#"
                                                        wire:click="deletePermission({{ $permission->id }})"
                                                        wire:target="deletePermission({{ $permission->id }})">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i>
                                                        Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex mb-2">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-sm">
                                            <span class="avatar-title bg-primary-subtle rounded p-2">
                                                <i class="ri-shield-check-line text-primary fs-4"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1 fs-15">
                                            <span
                                                class="text-body">{{ ucfirst(str_replace(['-', '_'], ' ', $permission->name)) }}</span>
                                        </h5>
                                        <p class="text-muted mb-2">
                                            <span class="badge bg-info-subtle text-info">{{ $permission->guard_name }}</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-auto">
                                    <div class="d-flex mb-2">
                                        <div class="flex-grow-1">
                                            <div class="text-muted">Roles with this permission</div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <div>
                                                <i class="ri-shield-user-line align-bottom me-1 text-muted"></i>
                                                <strong>{{ $permission->roles_count ?? 0 }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end card body -->
                        <div class="card-footer bg-transparent border-top-dashed py-2">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <div class="text-muted">
                                        <i class="ri-calendar-event-fill me-1 align-bottom"></i>
                                        {{ $permission->created_at?->format('d M, Y') ?? 'N/A' }}
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $permission->name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- end card footer -->
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col -->
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                            colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                        </lord-icon>
                        <h5 class="mt-2">No Permissions Found</h5>
                        <p class="text-muted mb-0">There are no permissions in the system yet.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <x-pagination :paginator="$permissions" :show-summary="true" />
    @endif

    <!-- Create/Edit Permission Modal -->
    @include('livewire.permission.modals.create-permission-modal')

    <!-- Delete Permission Modal -->
    @include('livewire.permission.modals.delete-modal')

</div>