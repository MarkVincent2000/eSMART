<div>


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
                            </div>
                        </div>
                    </div>
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
                                    <th scope="col" style="width: 80px;">ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Joined</th>
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
                                        <td>#{{ $user->id }}</td>
                                        <td class="name fw-medium">{{ $user->name }}</td>
                                        <td class="email">{{ $user->email }}</td>
                                        <td class="status">
                                            <span
                                                class="badge {{ $user->active_status ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                                {{ $user->active_status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="created">{{ $user->created_at?->format('d M, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No users found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <x-pagination :paginator="$users" :show-summary="true" />
                </div>
                <!--end card-body-->
            </div>
            <!--end card-->
        </div>
        <!--end col-->
    </div>

    <!-- Invite User Modal -->
    <x-modal id="invite-user-modal" wire:model="showInviteModal" title="Invite User" size="lg" :centered="true"
        :show-footer="true">
        <form wire:submit.prevent="saveUser">
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        wire:model="name" placeholder="Enter full name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                        wire:model="email" placeholder="Enter email address">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password"
                        wire:model="password" placeholder="Enter password (min. 8 characters)">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="active_status" wire:model="active_status">
                        <label class="form-check-label" for="active_status">
                            Active Status
                        </label>
                    </div>
                    <small class="text-muted">Uncheck to create user as inactive</small>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-light" x-on:click="show = false">Cancel</button>
            <x-button color="primary" wire:click="saveUser" wire-target="saveUser">
                <span wire:loading.remove wire:target="saveUser">Invite User</span>
                <span wire:loading wire:target="saveUser">Inviting...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>



</div>