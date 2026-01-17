<div>
    <x-toast-notification />

    <div class="card">
        <div class="card-header border-0">
            <div class="row g-4">
                <div class="col-sm-auto">
                    <div>
                        <x-button color="primary" icon="ri-add-line" icon-position="left" wire:click="openCreateModal">
                            Add Setting
                        </x-button>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="d-flex justify-content-sm-end">
                        <div class="search-box ms-2">
                            <input type="text" class="form-control" id="searchSettings" placeholder="Search settings..."
                                wire:model.live.debounce.300ms="search">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <ul class="nav nav-tabs-custom card-header-tabs border-bottom-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active fw-semibold" data-bs-toggle="tab" href="#settings-all" role="tab">
                                All <span
                                    class="badge bg-danger-subtle text-danger align-middle rounded-pill ms-1">{{ $paginatedSettings->total() }}</span>
                            </a>
                        </li>
                        @foreach ($groupedSettings as $group => $groupSettings)
                            @php
                                $groupName = $group ?? 'General';
                                $groupSlug = Str::slug($groupName);
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#settings-{{ $groupSlug }}"
                                    role="tab">
                                    {{ $groupName }} <span
                                        class="badge bg-danger-subtle text-danger align-middle rounded-pill ms-1">{{ $groupSettings->count() }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <!-- end card header -->

        <div class="card-body">
            @if ($paginatedSettings->isEmpty() && $groupedSettings->isEmpty())
                <div class="text-center py-5">
                    <div class="avatar-md mx-auto mb-4">
                        <div
                            class="avatar-title {{ !empty($search) ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' }} rounded-circle fs-24">
                            <i class="{{ !empty($search) ? 'ri-search-line' : 'ri-settings-3-line' }}"></i>
                        </div>
                    </div>
                    <h5>No Settings Found</h5>
                    <p class="text-muted">
                        @if (!empty($search))
                            No settings match your search "{{ $search }}".
                        @else
                            Get started by creating your first system setting.
                        @endif
                    </p>
                    @if (!empty($search))
                        <button class="btn btn-primary" wire:click="$set('search', '')">
                            <i class="ri-close-line align-bottom me-1"></i> Clear Search
                        </button>
                    @else
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSettingModal">
                            <i class="ri-add-line align-bottom me-1"></i> Add Setting
                        </button>
                    @endif
                </div>
            @else
                <div class="tab-content text-muted">
                    <div class="tab-pane active" id="settings-all" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-nowrap align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" style="width: 50px;">#</th>
                                        <th scope="col">Key</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Value</th>
                                        <th scope="col">Type</th>
                                        <th scope="col">Group</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($paginatedSettings as $index => $setting)
                                        <tr wire:key="setting-row-{{ $setting->id }}">
                                            <td>{{ $paginatedSettings->firstItem() + $index }}</td>
                                            <td>
                                                <code class="text-primary">{{ $setting->key }}</code>
                                            </td>
                                            <td>
                                                <span class="fw-medium">{{ $setting->name }}</span>
                                            </td>
                                            <td>
                                                @if ($setting->type === 'file' && $setting->value)
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="{{ asset($setting->value) }}" alt="{{ $setting->name }}"
                                                            class="img-thumbnail rounded"
                                                            style="max-width: 50px; max-height: 50px; object-fit: contain;"
                                                            onerror="this.style.display='none'">
                                                        <span class="text-muted small">{{ Str::limit($setting->value, 30) }}</span>
                                                    </div>
                                                @elseif ($setting->type === 'textarea')
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                        title="{{ $setting->value }}">
                                                        {{ Str::limit($setting->value ?? 'No value set', 50) }}
                                                    </span>
                                                @else
                                                    <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                        title="{{ $setting->value }}">
                                                        {{ Str::limit($setting->value ?? 'No value set', 50) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info">
                                                    {{ ucfirst($setting->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $groupName = $setting->group ?? 'General';
                                                @endphp
                                                <span class="badge bg-primary-subtle text-primary">{{ $groupName }}</span>
                                            </td>
                                            <td>
                                                @if ($setting->is_locked)
                                                    <span class="badge bg-warning-subtle text-warning">
                                                        <i class="ri-lock-line align-middle"></i> Locked
                                                    </span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success">
                                                        <i class="ri-edit-line align-middle"></i> Editable
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a href="javascript:void(0);" class="btn btn-soft-secondary btn-sm"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ri-more-fill align-middle"></i>
                                                    </a>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="javascript:void(0);"
                                                                wire:click="editSetting({{ $setting->id }})">
                                                                <i class="ri-edit-2-line align-bottom me-2 text-muted"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                wire:click="confirmDelete({{ $setting->id }})">
                                                                <i
                                                                    class="ri-delete-bin-5-line align-bottom me-2 text-muted"></i>
                                                                Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="ri-inbox-line fs-48 mb-3 d-block"></i>
                                                    No settings found
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($paginatedSettings->hasPages())
                            <div class="mt-4">
                                <x-pagination :paginator="$paginatedSettings" />
                            </div>
                        @endif

                    </div>


                    <!-- end tab pane -->

                    @foreach ($groupedSettings as $group => $groupSettings)
                        @php
                            $groupName = is_object($group) && method_exists($group, 'value') ? $group->value : ($group ?? 'General');
                            $groupSlug = Str::slug($groupName);
                        @endphp
                        <div class="tab-pane" id="settings-{{ $groupSlug }}" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-nowrap align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">#</th>
                                            <th scope="col">Key</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Value</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Status</th>
                                            <th scope="col" style="width: 120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $groupIndex = 1;
                                        @endphp
                                        @foreach ($groupSettings as $setting)
                                            <tr wire:key="setting-{{ $groupSlug }}-{{ $setting->id }}">
                                                <td>{{ $groupIndex++ }}</td>
                                                <td>
                                                    <code class="text-primary">{{ $setting->key }}</code>
                                                </td>
                                                <td>
                                                    <span class="fw-medium">{{ $setting->name }}</span>
                                                </td>
                                                <td>
                                                    @if ($setting->type === 'file' && $setting->value)
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img src="{{ asset($setting->value) }}" alt="{{ $setting->name }}"
                                                                class="img-thumbnail rounded"
                                                                style="max-width: 50px; max-height: 50px; object-fit: contain;"
                                                                onerror="this.style.display='none'">
                                                            <span class="text-muted small">{{ Str::limit($setting->value, 30) }}</span>
                                                        </div>
                                                    @elseif ($setting->type === 'textarea')
                                                        <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                            title="{{ $setting->value }}">
                                                            {{ Str::limit($setting->value ?? 'No value set', 50) }}
                                                        </span>
                                                    @else
                                                        <span class="text-truncate d-inline-block" style="max-width: 200px;"
                                                            title="{{ $setting->value }}">
                                                            {{ Str::limit($setting->value ?? 'No value set', 50) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-info-subtle text-info">
                                                        {{ ucfirst($setting->type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($setting->is_locked)
                                                        <span class="badge bg-warning-subtle text-warning">
                                                            <i class="ri-lock-line align-middle"></i> Locked
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success">
                                                            <i class="ri-edit-line align-middle"></i> Editable
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <a href="javascript:void(0);" class="btn btn-soft-secondary btn-sm"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="ri-more-fill align-middle"></i>
                                                        </a>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item" href="javascript:void(0);"
                                                                    wire:click="editSetting({{ $setting->id }})">
                                                                    <i class="ri-edit-2-line align-bottom me-2 text-muted"></i> Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                                    wire:click="confirmDelete({{ $setting->id }})">
                                                                    <i
                                                                        class="ri-delete-bin-5-line align-bottom me-2 text-muted"></i>
                                                                    Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- end tab pane -->
                    @endforeach
                </div>
                <!-- end tab content -->
            @endif
        </div>
        <!-- end card body -->
    </div>
    <!-- end card -->

    <!-- Create/Edit Setting Modal -->
    <x-modal wire:model="showSettingModal" :title="$settingId ? 'Edit Setting' : 'Create Setting'" size="lg"
        :show-footer="true" close-on-backdrop>
        <form wire:submit.prevent="saveSetting">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="key" class="form-label">Key <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('key') is-invalid @enderror" id="key" wire:model="key"
                        placeholder="e.g., site.logo" {{ $settingId ? 'readonly' : '' }}>
                    @error('key')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Unique identifier for the
                        setting{{ $settingId ? ' (cannot be changed)' : '' }}</small>
                </div>

                <div class="col-md-6">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        wire:model="name" placeholder="e.g., Site Logo">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Human-readable name</small>
                </div>

                <div class="col-md-6">
                    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" wire:model.live="type">
                        <option value="text">Text</option>
                        <option value="textarea">Textarea</option>
                        <option value="file">File</option>
                        <option value="number">Number</option>
                        <option value="boolean">Boolean</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="group" class="form-label">Group <span class="text-danger">*</span></label>
                    <select class="form-select @error('group') is-invalid @enderror" id="group" wire:model="group">
                        <option value="">Select Group</option>
                        @foreach (App\Enums\SystemSettingGroup::options() as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @error('group')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if ($type === 'file')
                    <div class="col-md-12" wire:key="value-field-file">
                        <label for="file" class="form-label">File <span
                                class="text-danger">{{ $settingId ? '' : '*' }}</span></label>
                        <div wire:ignore>
                            <div x-data x-init="
                                            FilePond.registerPlugin(FilePondPluginImagePreview);
                                            FilePond.registerPlugin(FilePondPluginFileValidateSize);
                                            FilePond.registerPlugin(FilePondPluginImageExifOrientation);
                                            FilePond.registerPlugin(FilePondPluginFileEncode);

                                            FilePond.setOptions({
                                                allowMultiple: false,
                                                maxFileSize: '2MB',
                                                acceptedFileTypes: ['image/*'],
                                                server: {
                                                    process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                                                        @this.upload('file', file, load, error, progress);
                                                    },
                                                    revert: (filename, load) => {
                                                        @this.removeUpload('file', filename);
                                                        load();
                                                    }
                                                },
                                                labelIdle: 'Drag & Drop your image or Browse',
                                                labelMaxFileSizeExceeded: 'File is too large',
                                                labelMaxFileSize: 'Maximum file size is 2MB',
                                                imagePreviewHeight: 170,
                                                credits: false
                                            });

                                            FilePond.create($refs.fileInput);
                                        ">
                                <input type="file" x-ref="fileInput" class="@error('file') is-invalid @enderror"
                                    accept="image/*">
                            </div>
                        </div>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if ($settingId && $value && !$file)
                            <small class="text-muted d-block mt-1">Current file:
                                <a href="{{ asset($value) }}" target="_blank">{{ $value }}</a>
                            </small>
                        @endif
                        @if ($file)
                            <small class="text-muted d-block mt-1">New file selected:
                                {{ $file->getClientOriginalName() }}</small>
                        @endif
                    </div>
                @elseif ($type === 'textarea')
                    <div class="col-md-12" wire:key="value-field-textarea">
                        <label for="value" class="form-label">Value</label>
                        <textarea class="form-control @error('value') is-invalid @enderror" id="value" wire:model="value"
                            rows="4" placeholder="Enter value..."></textarea>
                        @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @elseif ($type === 'boolean')
                    <div class="col-md-12" wire:key="value-field-boolean">
                        <label for="value" class="form-label">Value</label>
                        <select class="form-select @error('value') is-invalid @enderror" id="value" wire:model="value">
                            <option value="0">False</option>
                            <option value="1">True</option>
                        </select>
                        @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @else
                    <div class="col-md-12" wire:key="value-field-{{ $type }}">
                        <label for="value" class="form-label">Value</label>
                        <input type="{{ $type === 'number' ? 'number' : 'text' }}"
                            class="form-control @error('value') is-invalid @enderror" id="value" wire:model="value"
                            placeholder="Enter value...">
                        @error('value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif

                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="isLocked" wire:model="isLocked">
                        <label class="form-check-label" for="isLocked">
                            Locked (prevents modification)
                        </label>
                    </div>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <x-button color="light" wire:click="closeSettingModal" wireTarget="closeSettingModal">Cancel</x-button>
            <x-button color="primary" wire:click="saveSetting" wireTarget="saveSetting">
                {{ $settingId ? 'Update' : 'Create' }} Setting
            </x-button>
        </x-slot:footer>
    </x-modal>

    <!-- Delete Confirmation Modal -->
    <x-modal wire:model="showDeleteModal" title="Delete Setting" size="sm">
        <div class="text-center">
            <div class="mb-4">
                <div class="avatar-md mx-auto mb-4">
                    <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-24">
                        <i class="ri-delete-bin-5-line"></i>
                    </div>
                </div>
                <h5 class="mb-2">Are you sure?</h5>
                <p class="text-muted mb-0">
                    Are you sure you want to delete the setting <strong>"{{ $deleteSettingName }}"</strong>?
                    This action cannot be undone.
                </p>
            </div>
        </div>

        <x-slot:footer>
            <x-button color="light" wire:click="closeDeleteModal" wireTarget="closeDeleteModal">Cancel</x-button>
            <x-button color="danger" wire:click="deleteSetting" wireTarget="deleteSetting">
                <i class="ri-delete-bin-5-line align-bottom me-1"></i> Delete
            </x-button>
        </x-slot:footer>
    </x-modal>



</div>