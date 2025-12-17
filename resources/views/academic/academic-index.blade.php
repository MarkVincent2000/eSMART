@extends('layouts.master')
@section('title')
    Academic Timeline
@endsection
@section('content')
@component('components.breadcrumb')
@slot('li_1')
Academic
@endslot
@slot('title')
Academic Timeline
@endslot
@endcomponent

<style>
    /* Academic Timeline Custom Styles */
    .semester-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.08);
        overflow: hidden;
        position: relative;
    }

    .semester-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, var(--semester-color) 0%, var(--semester-color-dark) 100%);
        transition: width 0.3s ease;
    }

    .semester-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
    }

    .semester-card:hover::before {
        width: 6px;
    }

    .semester-card.first-semester {
        --semester-color: #405189;
        --semester-color-dark: #2d3a5f;
    }

    .semester-card.second-semester {
        --semester-color: #0ab39c;
        --semester-color-dark: #088f7a;
    }

    .semester-header-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .semester-header-icon::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s ease, height 0.6s ease;
    }

    .semester-card:hover .semester-header-icon::before {
        width: 200px;
        height: 200px;
    }

    .semester-card:hover .semester-header-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .quarter-item {
        transition: all 0.3s ease;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 8px;
        background: rgba(0, 0, 0, 0.02);
        border-left: 3px solid transparent;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .quarter-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 3px;
        height: 100%;
        background: var(--quarter-color);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .quarter-item:hover {
        background: rgba(0, 0, 0, 0.04);
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .quarter-item:hover::before {
        transform: scaleY(1);
    }

    .quarter-item.active {
        border-left-color: var(--quarter-color);
        background: rgba(var(--quarter-color-rgb), 0.08);
    }

    .quarter-item.active::before {
        transform: scaleY(1);
    }

    .quarter-badge {
        transition: all 0.3s ease;
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 12px;
        font-weight: 500;
    }

    .quarter-item:hover .quarter-badge {
        transform: scale(1.1);
    }

    .semester-stats {
        display: flex;
        gap: 16px;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
    }

    .stat-item {
        flex: 1;
        text-align: center;
        padding: 12px;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .stat-item:hover {
        background: rgba(0, 0, 0, 0.04);
        transform: translateY(-2px);
    }

    .stat-value {
        font-size: 20px;
        font-weight: 600;
        color: var(--semester-color);
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .empty-state {
        padding: 40px 20px;
        text-align: center;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 16px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(0, 0, 0, 0.05) 0%, rgba(0, 0, 0, 0.02) 100%);
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.8;
        }
    }

    .duration-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(0, 0, 0, 0.04);
        border-radius: 20px;
        font-size: 13px;
        margin-bottom: 16px;
    }

    .fade-in {
        animation: fadeIn 0.6s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }

    /* Edit Button Styles */
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .btn-icon:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-soft-primary {
        background-color: rgba(64, 81, 137, 0.1);
        border-color: rgba(64, 81, 137, 0.2);
        color: #405189;
    }

    .btn-soft-primary:hover {
        background-color: rgba(64, 81, 137, 0.2);
        border-color: rgba(64, 81, 137, 0.3);
        color: #405189;
    }

    .btn-soft-info {
        background-color: rgba(10, 179, 156, 0.1);
        border-color: rgba(10, 179, 156, 0.2);
        color: #0ab39c;
    }

    .btn-soft-info:hover {
        background-color: rgba(10, 179, 156, 0.2);
        border-color: rgba(10, 179, 156, 0.3);
        color: #0ab39c;
    }
</style>

<x-toast-notification />


<div class="card">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-lg-auto">
                <div class="hstack gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createboardModal"><i class="ri-add-line align-bottom me-1"></i> Create Board</button>
                </div>
            </div>
            <!--end col-->
            <div class="col-lg-3 col-auto">
                <div class="search-box">
                    <input type="text" class="form-control search" id="search-task-options" placeholder="Search for project, tasks...">
                    <i class="ri-search-line search-icon"></i>
                </div>
            </div>
            <!--end col-->
        </div>
        <!--end row-->
    </div>
    <!--end card-body-->
</div>

<div class="row">
    <!-- 1st Semester Card -->
    <div class="col-xl-6 mb-4">
        <div class="card semester-card first-semester fade-in stagger-1" data-semester="first">
            <div class="card-header bg-transparent border-bottom">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="semester-header-icon bg-primary-subtle">
                            <i class="ri-calendar-line text-primary fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-1 fw-semibold" id="first-semester-name">1st Semester</h5>
                        <p class="text-muted mb-0 small">
                            <i class="ri-calendar-event-line align-middle me-1"></i>
                            School Year: <span id="first-semester-year">2024-2025</span>
                        </p>
                    </div>
                    <div class="flex-shrink-0 d-flex align-items-center gap-2">
                        <button 
                            type="button" 
                            class="btn btn-sm btn-soft-primary btn-icon edit-semester-btn" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editSemesterModal"
                            data-semester-id=""
                            data-semester-type="first"
                            title="Edit Semester"
                            id="first-semester-edit-btn"
                            style="display: none;"
                        >
                            <i class="ri-pencil-fill"></i>
                        </button>
                        <span class="badge bg-secondary-subtle text-secondary" id="first-semester-status">
                            <i class="ri-time-line align-middle me-1"></i>Loading...
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="duration-badge">
                    <i class="ri-calendar-2-line text-primary"></i>
                    <span><strong>Duration:</strong> <span id="first-semester-duration">Loading...</span></span>
                </div>

                <div class="mb-3">
                    <h6 class="mb-3 d-flex align-items-center">
                        <i class="ri-list-check-2 align-middle me-2 text-primary"></i>
                        <span>Quarters</span>
                        <span class="badge bg-primary-subtle text-primary ms-2" id="first-semester-quarters-count">0</span>
                    </h6>
                    <div class="quarters-list" id="first-semester-quarters">
                        <!-- Quarters will be populated dynamically via JavaScript -->
                    </div>
                </div>

                <div class="semester-stats">
                    <div class="stat-item">
                        <div class="stat-value" style="color: #405189;" id="first-semester-stat-quarters">0</div>
                        <div class="stat-label">Quarters</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" style="color: #405189;" id="first-semester-stat-active">0</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" style="color: #405189;" id="first-semester-stat-days">0</div>
                        <div class="stat-label">Days</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end col -->

    <!-- 2nd Semester Card -->
    <div class="col-xl-6 mb-4">
        <div class="card semester-card second-semester fade-in stagger-1" data-semester="second">
            <div class="card-header bg-transparent border-bottom">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="semester-header-icon bg-info-subtle">
                            <i class="ri-calendar-line text-info fs-3"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h5 class="card-title mb-1 fw-semibold" id="second-semester-name">2nd Semester</h5>
                        <p class="text-muted mb-0 small">
                            <i class="ri-calendar-event-line align-middle me-1"></i>
                            School Year: <span id="second-semester-year">2024-2025</span>
                        </p>
                    </div>
                    <div class="flex-shrink-0 d-flex align-items-center gap-2">
                        <button 
                            type="button" 
                            class="btn btn-sm btn-soft-info btn-icon edit-semester-btn" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editSemesterModal"
                            data-semester-id=""
                            data-semester-type="second"
                            title="Edit Semester"
                            id="second-semester-edit-btn"
                            style="display: none;"
                        >
                            <i class="ri-pencil-fill"></i>
                        </button>
                        <span class="badge bg-secondary-subtle text-secondary" id="second-semester-status">
                            <i class="ri-time-line align-middle me-1"></i>Loading...
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="duration-badge">
                    <i class="ri-calendar-2-line text-info"></i>
                    <span><strong>Duration:</strong> <span id="second-semester-duration">Loading...</span></span>
                </div>

                <div class="mb-3">
                    <h6 class="mb-3 d-flex align-items-center">
                        <i class="ri-list-check-2 align-middle me-2 text-info"></i>
                        <span>Quarters</span>
                        <span class="badge bg-info-subtle text-info ms-2" id="second-semester-quarters-count">0</span>
                    </h6>
                    <div class="quarters-list" id="second-semester-quarters">
                        <!-- Quarters will be populated dynamically via JavaScript -->
                    </div>
                </div>

                <div class="semester-stats">
                    <div class="stat-item">
                        <div class="stat-value" style="color: #0ab39c;" id="second-semester-stat-quarters">0</div>
                        <div class="stat-label">Quarters</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" style="color: #0ab39c;" id="second-semester-stat-active">0</div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" style="color: #0ab39c;" id="second-semester-stat-days">0</div>
                        <div class="stat-label">Days</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end col -->
</div>
<!-- end row -->

<!-- Edit Semester Modal -->
<div class="modal fade zoomIn" id="editSemesterModal" tabindex="-1" aria-labelledby="editSemesterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header p-3 bg-primary-subtle">
                <h5 class="modal-title" id="editSemesterModalLabel">
                    <i class="ri-pencil-line align-middle me-2"></i>
                    Edit Semester
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="tablelist-form" autocomplete="off" id="editSemesterForm" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" id="semesterId" name="semester_id" />
                    <div class="row g-3">
                        <div class="col-lg-12">
                            <label class="form-label">
                                Semester
                            </label>
                            <input 
                                type="text" 
                                id="semester_name" 
                                class="form-control bg-light" 
                                placeholder="1st Semester"
                                readonly
                            />
                            <small class="text-muted">Semester name is fixed and cannot be changed.</small>
                        </div>
                        <!--end col-->

                        <div class="col-lg-12">
                            <label for="school_year" class="form-label">
                                School Year <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="school_year" 
                                class="form-control" 
                                name="school_year"
                                placeholder="e.g., 2024-2025"
                            />
                            <div class="invalid-feedback" id="school_year-error"></div>
                        </div>
                        <!--end col-->

                        <div class="col-lg-6">
                            <label for="start_date" class="form-label">
                                Start Date <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="start_date" 
                                class="form-control" 
                                name="start_date"
                            />
                            <div class="invalid-feedback" id="start_date-error"></div>
                        </div>
                        <!--end col-->

                        <div class="col-lg-6">
                            <label for="end_date" class="form-label">
                                End Date <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="end_date" 
                                class="form-control" 
                                name="end_date"
                            />
                            <div class="invalid-feedback" id="end_date-error"></div>
                        </div>
                        <!--end col-->

                        <div class="col-lg-6">
                            <label class="form-label">
                                Quarters
                            </label>
                            @component('components.select-vanilla', [
                                'name' => 'quarter_ids',
                                'id' => 'edit-quarter-select',
                                'label' => null,
                                'placeholder' => 'Select quarters',
                                'searchable' => false,
                                'multiple' => true,
                                'options' => [],
                            ])
                            @endcomponent
                            <div class="invalid-feedback d-block" id="quarters-error" style="display:none;"></div>
                        </div>
                        <!--end col-->

                        <div class="col-lg-6 d-flex align-items-center">
                            <div>
                                <div class="form-check form-switch mb-1">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        id="is_active" 
                                        name="is_active"
                                        value="1"
                                    />
                                    <label class="form-check-label" for="is_active">
                                        Active Semester
                                    </label>
                                </div>
                                <small class="text-muted d-block">Toggle to activate or deactivate this semester.</small>
                                <small class="text-muted">Quarter selection is static and used for display/organization.</small>
                            </div>
                        </div>
                        <!--end col-->
                    </div>
                    <!--end row-->
                </div>
                <div class="modal-footer">
                    <div class="hstack gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" id="close-modal-btn" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="update-btn">
                            <i class="ri-save-line align-middle me-1"></i>Update Semester
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end modal-->

<!-- Edit Quarter Modal -->
<div class="modal fade zoomIn" id="editQuarterModal" tabindex="-1" aria-labelledby="editQuarterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header p-3 bg-info-subtle">
                <h5 class="modal-title" id="editQuarterModalLabel">
                    <i class="ri-pencil-line align-middle me-2"></i>
                    Edit Quarter
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editQuarterForm" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" id="quarterId" name="quarter_id" />
                    <div class="mb-3">
                        <label class="form-label" for="quarter_name">Quarter</label>
                        <input 
                            type="text" 
                            id="quarter_name" 
                            class="form-control bg-light" 
                            readonly
                        />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="quarter_is_active">Status</label>
                        <div class="form-check form-switch">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="quarter_is_active" 
                                name="is_active"
                                value="1"
                            />
                            <label class="form-check-label" for="quarter_is_active">
                                Active
                            </label>
                        </div>
                        <small class="text-muted">Toggle to activate or deactivate this quarter.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" id="quarter-close-btn">Close</button>
                    <button type="button" class="btn btn-primary" id="quarter-update-btn">
                        <i class="ri-save-line align-middle me-1"></i>Update Quarter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end quarter modal-->

@endsection
@section('script')

<script src="{{URL::asset('build/js/pages/academic-timeline.js')}}"></script>

<!-- App js -->
<script src="{{URL::asset('build/js/app.js')}}"></script>


@endsection
