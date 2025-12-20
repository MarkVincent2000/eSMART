<x-modal id="print-students-modal" wire:model="showPrintModal" title="Print Students" size="xl" :centered="true"
    :show-footer="true">

    <style>
        .nav-pills .nav-link.completed {
            background-color: #28a745;
            color: #fff;
        }

        .nav-pills .nav-link.completed::after {
            content: ' ✓';
        }
    </style>

    <!-- Wizard Steps Indicator -->
    <div class="mb-4">
        <ul class="nav nav-pills nav-justified" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $printStep == 1 ? 'active' : ($printStep > 1 ? 'completed' : '') }}"
                    type="button" disabled>
                    <i class="ri-filter-line me-1"></i>
                    <span class="d-none d-sm-inline">Step 1:</span> Filters
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $printStep == 2 ? 'active' : '' }}" type="button" disabled>
                    <i class="ri-file-pdf-line me-1"></i>
                    <span class="d-none d-sm-inline">Step 2:</span> Preview
                </button>
            </li>
        </ul>
    </div>

    <!-- Step 1: Filter Selection -->
    @if($printStep == 1)
        <div class="mb-4">
            <h5 class="mb-3">Selected Filters</h5>
            <div class="card">
                <div class="card-body">
                    @if(empty($this->printFilterSummary))
                        <p class="text-muted mb-0">No filters selected. All students will be included.</p>
                    @else
                        <div class="row g-3">
                            @foreach($this->printFilterSummary as $label => $value)
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <strong class="text-muted me-2" style="min-width: 100px;">{{ $label }}:</strong>
                                        <span>{{ $value }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <h6 class="mb-2">Filter Details:</h6>
                <ul class="list-unstyled">
                    <li><strong>Status:</strong> {{ $studentStatus === 'all' ? 'All' : ucfirst($studentStatus) }}</li>
                    <li><strong>School Years:</strong>
                        {{ empty($selectedSchoolYears) ? 'All' : count($selectedSchoolYears) . ' selected' }}</li>
                    <li><strong>Sections:</strong>
                        {{ empty($selectedSections) ? 'All' : count($selectedSections) . ' selected' }}</li>
                    <li><strong>Programs:</strong>
                        {{ empty($selectedPrograms) ? 'All' : count($selectedPrograms) . ' selected' }}</li>
                    @if($studentSearch)
                        <li><strong>Search:</strong> {{ $studentSearch }}</li>
                    @endif
                </ul>
            </div>
        </div>
    @endif

    <!-- Step 2: PDF Preview -->
    @if($printStep == 2)
        <div class="mb-4">
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                <strong>Preview:</strong> The PDF preview is shown below. You can print or download it using the browser's
                print function.
            </div>

            @if($printUrl)
                <div class="border rounded" style="height: 600px; overflow: hidden;">
                    <iframe src="{{ $printUrl }}" style="width: 100%; height: 100%; border: none;"
                        title="Student List PDF Preview">
                    </iframe>
                </div>
            @else
                <div class="text-center py-5">
                    <p class="text-muted">Generating PDF preview...</p>
                </div>
            @endif
        </div>
    @endif

    <x-slot:footer>
        @if($printStep == 1)
            <button type="button" class="btn btn-light" wire:click="closePrintModal">Cancel</button>
            <x-button color="primary" wire:click="nextPrintStep" wireTarget="nextPrintStep">
                Next: Preview
                <i class="ri-arrow-right-line ms-1"></i>
            </x-button>
        @elseif($printStep == 2)
            <button type="button" class="btn btn-light" wire:click="previousPrintStep">Back</button>
            <button type="button" class="btn btn-light" wire:click="closePrintModal">Close</button>
            @if($printUrl)
                <a href="{{ $printUrl }}" target="_blank" class="btn btn-primary">
                    <i class="ri-download-line me-1"></i>
                    Download PDF
                </a>
            @endif
        @endif
    </x-slot:footer>
</x-modal>