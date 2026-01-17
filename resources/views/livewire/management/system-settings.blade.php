<div>
    <x-toast-notification />

    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex mb-3">
                        <div class="flex-grow-1">
                            <h5 class="fs-16">Filters</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="#" class="text-decoration-underline" wire:click.prevent="clearAllFilters">Clear
                                All</a>
                        </div>
                    </div>
                </div>

                <div class="accordion accordion-flush filter-accordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingGroups">
                            <button class="accordion-button bg-transparent shadow-none" type="button"
                                data-bs-toggle="collapse" data-bs-target="#flush-collapseGroups" aria-expanded="true"
                                aria-controls="flush-collapseGroups">
                                <span class="text-muted text-uppercase fs-12 fw-medium">Groups</span>
                                <span class="badge bg-success rounded-pill align-middle ms-1 filter-badge">
                                    {{ count($selectedGroups) }}
                                </span>
                            </button>
                        </h2>

                        <div id="flush-collapseGroups" class="accordion-collapse collapse show"
                            aria-labelledby="flush-headingGroups">
                            <div class="accordion-body text-body pt-0">
                                <div class="d-flex flex-column gap-2 filter-check">
                                    @forelse($filteredGroups as $group)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="{{ $group }}"
                                                id="group_{{ Str::slug($group) }}" wire:model.change="selectedGroups">
                                            <label class="form-check-label d-flex align-items-center gap-2 small"
                                                for="group_{{ Str::slug($group) }}">
                                                <span class="small">{{ $group }}</span>
                                                @if(isset($groupCounts[$group]))
                                                    <span class="badge bg-primary-subtle text-primary small">
                                                        {{ $groupCounts[$group] }}
                                                    </span>
                                                @endif
                                            </label>
                                        </div>
                                    @empty
                                        <div class="text-muted text-center py-2">
                                            <small>No groups found</small>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-lg-8">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row g-4">
                        <div class="col-sm-auto">
                            <div>
                                <x-button color="primary" icon="ri-add-line" icon-position="left"
                                    wire:click="openCreateModal">
                                    Add Setting
                                </x-button>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="d-flex justify-content-sm-end">
                                <div class="search-box ms-2">
                                    <input type="text" class="form-control" id="searchSettings"
                                        placeholder="Search settings..." wire:model.live.debounce.300ms="search">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if ($paginatedSettings->isEmpty())
                        <div class="text-center py-5">
                            <div class="avatar-md mx-auto mb-4">
                                <div
                                    class="avatar-title {{ !empty($search) || !empty($selectedGroups) ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' }} rounded-circle fs-24">
                                    <i
                                        class="{{ !empty($search) || !empty($selectedGroups) ? 'ri-search-line' : 'ri-settings-3-line' }}"></i>
                                </div>
                            </div>
                            <h5>No Settings Found</h5>
                            <p class="text-muted">
                                @if (!empty($search) || !empty($selectedGroups))
                                    No settings match your current filters.
                                @else
                                    Get started by creating your first system setting.
                                @endif
                            </p>
                            @if (!empty($search) || !empty($selectedGroups))
                                <button class="btn btn-primary" wire:click="clearAllFilters">
                                    <i class="ri-close-line align-bottom me-1"></i> Clear Filters
                                </button>
                            @else
                                <button class="btn btn-primary" wire:click="openCreateModal">
                                    <i class="ri-add-line align-bottom me-1"></i> Add Setting
                                </button>
                            @endif
                        </div>
                    @else
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
                                    @foreach ($paginatedSettings as $index => $setting)
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($paginatedSettings->hasPages())
                            <div class="mt-4">
                                <x-pagination :paginator="$paginatedSettings" />
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Setting Modal -->
    <x-modal wire:model="showSettingModal" :title="$settingId ? 'Edit Setting' : 'Create Setting'" size="lg"
        :show-footer="true">
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