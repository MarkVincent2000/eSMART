<div class="modal fade" id="userCreateEditModal" tabindex="-1" data-bs-backdrop="static"
    aria-labelledby="userCreateEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userCreateEditLabel">Create User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Front-end only: wire this form to backend later --}}
                <form id="userCreateEditForm" method="POST" action="{{ route('user-management.users.store') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        {{-- Avatar on top, centered --}}
                        <div class="col-12 d-flex flex-column align-items-center mb-2">
                            <label for="user-avatar" class="form-label mb-2">Avatar</label>
                            {{-- FilePond-enhanced file input for avatar upload (front-end only) --}}
                            <div class="avatar-xl">
                                <input type="file" class="filepond filepond-input-circle" id="user-avatar" name="avatar"
                                    accept="image/png, image/jpeg, image/gif" />
                            </div>
                        </div>

                        {{-- Text inputs below avatar --}}
                        <div class="col-md-6">
                            <label for="user-name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="user-name" name="name"
                                placeholder="Enter full name">
                        </div>
                        <div class="col-md-6">
                            <label for="user-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="user-email" name="email"
                                placeholder="Enter email address">
                        </div>
                        <div class="col-md-6">
                            <label for="user-password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="user-password" name="password"
                                placeholder="Enter password">
                        </div>
                        <div class="col-md-6">
                            <label for="user-password-confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="user-password-confirmation"
                                name="password_confirmation" placeholder="Re-type password">
                        </div>
                        <div class="col-md-6">
                            <label for="user-status" class="form-label">Status</label>
                            <select class="form-control" id="user-status" name="active_status">
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line align-middle me-1"></i> Cancel
                </button>
                {{-- For now just front-end – no submit handler --}}
                <button type="button" class="btn btn-primary" id="userSaveBtn">
                    <i class="ri-save-3-line align-middle me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.FilePond) {
            // Register the same plugins used in your asset form-file-upload.init.js
            FilePond.registerPlugin(
                FilePondPluginFileEncode,
                FilePondPluginFileValidateSize,
                FilePondPluginImageExifOrientation,
                FilePondPluginImagePreview
            );

            const circleInput = document.querySelector('.filepond-input-circle');
            if (circleInput) {
                FilePond.create(circleInput, {
                    labelIdle: 'Drag & Drop your picture or <span class="filepond--label-action">Browse</span>',
                    imagePreviewHeight: 170,
                    imageCropAspectRatio: '1:1',
                    imageResizeTargetWidth: 200,
                    imageResizeTargetHeight: 200,
                    stylePanelLayout: 'compact circle',
                    styleLoadIndicatorPosition: 'center bottom',
                    styleProgressIndicatorPosition: 'right bottom',
                    styleButtonRemoveItemPosition: 'left bottom',
                    styleButtonProcessItemPosition: 'right bottom',
                });
            }
        }

        // Submit the form when Save button is clicked
        const saveBtn = document.getElementById('userSaveBtn');
        const form = document.getElementById('userCreateEditForm');
        if (saveBtn && form) {
            saveBtn.addEventListener('click', function () {
                form.submit();
            });
        }
    });
</script>