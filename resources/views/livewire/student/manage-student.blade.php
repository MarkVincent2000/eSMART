<div>
    <x-toast-notification />
    @include('livewire.student.modals.create-section')
    @include('livewire.student.modals.delete-section')
    @include('livewire.student.modals.create-program')
    @include('livewire.student.modals.delete-program')
    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex mb-3">
                        <div class="flex-grow-1">
                            <h5 class="fs-16">Filters</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="#" class="text-decoration-underline" id="clearall">Clear All</a>
                        </div>
                    </div>


                </div>

                <div class="accordion accordion-flush filter-accordion">

                    <div class="card-body border-bottom">
                        <div>
                            <div class="d-flex justify-content-between mb-2">
                                <p class="text-muted text-uppercase fs-12 fw-medium mb-2">Semester</p>
                                <x-button color="primary" size="sm" icon="ri-settings-3-line"
                                    tooltip="Configure Semesters" icon-position="left" :iconOnly="true"
                                    wire:click="addSemester" wireTarget="addSemester"></x-button>
                            </div>

                            <div class="search-box search-box-sm d-flex flex-column gap-2">
                                <div class="position-relative">
                                    <input type="text" class="form-control bg-light border-0"
                                        placeholder="Search Semesters..." style="padding-right: 35px;"
                                        wire:model.live.debounce.300ms="semesterSearch">
                                    <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y"
                                        style="right: 10px; pointer-events: none; z-index: 1;"></i>
                                </div>
                            </div>

                            <div class="d-flex flex-column gap-2 mt-3 filter-check">
                                @forelse($semesters as $semester)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="{{ $semester->id }}"
                                            id="semester_{{ $semester->id }}">
                                        <label class="form-check-label d-flex align-items-center gap-2"
                                            for="semester_{{ $semester->id }}">
                                            <span>{{ $semester->name }} <span
                                                    class="text-muted">({{ $semester->school_year }})</span></span>
                                            @if($semester->is_active)
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <div class="text-muted text-center py-2">
                                        <small>No semesters found</small>
                                    </div>
                                @endforelse
                                @if($totalSemesters > $semesterLimit)
                                    <div class="text-center mt-2">
                                        <button type="button"
                                            class="btn btn-link text-decoration-none text-uppercase fw-medium p-0"
                                            wire:click="loadMoreSemesters" wire:target="loadMoreSemesters">
                                            <span wire:loading.remove wire:target="loadMoreSemesters">
                                                Load More ({{ $totalSemesters - $semesterLimit }} remaining)
                                            </span>
                                            <span wire:loading wire:target="loadMoreSemesters">
                                                Loading...
                                            </span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingBrands">
                            <button class="accordion-button bg-transparent shadow-none" type="button"
                                data-bs-toggle="collapse" data-bs-target="#flush-collapseBrands" aria-expanded="true"
                                aria-controls="flush-collapseBrands">
                                <span class="text-muted text-uppercase fs-12 fw-medium">Sections</span> <span
                                    class="badge bg-success rounded-pill align-middle ms-1 filter-badge"></span>
                            </button>
                        </h2>

                        <div id="flush-collapseBrands" class="accordion-collapse collapse show"
                            aria-labelledby="flush-headingBrands">
                            <div class="accordion-body text-body pt-0">
                                <div class="search-box search-box-sm d-flex flex-column gap-2">
                                    <div class="position-relative">
                                        <input type="text" class="form-control bg-light border-0"
                                            placeholder="Search Sections..." style="padding-right: 35px;"
                                            wire:model.live.debounce.300ms="sectionSearch">
                                        <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y"
                                            style="right: 10px; pointer-events: none; z-index: 1;"></i>
                                    </div>
                                    <div>
                                        <x-button color="primary" size="sm" icon="ri-add-line" icon-position="left"
                                            wire:click="addSection" wireTarget="addSection">Add Section</x-button>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2 mt-3 filter-check">
                                    @forelse($sections as $section)
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="form-check flex-grow-1">
                                                <input class="form-check-input" type="checkbox" value="{{ $section->id }}"
                                                    id="section_{{ $section->id }}">
                                                <label class="form-check-label d-flex align-items-center gap-2"
                                                    for="section_{{ $section->id }}">
                                                    <span>{{ $section->name }} ({{ $section->year_level->label() }})</span>
                                                    @if($section->active)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </label>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <x-button color="info" icon="ri-edit-line" icon-position="left" size="sm"
                                                    :iconOnly="true" tooltip="Edit Section" tooltip-placement="top"
                                                    wire:click="editSection({{ $section->id }})"
                                                    wireTarget="editSection({{ $section->id }})">
                                                </x-button>
                                                <x-button color="danger" icon="ri-delete-bin-line" icon-position="left"
                                                    size="sm" :iconOnly="true" tooltip="Delete Section"
                                                    tooltip-placement="top" wire:click="deleteSection({{ $section->id }})"
                                                    wireTarget="deleteSection({{ $section->id }})">
                                                </x-button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted text-center py-2">
                                            <small>No sections found</small>
                                        </div>
                                    @endforelse
                                    @if($totalSections > $sectionLimit)
                                        <div class="text-center mt-2">
                                            <button type="button"
                                                class="btn btn-link text-decoration-none text-uppercase fw-medium p-0"
                                                wire:click="loadMoreSections" wire:target="loadMoreSections">
                                                <span wire:loading.remove wire:target="loadMoreSections">
                                                    Load More ({{ $totalSections - $sectionLimit }} remaining)
                                                </span>
                                                <span wire:loading wire:target="loadMoreSections">
                                                    Loading...
                                                </span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end accordion-item -->

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="flush-headingDiscount">
                            <button class="accordion-button bg-transparent shadow-none" type="button"
                                data-bs-toggle="collapse" data-bs-target="#flush-collapseDiscount" aria-expanded="true"
                                aria-controls="flush-collapseDiscount">
                                <span class="text-muted text-uppercase fs-12 fw-medium">Programs</span> <span
                                    class="badge bg-success rounded-pill align-middle ms-1 filter-badge"></span>
                            </button>
                        </h2>
                        <div id="flush-collapseDiscount" class="accordion-collapse collapse show"
                            aria-labelledby="flush-headingDiscount">
                            <div class="accordion-body text-body pt-0">
                                <div class="search-box search-box-sm d-flex flex-column gap-2">
                                    <div class="position-relative">
                                        <input type="text" class="form-control bg-light border-0"
                                            placeholder="Search Programs..." style="padding-right: 35px;"
                                            wire:model.live.debounce.300ms="programSearch">
                                        <i class="ri-search-line search-icon position-absolute top-50 translate-middle-y"
                                            style="right: 10px; pointer-events: none; z-index: 1;"></i>
                                    </div>
                                    <div>
                                        <x-button color="primary" size="sm" icon="ri-add-line" icon-position="left"
                                            wire:click="addProgram" wireTarget="addProgram">Add Program</x-button>
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-2 mt-3 filter-check">
                                    @forelse($programs as $program)
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="form-check flex-grow-1">
                                                <input class="form-check-input" type="checkbox" value="{{ $program->id }}"
                                                    id="program_{{ $program->id }}">
                                                <label class="form-check-label d-flex align-items-center gap-2"
                                                    for="program_{{ $program->id }}">
                                                    <span>{{ $program->code }}</span>
                                                    @if($program->active)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                                    @endif
                                                </label>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <x-button color="info" icon="ri-edit-line" icon-position="left" size="sm"
                                                    :iconOnly="true" tooltip="Edit Program" tooltip-placement="top"
                                                    wire:click="editProgram({{ $program->id }})"
                                                    wireTarget="editProgram({{ $program->id }})">
                                                </x-button>
                                                <x-button color="danger" icon="ri-delete-bin-line" icon-position="left"
                                                    size="sm" :iconOnly="true" tooltip="Delete Program"
                                                    tooltip-placement="top" wire:click="deleteProgram({{ $program->id }})"
                                                    wireTarget="deleteProgram({{ $program->id }})">
                                                </x-button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted text-center py-2">
                                            <small>No programs found</small>
                                        </div>
                                    @endforelse
                                    @if($totalPrograms > $programLimit)
                                        <div class="text-center mt-2">
                                            <button type="button"
                                                class="btn btn-link text-decoration-none text-uppercase fw-medium p-0"
                                                wire:click="loadMorePrograms" wire:target="loadMorePrograms">
                                                <span wire:loading.remove wire:target="loadMorePrograms">
                                                    Load More ({{ $totalPrograms - $programLimit }} remaining)
                                                </span>
                                                <span wire:loading wire:target="loadMorePrograms">
                                                    Loading...
                                                </span>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end accordion-item -->

                </div>
            </div>
            <!-- end card -->
        </div>
        <!-- end col -->

        <div class="col-xl-9 col-lg-8">
            <div>
                <div class="card">
                    <div class="card-header border-0">
                        <div class="row g-4">
                            <div class="col-sm-auto">
                                <div>
                                    <a href="apps-ecommerce-add-product" class="btn btn-success" id="addproduct-btn"><i
                                            class="ri-add-line align-bottom me-1"></i> Add Product</a>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <input type="text" class="form-control" id="searchProductList"
                                            placeholder="Search Products...">
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
                                        <a class="nav-link active fw-semibold" data-bs-toggle="tab"
                                            href="#productnav-all" role="tab">
                                            All <span
                                                class="badge bg-danger-subtle text-danger align-middle rounded-pill ms-1">12</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link fw-semibold" data-bs-toggle="tab"
                                            href="#productnav-published" role="tab">
                                            Published <span
                                                class="badge bg-danger-subtle text-danger align-middle rounded-pill ms-1">5</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link fw-semibold" data-bs-toggle="tab" href="#productnav-draft"
                                            role="tab">
                                            Draft
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-auto">
                                <div id="selection-element">
                                    <div class="my-n1 d-flex align-items-center text-muted">
                                        Select <div id="select-content" class="text-body fw-semibold px-1"></div> Result
                                        <button type="button"
                                            class="btn btn-link link-danger p-0 ms-3 material-shadow-none"
                                            data-bs-toggle="modal" data-bs-target="#removeItemModal">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end card header -->
                    <div class="card-body">

                        <div class="tab-content text-muted">
                            <div class="tab-pane active" id="productnav-all" role="tabpanel">
                                <div id="table-product-list-all" class="table-card gridjs-border-none"></div>
                            </div>
                            <!-- end tab pane -->

                            <div class="tab-pane" id="productnav-published" role="tabpanel">
                                <div id="table-product-list-published" class="table-card gridjs-border-none"></div>
                            </div>
                            <!-- end tab pane -->

                            <div class="tab-pane" id="productnav-draft" role="tabpanel">
                                <div class="py-4 text-center">
                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                        colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
                                    </lord-icon>
                                    <h5 class="mt-4">Sorry! No Result Found</h5>
                                </div>
                            </div>
                            <!-- end tab pane -->
                        </div>
                        <!-- end tab content -->

                    </div>
                    <!-- end card body -->
                </div>
                <!-- end card -->
            </div>
        </div>
        <!-- end col -->
    </div>
</div>