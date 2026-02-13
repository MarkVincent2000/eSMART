<div>
    <x-toast-notification />

    {{-- Add / Edit Grade Modal --}}
    <x-modal id="add-grade-modal" wire:model="showAddGradeModal" :title="$editingGradeId ? 'Edit Grade' : 'Add Grade'"
        size="xl" :centered="true" :show-footer="true" overflow="hidden" :contentScrollable="false">
        <form wire:submit.prevent="saveGrade">
            <div class="row g-3" style="overflow: visible;">
                {{-- Grade Level Selection (filter for students) --}}
                <div class="col-12">
                    <label for="selectedGradeLevel" class="form-label fw-semibold mb-2">Grade Level</label>
                    <select id="selectedGradeLevel"
                        class="form-select @error('selectedGradeLevel') is-invalid @enderror"
                        wire:model.live="selectedGradeLevel">
                        <option value="">Select grade level...</option>
                        @foreach($this->gradeLevelOptions as $gradeOption)
                            <option value="{{ $gradeOption['value'] }}">{{ $gradeOption['label'] }}</option>
                        @endforeach
                    </select>
                    @error('selectedGradeLevel')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Select a grade to filter the student list below.</small>
                </div>

                {{-- Student Selection (filtered by grade) --}}
                <div class="col-12" wire:key="student-options-{{ $selectedGradeLevel ?? 'all' }}">
                    <x-select label="Student" wire:model.live="studentInfoId" :options="$this->studentOptions"
                        placeholder="Select student..." :searchable="true" />
                    @if($selectedGradeLevel)
                        @php
                            $gradeLabel = collect($this->gradeLevelOptions)->firstWhere('value', (int) $selectedGradeLevel)['label'] ?? 'selected grade';
                        @endphp
                        <small class="text-muted">Showing students in {{ $gradeLabel }}.</small>
                    @else
                        <small class="text-muted">Select a grade level above to filter students.</small>
                    @endif
                    @error('studentInfoId')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Student Information Section --}}
                <div class="col-12">
                    <hr>
                    <h6 class="mb-3">Student Information</h6>
                </div>

                <div class="col-md-6">
                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" wire:model="name"
                        class="form-control @error('name') is-invalid @enderror" placeholder="Student name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="schoolYear" class="form-label">School Year <span class="text-danger">*</span></label>
                    <input type="text" id="schoolYear" wire:model="schoolYear"
                        class="form-control @error('schoolYear') is-invalid @enderror" placeholder="e.g., 2025-2026">
                    @error('schoolYear')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="lrn" class="form-label">LRN</label>
                    <input type="text" id="lrn" wire:model="lrn" class="form-control @error('lrn') is-invalid @enderror"
                        placeholder="Learner Reference Number">
                    @error('lrn')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="grade" class="form-label">Grade Level</label>
                    <input type="number" id="grade" wire:model="grade" min="1" max="12"
                        class="form-control @error('grade') is-invalid @enderror" placeholder="Grade level">
                    @error('grade')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="section" class="form-label">Section</label>
                    <input type="text" id="section" wire:model="section"
                        class="form-control @error('section') is-invalid @enderror" placeholder="Section name">
                    @error('section')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" id="age" wire:model="age" min="1" max="150"
                        class="form-control @error('age') is-invalid @enderror" placeholder="Age">
                    @error('age')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="sex" class="form-label">Sex</label>
                    <input type="text" id="sex" wire:model="sex" class="form-control @error('sex') is-invalid @enderror"
                        placeholder="Sex">
                    @error('sex')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label for="dateOfBirth" class="form-label">Date of Birth</label>
                    <input type="date" id="dateOfBirth" wire:model="dateOfBirth"
                        class="form-control @error('dateOfBirth') is-invalid @enderror">
                    @error('dateOfBirth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Teacher Information Section --}}
                <div class="col-12">
                    <hr>
                    <h6 class="mb-3">Teacher Information</h6>
                </div>

                <div class="col-md-6">
                    <label for="teacherName" class="form-label">Teacher Name</label>
                    <input type="text" id="teacherName" wire:model="teacherName"
                        class="form-control @error('teacherName') is-invalid @enderror" placeholder="Teacher name"
                        readonly>
                    @error('teacherName')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="dateIssued" class="form-label">Date Issued</label>
                    <input type="date" id="dateIssued" wire:model="dateIssued"
                        class="form-control @error('dateIssued') is-invalid @enderror">
                    @error('dateIssued')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Subject Grades Table --}}
                @if($teacherId && !empty($teacherWorkloads))
                    <div class="col-12">
                        <hr>
                        <h6 class="mb-3">Subject</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    @if($grade >= 7 && $grade <= 10)
                                        {{-- Grade 7-10: Quarters --}}
                                        <tr>
                                            <th>Subject</th>
                                            <th>Quarter 1</th>
                                            <th>Quarter 2</th>
                                            <th>Quarter 3</th>
                                            <th>Quarter 4</th>
                                            <th>Final Grade</th>
                                            <th>Remarks</th>
                                        </tr>
                                    @elseif($grade >= 11 && $grade <= 12)
                                        {{-- Grade 11-12: 1st Semester only in first table --}}
                                        <tr>
                                            <th rowspan="2" class="text-center align-middle">Subject</th>
                                            @foreach($this->firstSemesterOnly as $semesterGroup)
                                                <th colspan="2" class="text-center">{{ $semesterGroup['semester_name'] }}</th>
                                            @endforeach
                                            <th rowspan="2" class="text-center align-middle">Final Grade</th>
                                            <th rowspan="2" class="text-center align-middle">Remarks</th>
                                        </tr>
                                        <tr>
                                            @foreach($this->firstSemesterOnly as $semesterGroup)
                                                <th class="text-center align-middle">Midterm</th>
                                                <th class="text-center align-middle">Final Term</th>
                                            @endforeach
                                        </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @if($grade >= 7 && $grade <= 10)
                                        {{-- Grade 7-10: Quarter inputs --}}
                                        @foreach($teacherWorkloads as $index => $workload)
                                            @php
                                                $subjectKey = 'subject_' . $workload['subject_id'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $workload['subject_code'] ? $workload['subject_code'] . ' - ' : '' }}{{ $workload['subject_name'] }}</strong>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        wire:model.live="subjectGrades.{{ $subjectKey }}.quarter_1"
                                                        class="form-control form-control-sm" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        wire:model.live="subjectGrades.{{ $subjectKey }}.quarter_2"
                                                        class="form-control form-control-sm" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        wire:model.live="subjectGrades.{{ $subjectKey }}.quarter_3"
                                                        class="form-control form-control-sm" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        wire:model.live="subjectGrades.{{ $subjectKey }}.quarter_4"
                                                        class="form-control form-control-sm" placeholder="0.00">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        wire:model.live="subjectGrades.{{ $subjectKey }}.final_grade"
                                                        class="form-control form-control-sm" placeholder="0.00">
                                                </td>
                                                <td>
                                                    @php
                                                        $sg = $this->subjectGrades[$subjectKey] ?? [];
                                                        $fg = $sg['final_grade'] ?? null;
                                                        $subjectRemark = (isset($fg) && $fg !== '' && is_numeric($fg)) ? ((float) $fg >= 75 ? 'Passed' : 'Failed') : null;
                                                    @endphp
                                                    @if($subjectRemark)
                                                        <span
                                                            class="badge {{ $subjectRemark === 'Passed' ? 'bg-success' : 'bg-danger' }}">{{ $subjectRemark }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @elseif($grade >= 11 && $grade <= 12)
                                        {{-- Grade 11-12: Only subjects that belong to 1st semester --}}
                                        @foreach($this->firstSemesterOnly as $firstSemesterGroup)
                                            @foreach($firstSemesterGroup['workloads'] as $workload)
                                                @php
                                                    $semesterSubjectKey = 'subject_' . $workload['subject_id'] . '_sem_' . $firstSemesterGroup['semester_id'];
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $workload['subject_code'] ? $workload['subject_code'] . ' - ' : '' }}{{ $workload['subject_name'] }}</strong>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="100"
                                                            wire:model.live="subjectGrades.{{ $semesterSubjectKey }}.midterm"
                                                            class="form-control form-control-sm" placeholder="0.00">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="100"
                                                            wire:model.live="subjectGrades.{{ $semesterSubjectKey }}.final_term"
                                                            class="form-control form-control-sm" placeholder="0.00">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="100"
                                                            wire:model.live="subjectGrades.{{ $semesterSubjectKey }}.final_grade"
                                                            class="form-control form-control-sm" placeholder="0.00">
                                                    </td>
                                                    <td>
                                                        @php
                                                            $sg = $this->subjectGrades[$semesterSubjectKey] ?? [];
                                                            $fg = $sg['final_grade'] ?? null;
                                                            $subjectRemark = (isset($fg) && $fg !== '' && is_numeric($fg)) ? ((float) $fg >= 75 ? 'Passed' : 'Failed') : null;
                                                        @endphp
                                                        @if($subjectRemark)
                                                            <span
                                                                class="badge {{ $subjectRemark === 'Passed' ? 'bg-success' : 'bg-danger' }}">{{ $subjectRemark }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    @endif
                                    {{-- General Average row inside table: Overall (Grade 7-10) or 1st Semester (Grade
                                    11-12) --}}
                                    @if($teacherId && !empty($teacherWorkloads))
                                        @php
                                            $firstSem = $this->firstSemesterOnly;
                                            $gaKey = ($grade >= 11 && $grade <= 12 && !empty($firstSem)) ? (string) ($firstSem[0]['semester_id'] ?? '1') : 'overall';
                                            $gaComputed = $this->modalGeneralAverage;
                                            $gaAvg = $gaComputed !== null && is_array($gaComputed) ? ($gaComputed[$gaKey] ?? null) : null;
                                            $gaInput = $this->generalAverageInputs[$gaKey] ?? null;
                                            $gaValue = ($gaInput !== null && $gaInput !== '' && is_numeric($gaInput)) ? (float) $gaInput : $gaAvg;
                                            $gaRemark = $gaValue !== null ? ($gaValue >= 75 ? 'Passed' : 'Failed') : null;
                                            $gaLabel = ($grade >= 11 && $grade <= 12) ? '1st Semester' : 'Overall';
                                        @endphp
                                        <tr class="table align-middle" x-data x-init="$wire.syncGeneralAverageInputs()">
                                            <td class="fw-semibold">General Average</td>
                                            @if($grade >= 7 && $grade <= 10)
                                                <td colspan="4" class="text-center text-muted">—</td>
                                                <td class="p-2" style="max-width: 100px;">
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        class="form-control form-control-sm"
                                                        wire:model.live="generalAverageInputs.{{ $gaKey }}"
                                                        placeholder="{{ $gaAvg !== null ? number_format($gaAvg, 2) : '—' }}">
                                                </td>
                                                <td class="align-middle">
                                                    @if($gaRemark)
                                                        <span
                                                            class="badge {{ $gaRemark === 'Passed' ? 'bg-success' : 'bg-danger' }}">{{ $gaRemark }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            @else
                                                <td colspan="2" class="text-center text-muted">—</td>
                                                <td class="p-2" style="max-width: 100px;">
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        class="form-control form-control-sm"
                                                        wire:model.live="generalAverageInputs.{{ $gaKey }}"
                                                        placeholder="{{ $gaAvg !== null ? number_format($gaAvg, 2) : '—' }}">
                                                </td>
                                                <td class="align-middle">
                                                    @if($gaRemark)
                                                        <span
                                                            class="badge {{ $gaRemark === 'Passed' ? 'bg-success' : 'bg-danger' }}">{{ $gaRemark }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            @endif
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 2nd Semester Table (Grade 11-12) - Separate table below --}}
                    @if($grade >= 11 && $grade <= 12 && $this->secondSemesterOnly)
                        @php
                            $semesterGroup = $this->secondSemesterOnly;
                        @endphp
                        <div class="col-12 mt-4">
                            <hr>
                            <h6 class="mb-3">Subject</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th rowspan="2" class="text-center align-middle">Subject</th>
                                            <th colspan="2" class="text-center">{{ $semesterGroup['semester_name'] }}</th>
                                            <th rowspan="2" class="text-center align-middle">Final Grade</th>
                                            <th rowspan="2" class="text-center align-middle">Remarks</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center align-middle">Midterm</th>
                                            <th class="text-center align-middle">Final Term</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $semesterSubjects = [];
                                            foreach ($semesterGroup['workloads'] as $workload) {
                                                $subjectId = $workload['subject_id'];
                                                if (!isset($semesterSubjects[$subjectId])) {
                                                    $semesterSubjects[$subjectId] = [
                                                        'subject_id' => $subjectId,
                                                        'subject_name' => $workload['subject_name'],
                                                        'subject_code' => $workload['subject_code'],
                                                    ];
                                                }
                                            }
                                        @endphp
                                        @foreach($semesterSubjects as $subject)
                                            @php
                                                $workloadForSemester = collect($semesterGroup['workloads'])->firstWhere('subject_id', $subject['subject_id']);
                                                $semesterSubjectKey = $workloadForSemester ? 'subject_' . $workloadForSemester['subject_id'] . '_sem_' . $semesterGroup['semester_id'] : null;
                                                $baseSubjectKey = $semesterSubjectKey ?? 'subject_' . $subject['subject_id'];
                                            @endphp
                                            <tr>
                                                <td>
                                                    <strong>{{ $subject['subject_code'] ? $subject['subject_code'] . ' - ' : '' }}{{ $subject['subject_name'] }}</strong>
                                                </td>
                                                @if($workloadForSemester && $semesterSubjectKey)
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="100"
                                                            wire:model.live="subjectGrades.{{ $semesterSubjectKey }}.midterm"
                                                            class="form-control form-control-sm" placeholder="0.00">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="100"
                                                            wire:model.live="subjectGrades.{{ $semesterSubjectKey }}.final_term"
                                                            class="form-control form-control-sm" placeholder="0.00">
                                                    </td>
                                                @else
                                                    <td colspan="2" class="text-center text-muted">-</td>
                                                @endif
                                                <td>
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        wire:model.live="subjectGrades.{{ $baseSubjectKey }}.final_grade"
                                                        class="form-control form-control-sm" placeholder="0.00">
                                                </td>
                                                <td>
                                                    @php
                                                        $sg = $this->subjectGrades[$baseSubjectKey] ?? [];
                                                        $fg = $sg['final_grade'] ?? null;
                                                        $subjectRemark = (isset($fg) && $fg !== '' && is_numeric($fg)) ? ((float) $fg >= 75 ? 'Passed' : 'Failed') : null;
                                                    @endphp
                                                    @if($subjectRemark)
                                                        <span
                                                            class="badge {{ $subjectRemark === 'Passed' ? 'bg-success' : 'bg-danger' }}">{{ $subjectRemark }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        {{-- General Average row inside 2nd Semester table --}}
                                        @if($teacherId && !empty($teacherWorkloads) && $semesterGroup)
                                            @php
                                                $gaKey2 = (string) $semesterGroup['semester_id'];
                                                $gaComputed2 = $this->modalGeneralAverage;
                                                $gaAvg2 = $gaComputed2 !== null && is_array($gaComputed2) ? ($gaComputed2[$gaKey2] ?? null) : null;
                                                $gaInput2 = $this->generalAverageInputs[$gaKey2] ?? null;
                                                $gaValue2 = ($gaInput2 !== null && $gaInput2 !== '' && is_numeric($gaInput2)) ? (float) $gaInput2 : $gaAvg2;
                                                $gaRemark2 = $gaValue2 !== null ? ($gaValue2 >= 75 ? 'Passed' : 'Failed') : null;
                                            @endphp
                                            <tr class="table align-middle">
                                                <td class="fw-semibold">General Average</td>
                                                <td colspan="2" class="text-center text-muted">—</td>
                                                <td class="p-2" style="max-width: 100px;">
                                                    <input type="number" step="0.01" min="0" max="100"
                                                        class="form-control form-control-sm"
                                                        wire:model.live="generalAverageInputs.{{ $gaKey2 }}"
                                                        placeholder="{{ $gaAvg2 !== null ? number_format($gaAvg2, 2) : '—' }}">
                                                </td>
                                                <td class="align-middle">
                                                    @if($gaRemark2)
                                                        <span
                                                            class="badge {{ $gaRemark2 === 'Passed' ? 'bg-success' : 'bg-danger' }}">{{ $gaRemark2 }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Additional Tables for Remaining Semesters (Grade 11-12) --}}
                    @if($grade >= 11 && $grade <= 12 && !empty($this->remainingSemesters))
                        @foreach($this->remainingSemesters as $semesterIndex => $semesterGroup)
                            <div class="col-12 mt-4">
                                <hr>
                                <h6 class="mb-3">Subject</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th rowspan="2" class="text-center align-middle">Subject</th>
                                                <th colspan="2" class="text-center">{{ $semesterGroup['semester_name'] }}</th>
                                                <th rowspan="2" class="text-center align-middle">Final Grade</th>
                                                <th rowspan="2" class="text-center align-middle">Remarks</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center align-middle">Midterm</th>
                                                <th class="text-center align-middle">Final Term</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                // Get unique subjects for this semester
                                                $semesterSubjects = [];
                                                foreach ($semesterGroup['workloads'] as $workload) {
                                                    $subjectId = $workload['subject_id'];
                                                    if (!isset($semesterSubjects[$subjectId])) {
                                                        $semesterSubjects[$subjectId] = [
                                                            'subject_id' => $subjectId,
                                                            'subject_name' => $workload['subject_name'],
                                                            'subject_code' => $workload['subject_code'],
                                                        ];
                                                    }
                                                }
                                            @endphp
                                            @foreach($semesterSubjects as $subject)
                                                @php
                                                    $workloadForSemester = collect($semesterGroup['workloads'])->firstWhere('subject_id', $subject['subject_id']);
                                                    $semesterSubjectKey = $workloadForSemester ? 'subject_' . $workloadForSemester['subject_id'] . '_sem_' . $semesterGroup['semester_id'] : null;
                                                    $baseSubjectKey = $semesterSubjectKey ?? 'subject_' . $subject['subject_id'];
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $subject['subject_code'] ? $subject['subject_code'] . ' - ' : '' }}{{ $subject['subject_name'] }}</strong>
                                                    </td>
                                                    @if($workloadForSemester && $semesterSubjectKey)
                                                        <td>
                                                            <input type="number" step="0.01" min="0" max="100"
                                                                wire:model.live="subjectGrades.{{ $semesterSubjectKey }}.midterm"
                                                                class="form-control form-control-sm" placeholder="0.00">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.01" min="0" max="100"
                                                                wire:model.live="subjectGrades.{{ $semesterSubjectKey }}.final_term"
                                                                class="form-control form-control-sm" placeholder="0.00">
                                                        </td>
                                                    @else
                                                        <td colspan="2" class="text-center text-muted">-</td>
                                                    @endif
                                                    <td>
                                                        <input type="number" step="0.01" min="0" max="100"
                                                            wire:model.live="subjectGrades.{{ $baseSubjectKey }}.final_grade"
                                                            class="form-control form-control-sm" placeholder="0.00">
                                                    </td>
                                                    <td>
                                                        @php
                                                            $sg = $this->subjectGrades[$baseSubjectKey] ?? [];
                                                            $fg = $sg['final_grade'] ?? null;
                                                            $subjectRemark = (isset($fg) && $fg !== '' && is_numeric($fg)) ? ((float) $fg >= 75 ? 'Passed' : 'Failed') : null;
                                                        @endphp
                                                        @if($subjectRemark)
                                                            <span
                                                                class="badge {{ $subjectRemark === 'Passed' ? 'bg-success' : 'bg-danger' }}">{{ $subjectRemark }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            {{-- General Average row inside this semester table (3rd, 4th, etc.) --}}
                                            @if($teacherId && !empty($teacherWorkloads))
                                                @php
                                                    $gaKeyRem = (string) $semesterGroup['semester_id'];
                                                    $gaComputedRem = $this->modalGeneralAverage;
                                                    $gaAvgRem = $gaComputedRem !== null && is_array($gaComputedRem) ? ($gaComputedRem[$gaKeyRem] ?? null) : null;
                                                    $gaInputRem = $this->generalAverageInputs[$gaKeyRem] ?? null;
                                                    $gaValueRem = ($gaInputRem !== null && $gaInputRem !== '' && is_numeric($gaInputRem)) ? (float) $gaInputRem : $gaAvgRem;
                                                    $gaRemarkRem = $gaValueRem !== null ? ($gaValueRem >= 75 ? 'Passed' : 'Failed') : null;
                                                @endphp
                                                <tr class="table align-middle">
                                                    <td class="fw-semibold">General Average</td>
                                                    <td colspan="2" class="text-center text-muted">—</td>
                                                    <td class="p-2" style="max-width: 100px;">
                                                        <input type="number" step="0.01" min="0" max="100"
                                                            class="form-control form-control-sm"
                                                            wire:model.live="generalAverageInputs.{{ $gaKeyRem }}"
                                                            placeholder="{{ $gaAvgRem !== null ? number_format($gaAvgRem, 2) : '—' }}">
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($gaRemarkRem)
                                                            <span
                                                                class="badge {{ $gaRemarkRem === 'Passed' ? 'bg-success' : 'bg-danger' }}">{{ $gaRemarkRem }}</span>
                                                        @else
                                                            <span class="text-muted">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endif

                {{-- Eligibility Section --}}
                <div class="col-12">
                    <hr>
                    <h6 class="mb-3">Eligibility Status</h6>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="eligibleToAdvanceGrade"
                            wire:model="eligibleToAdvanceGrade">
                        <label class="form-check-label" for="eligibleToAdvanceGrade">
                            Eligible to Advance Grade
                        </label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="hasAdvanceUnitIn"
                            wire:model="hasAdvanceUnitIn">
                        <label class="form-check-label" for="hasAdvanceUnitIn">
                            Has Advance Unit In
                        </label>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="hasLackingUnitIn"
                            wire:model="hasLackingUnitIn">
                        <label class="form-check-label" for="hasLackingUnitIn">
                            Has Lacking Unit In
                        </label>
                    </div>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeAddGradeModal">Cancel</button>
            <x-button color="primary" wire:click="saveGrade" wireTarget="saveGrade">
                <span wire:loading.remove wire:target="saveGrade">Save Grade</span>
                <span wire:loading wire:target="saveGrade">Saving...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- View Grade Details Modal --}}
    <x-modal id="view-grade-details-modal" wire:model="showViewDetailsModal" title="Grade Details" size="xl"
        :centered="true" :show-footer="true" overflow="visible">
        @if($viewingGrade)
            <div class="p-2">
                {{-- Header Section --}}
                <div class="d-flex align-items-center gap-3 mb-4 pb-4 border-bottom">
                    <div class="avatar-md flex-shrink-0">
                        @if($viewingGrade->general_average && is_array($viewingGrade->general_average) && !empty($viewingGrade->general_average))
                            @php
                                // Compute overall average from JSON data
                                $averageValues = array_values($viewingGrade->general_average);
                                $overallAvg = !empty($averageValues) ? array_sum($averageValues) / count($averageValues) : null;
                            @endphp
                            @if($overallAvg !== null)
                                <div class="avatar-title bg-primary rounded-circle fs-24 shadow text-white"
                                    style="width: 60px; height: 60px;">
                                    {{ number_format($overallAvg, 1) }}
                                </div>
                            @else
                                <div class="avatar-title bg-light text-primary rounded-circle fs-24 shadow-sm"
                                    style="width: 60px; height: 60px;">
                                    <i class="ri-user-line"></i>
                                </div>
                            @endif
                        @else
                            <div class="avatar-title bg-light text-primary rounded-circle fs-24 shadow-sm"
                                style="width: 60px; height: 60px;">
                                <i class="ri-user-line"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-1 fw-bold text-primary">{{ $viewingGrade->name }}</h4>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="text-muted"><i
                                    class="ri-fingerprint-line me-1"></i>{{ $viewingGrade->lrn ?? 'No LRN' }}</span>
                            <span class="text-muted">|</span>
                            <span class="text-muted"><i
                                    class="ri-mail-line me-1"></i>{{ $viewingGrade->studentInfo->user->email ?? 'No Email' }}</span>
                            @if($viewingGrade->general_average_remark && is_array($viewingGrade->general_average_remark))
                                @foreach($viewingGrade->general_average_remark as $key => $remark)
                                    @php
                                        $label = $key === 'overall' ? 'Overall' : ($key === '1' ? '1st Semester' : ($key === '2' ? '2nd Semester' : "Semester $key"));
                                    @endphp
                                    <span class="badge {{ $remark === 'Passed' ? 'bg-success' : 'bg-danger' }} rounded-pill ms-2">
                                        {{ $label }}: {{ $remark }}
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="flex-shrink-0 d-none d-md-block">
                        <div class="text-end">
                            <p class="text-muted mb-0 small text-uppercase fw-bold">Issued Date</p>
                            <h6 class="mb-0 fw-semibold">
                                {{ $viewingGrade->date_issued ? $viewingGrade->date_issued->format('M d, Y') : 'N/A' }}
                            </h6>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    {{-- Left Column: Info --}}
                    <div class="col-lg-4">
                        <div class="card border shadow-none mb-4">
                            <div class="card-header bg-light-subtle py-2">
                                <h6 class="card-title mb-0 fs-13 text-uppercase fw-bold">
                                    <i class="ri-information-line align-bottom me-1"></i> Academic Info
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush border-dashed mb-0">
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                        <div class="text-muted small"><i class="ri-calendar-event-line me-1"></i> School
                                            Year</div>
                                        <div class="fw-semibold text-end">{{ $viewingGrade->school_year ?? 'N/A' }}</div>
                                    </div>
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                        <div class="text-muted small"><i class="ri-graduation-cap-line me-1"></i> Grade
                                            Level</div>
                                        <div class="fw-semibold text-end">
                                            {{ $viewingGrade->grade ? 'Grade ' . $viewingGrade->grade : 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                        <div class="text-muted small"><i class="ri-team-line me-1"></i> Section</div>
                                        <div class="fw-semibold text-end">{{ $viewingGrade->section ?? 'N/A' }}</div>
                                    </div>
                                    <div
                                        class="list-group-item px-0 d-flex justify-content-between align-items-start border-bottom-0">
                                        <div class="text-muted small"><i class="ri-user-follow-line me-1"></i> Adviser</div>
                                        <div class="fw-semibold text-end small">
                                            {{ $viewingGrade->teacher_name ?? ($viewingGrade->teacher && $viewingGrade->teacher->user ? $viewingGrade->teacher->user->name : 'N/A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border shadow-none mb-4">
                            <div class="card-header bg-light-subtle py-2">
                                <h6 class="card-title mb-0 fs-13 text-uppercase fw-bold">
                                    <i class="ri-user-settings-line align-bottom me-1"></i> Personal Details
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush border-dashed mb-0">
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                        <div class="text-muted small"><i class="ri-calendar-line me-1"></i> Age</div>
                                        <div class="fw-semibold text-end">{{ $viewingGrade->age ?? 'N/A' }}</div>
                                    </div>
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                        <div class="text-muted small"><i class="ri-genderless-line me-1"></i> Sex</div>
                                        <div class="fw-semibold text-end">{{ $viewingGrade->sex ?? 'N/A' }}</div>
                                    </div>
                                    <div
                                        class="list-group-item px-0 d-flex justify-content-between align-items-start border-bottom-0">
                                        <div class="text-muted small"><i class="ri-cake-2-line me-1"></i> Birth Date</div>
                                        <div class="fw-semibold text-end">
                                            {{ $viewingGrade->date_of_birth ? $viewingGrade->date_of_birth->format('M d, Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border shadow-none">
                            <div class="card-header bg-light-subtle py-2">
                                <h6 class="card-title mb-0 fs-13 text-uppercase fw-bold">
                                    <i class="ri-checkbox-circle-line align-bottom me-1"></i> Eligibility
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    @if($viewingGrade->eligible_to_advance_grade)
                                        <div
                                            class="p-2 bg-success-subtle text-success rounded border border-success-subtle d-flex align-items-center">
                                            <i class="ri-checkbox-circle-fill me-2 fs-18"></i>
                                            <span class="small fw-semibold">Eligible to Advance</span>
                                        </div>
                                    @endif
                                    @if($viewingGrade->has_advance_unit_in)
                                        <div
                                            class="p-2 bg-info-subtle text-info rounded border border-info-subtle d-flex align-items-center">
                                            <i class="ri-arrow-up-circle-fill me-2 fs-18"></i>
                                            <span class="small fw-semibold">Has Advance Unit</span>
                                        </div>
                                    @endif
                                    @if($viewingGrade->has_lacking_unit_in)
                                        <div
                                            class="p-2 bg-warning-subtle text-warning rounded border border-warning-subtle d-flex align-items-center">
                                            <i class="ri-error-warning-fill me-2 fs-18"></i>
                                            <span class="small fw-semibold">Has Lacking Unit</span>
                                        </div>
                                    @endif
                                    @if(!$viewingGrade->eligible_to_advance_grade && !$viewingGrade->has_advance_unit_in && !$viewingGrade->has_lacking_unit_in)
                                        <div class="text-center py-2 text-muted small italic">No special status</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Grades --}}
                    <div class="col-lg-8">
                        @if($viewingGrade->subjectGrades && $viewingGrade->subjectGrades->count() > 0)
                            @if($viewingGrade->grade >= 7 && $viewingGrade->grade <= 10)
                                <div class="card border shadow-none mb-0">
                                    <div class="card-header bg-light-subtle py-2 d-flex justify-content-between align-items-center">
                                        <h6 class="card-title mb-0 fs-13 text-uppercase fw-bold">
                                            <i class="ri-book-open-line align-bottom me-1"></i> Subject Grades
                                        </h6>
                                        <span
                                            class="badge bg-primary-subtle text-primary">{{ $viewingGrade->subjectGrades->count() }}
                                            Subjects</span>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover table-nowrap align-middle mb-0">
                                                <thead class="table-light text-muted">
                                                    <tr>
                                                        <th scope="col">Subject</th>
                                                        <th scope="col" class="text-center">Q1</th>
                                                        <th scope="col" class="text-center">Q2</th>
                                                        <th scope="col" class="text-center">Q3</th>
                                                        <th scope="col" class="text-center">Q4</th>
                                                        <th scope="col" class="text-center bg-light-subtle">Final</th>
                                                        <th scope="col" class="text-center">Remark</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($viewingGrade->subjectGrades as $subjectGrade)
                                                        @php
                                                            $gradeType = is_array($subjectGrade->grade_type) ? $subjectGrade->grade_type : (array) $subjectGrade->grade_type;
                                                        @endphp
                                                        <tr>
                                                            <td class="fw-medium">
                                                                {{ $subjectGrade->subject_name ?? ($subjectGrade->subject->name ?? 'N/A') }}
                                                            </td>
                                                            <td class="text-center">{{ $gradeType['quarter_1'] ?? '—' }}</td>
                                                            <td class="text-center">{{ $gradeType['quarter_2'] ?? '—' }}</td>
                                                            <td class="text-center">{{ $gradeType['quarter_3'] ?? '—' }}</td>
                                                            <td class="text-center">{{ $gradeType['quarter_4'] ?? '—' }}</td>
                                                            <td class="text-center fw-bold text-primary bg-light-subtle">
                                                                {{ $gradeType['final_grade'] ?? '—' }}
                                                            </td>
                                                            <td class="text-center">
                                                                @if(isset($gradeType['remarks']) && $gradeType['remarks'])
                                                                    <span
                                                                        class="badge {{ $gradeType['remarks'] === 'Passed' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border {{ $gradeType['remarks'] === 'Passed' ? 'border-success-subtle' : 'border-danger-subtle' }}">
                                                                        {{ $gradeType['remarks'] }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted small">—</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @elseif($viewingGrade->grade >= 11 && $viewingGrade->grade <= 12)
                                @php
                                    $gradesBySemester = [];
                                    foreach ($viewingGrade->subjectGrades as $subjectGrade) {
                                        $semesterId = $subjectGrade->semester_id ?? 'no_semester';
                                        $semesterName = $subjectGrade->semester->name ?? 'No Semester';
                                        if (!isset($gradesBySemester[$semesterId])) {
                                            $gradesBySemester[$semesterId] = ['semester_id' => $semesterId, 'semester_name' => $semesterName, 'grades' => []];
                                        }
                                        $gradesBySemester[$semesterId]['grades'][] = $subjectGrade;
                                    }
                                    ksort($gradesBySemester);
                                @endphp

                                @foreach($gradesBySemester as $semesterGroup)
                                    <div class="card border shadow-none mb-4 last-child-mb-0">
                                        <div class="card-header bg-light-subtle py-2 d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0 fs-13 text-uppercase fw-bold">
                                                <i class="ri-calendar-line align-bottom me-1"></i> {{ $semesterGroup['semester_name'] }}
                                            </h6>
                                            <span class="badge bg-primary-subtle text-primary">{{ count($semesterGroup['grades']) }}
                                                Subjects</span>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover table-nowrap align-middle mb-0">
                                                    <thead class="table-light text-muted">
                                                        <tr>
                                                            <th scope="col">Subject</th>
                                                            <th scope="col" class="text-center">Midterm</th>
                                                            <th scope="col" class="text-center">Final Term</th>
                                                            <th scope="col" class="text-center bg-light-subtle">Final</th>
                                                            <th scope="col" class="text-center">Remark</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($semesterGroup['grades'] as $subjectGrade)
                                                            @php
                                                                $gradeType = is_array($subjectGrade->grade_type) ? $subjectGrade->grade_type : (array) $subjectGrade->grade_type;
                                                            @endphp
                                                            <tr>
                                                                <td class="fw-medium">
                                                                    {{ $subjectGrade->subject_name ?? ($subjectGrade->subject->name ?? 'N/A') }}
                                                                </td>
                                                                <td class="text-center">{{ $gradeType['midterm'] ?? '—' }}</td>
                                                                <td class="text-center">{{ $gradeType['final_term'] ?? '—' }}</td>
                                                                <td class="text-center fw-bold text-primary bg-light-subtle">
                                                                    {{ $gradeType['final_grade'] ?? '—' }}
                                                                </td>
                                                                <td class="text-center">
                                                                    @if(isset($gradeType['remarks']) && $gradeType['remarks'])
                                                                        <span
                                                                            class="badge {{ $gradeType['remarks'] === 'Passed' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} border {{ $gradeType['remarks'] === 'Passed' ? 'border-success-subtle' : 'border-danger-subtle' }}">
                                                                            {{ $gradeType['remarks'] }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted small">—</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        @else
                            <div class="text-center py-5 border rounded bg-light-subtle">
                                <i class="ri-book-open-line fs-1 text-muted opacity-50 mb-2"></i>
                                <h5 class="text-muted">No subject grades found</h5>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                    colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
                </lord-icon>
                <h5 class="mt-3">Loading grade details...</h5>
            </div>
        @endif

        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeViewDetailsModal">Close</button>
            @if($viewingGrade)
                <button type="button" class="btn btn-soft-secondary" wire:click="openPrintModal({{ $viewingGrade->id }})">
                    <i class="ri-printer-line me-1"></i>Print
                </button>

                @php
                    $viewUser = auth()->user();
                    $viewTeacherId = $viewUser?->teacher?->id;
                    $isOwner = $viewTeacherId !== null && (int) $viewingGrade->teacher_id === (int) $viewTeacherId;
                    $isSuperAdmin = $viewUser && $viewUser->hasRole('super-admin');
                    $canManage = $isSuperAdmin || $isOwner;
                @endphp

                @if($canManage)
                    <x-button color="primary" wire:click="editGrade({{ $viewingGrade->id }})"
                        wireTarget="editGrade({{ $viewingGrade->id }})">
                        <i class="ri-pencil-line me-1"></i>Edit Record
                    </x-button>
                @endif
            @endif
        </x-slot:footer>
    </x-modal>

    {{-- Print PDF Modal (iframe) --}}
    <x-modal id="print-grade-modal" wire:model="showPrintModal" title="Report Card (PDF)" size="xl" :centered="true"
        :show-footer="true" overflow="hidden">
        @if($printGradeId)
            <div class="position-relative bg-light rounded" style="min-height: 70vh;">
                <iframe id="print-grade-iframe" src="{{ route('teacher.grading.pdf', $printGradeId) }}"
                    title="Report Card PDF" class="border-0 w-100 rounded"
                    style="height: 70vh; min-height: 500px;"></iframe>
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="ri-file-pdf-line fs-1"></i>
                <p class="mb-0 mt-2">Loading PDF...</p>
            </div>
        @endif
        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closePrintModal">Close</button>
            @if($printGradeId)
                <a href="{{ route('teacher.grading.pdf', $printGradeId) }}" target="_blank" class="btn btn-soft-secondary">
                    <i class="ri-download-line me-1"></i>Download PDF
                </a>
                <button type="button" class="btn btn-primary"
                    onclick="document.getElementById('print-grade-iframe')?.contentWindow?.print();">
                    <i class="ri-printer-line me-1"></i>Print
                </button>
            @endif
        </x-slot:footer>
    </x-modal>

    {{-- Delete Grade Confirmation Modal --}}
    <x-modal id="delete-grade-modal" wire:model="showDeleteGradeModal" title="Delete Grade Record" size="md"
        :centered="true" :show-footer="true">
        <div class="text-center">
            <div class="mb-4">
                <i class="ri-delete-bin-line text-danger" style="font-size: 4rem;"></i>
            </div>
            <h5 class="mb-3">Are you sure?</h5>
            <p class="text-muted">
                You are about to delete the grade record for
                <strong>{{ $deleteGradeLabel ?? 'this student' }}</strong>.
                This action cannot be undone.
            </p>
            <div class="alert alert-warning mt-3">
                <i class="ri-alert-line me-2"></i>
                <strong>Warning:</strong> This will remove the grade record and all subject grades from the system.
            </div>
        </div>
        <x-slot:footer>
            <button type="button" class="btn btn-light" wire:click="closeDeleteGradeModal">Cancel</button>
            <x-button color="danger" wire:click="confirmDeleteGrade" wireTarget="confirmDeleteGrade">
                <span wire:loading.remove wire:target="confirmDeleteGrade">Delete Grade</span>
                <span wire:loading wire:target="confirmDeleteGrade">Deleting...</span>
            </x-button>
        </x-slot:footer>
    </x-modal>

    <div class="row g-4 mb-3">
        <div class="col-sm-auto">
            <div>
                @if($canAddGrade)
                    <div class="col-sm-auto">
                        <x-button color="success" icon="ri-add-line" wire:click="openAddGradeModal"
                            wireTarget="openAddGradeModal">
                            Add Grade
                        </x-button>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-sm">
            <div class="d-flex justify-content-sm-end gap-2">

                <div class="search-box ms-2">
                    <input type="text" class="form-control" placeholder="Search..."
                        wire:model.live.debounce.300ms="search">
                    <i class="ri-search-line search-icon"></i>
                </div>
            </div>
        </div>
    </div>

    @php
        $authUser = auth()->user();
        $currentTeacherIdForCards = $authUser ? \App\Models\Teacher\Teacher::where('user_id', $authUser->id)->value('id') : null;
        $isSuperAdminForCards = $authUser && $authUser->hasRole('super-admin');
    @endphp
    <div class="row">
        @forelse($studentInfoGrades as $grade)
            <div class="col-xxl-3 col-sm-6">
                <div class="card card-height-100 shadow-sm border-0 overflow-hidden">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column h-100">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($grade->general_average && is_array($grade->general_average) && !empty($grade->general_average))
                                            @php
                                                // Compute overall average from JSON data
                                                $averageValues = array_values($grade->general_average);
                                                $overallAvg = !empty($averageValues) ? array_sum($averageValues) / count($averageValues) : null;
                                            @endphp
                                            @if($overallAvg !== null)
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 45px; height: 45px;">
                                                    <span
                                                        class="text-white fw-bold fs-14">{{ number_format($overallAvg, 1) }}</span>
                                                </div>
                                            @else
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 45px; height: 45px;">
                                                    <i class="ri-user-line text-muted fs-18"></i>
                                                </div>
                                            @endif
                                        @else
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 45px; height: 45px;">
                                                <i class="ri-user-line text-muted fs-18"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h5 class="mb-0 fs-15 fw-semibold text-truncate" style="max-width: 150px;">
                                                {{ $grade->name }}
                                            </h5>
                                            @if($grade->studentInfo && $grade->studentInfo->user)
                                                <p class="text-muted mb-0 small text-truncate" style="max-width: 150px;">
                                                    {{ $grade->studentInfo->user->email }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="dropdown">
                                        <button class="btn btn-soft-secondary btn-sm btn-icon material-shadow-none"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="ri-more-2-fill"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#"
                                                wire:click.prevent="viewGradeDetails({{ $grade->id }})"><i
                                                    class="ri-eye-fill align-bottom me-2 text-muted"></i> View Details</a>

                                            @php
                                                $isOwner = $currentTeacherIdForCards !== null && (int) $grade->teacher_id === (int) $currentTeacherIdForCards;
                                                $canManage = $isSuperAdminForCards || $isOwner;
                                            @endphp

                                            @if($canManage)
                                                <a class="dropdown-item" href="#"
                                                    wire:click.prevent="editGrade({{ $grade->id }})"><i
                                                        class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit Record</a>
                                            @endif

                                            <a class="dropdown-item" href="#"
                                                wire:click.prevent="openPrintModal({{ $grade->id }})"><i
                                                    class="ri-printer-line align-bottom me-2 text-muted"></i> Print</a>

                                            @if($canManage)
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#"
                                                    wire:click.prevent="deleteGrade({{ $grade->id }})"><i
                                                        class="ri-delete-bin-line align-bottom me-2"></i> Delete</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs flex-shrink-0">
                                            <div class="avatar-title bg-light text-primary rounded-circle">
                                                <i class="ri-calendar-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-muted mb-0 small">School Year</p>
                                            <h6 class="mb-0 text-truncate">{{ $grade->school_year ?? 'N/A' }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs flex-shrink-0">
                                            <div class="avatar-title bg-light text-primary rounded-circle">
                                                <i class="ri-graduation-cap-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-muted mb-0 small">Grade Level</p>
                                            <h6 class="mb-0 text-truncate">
                                                {{ $grade->grade ? 'Grade ' . $grade->grade : 'N/A' }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs flex-shrink-0">
                                            <div class="avatar-title bg-light text-primary rounded-circle">
                                                <i class="ri-team-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-muted mb-0 small">Section</p>
                                            <h6 class="mb-0 text-truncate">{{ $grade->section ?? 'N/A' }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs flex-shrink-0">
                                            <div class="avatar-title bg-light text-primary rounded-circle">
                                                <i class="ri-fingerprint-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-muted mb-0 small">LRN</p>
                                            <h6 class="mb-0 text-truncate">{{ $grade->lrn ?? 'N/A' }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($grade->teacher_name || ($grade->teacher && $grade->teacher->user))
                                <div class="bg-light rounded p-2 mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ri-user-follow-line text-muted"></i>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <p class="text-muted mb-0 small">Adviser</p>
                                            <h6 class="mb-0 text-truncate small fw-semibold">
                                                {{ $grade->teacher_name ?? ($grade->teacher && $grade->teacher->user ? $grade->teacher->user->name : 'N/A') }}
                                            </h6>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-auto">
                                <div class="d-flex flex-wrap gap-2 mb-0">
                                    @if($grade->general_average_remark && is_array($grade->general_average_remark))
                                        @foreach($grade->general_average_remark as $key => $remark)
                                            @php
                                                $label = $key === 'overall' ? 'Overall' : ($key === '1' ? '1st Sem' : ($key === '2' ? '2nd Sem' : "Sem $key"));
                                            @endphp
                                            <span
                                                class="badge {{ $remark === 'Passed' ? 'bg-success' : 'bg-danger' }} rounded-pill">
                                                {{ $label }}: {{ $remark }}
                                            </span>
                                        @endforeach
                                    @endif
                                    @if($grade->eligible_to_advance_grade)
                                        <span class="badge bg-success-subtle text-success rounded-pill">Eligible</span>
                                    @endif
                                    @if($grade->has_advance_unit_in)
                                        <span class="badge bg-info-subtle text-info rounded-pill">Advance Unit</span>
                                    @endif
                                    @if($grade->has_lacking_unit_in)
                                        <span class="badge bg-warning-subtle text-warning rounded-pill">Lacking Unit</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top-dashed py-3 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted small">
                                <i class="ri-calendar-check-line me-1 align-bottom"></i>
                                @if($grade->date_issued)
                                    {{ $grade->date_issued->format('M d, Y') }}
                                @else
                                    No date
                                @endif
                            </div>
                            @if($grade->subjectGrades && $grade->subjectGrades->count() > 0)
                                <div class="flex-shrink-0">
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $grade->subjectGrades->count() }}
                                        Subject{{ $grade->subjectGrades->count() > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                        colors="primary:#405189,secondary:#0ab39c" style="width:72px;height:72px">
                    </lord-icon>
                    <h5 class="mt-3">No grade records found</h5>
                    <p class="text-muted mb-0">
                        @if(!empty($search))
                            No grade records match your search criteria.
                        @else
                            There are no grade records available at this time.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>



    @if($studentInfoGrades->hasPages())
        <x-pagination :paginator="$studentInfoGrades" />
    @endif


</div>