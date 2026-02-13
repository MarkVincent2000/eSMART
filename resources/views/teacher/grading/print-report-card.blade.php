<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - {{ $grade->name ?? 'Student' }}</title>
    <style>
        @page {
            size: legal portrait;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }

        thead {
            display: table-header-group;
        }

        tbody {
            display: table-row-group;
        }

        .info-table {
            border: none;
            margin-bottom: 5px;
        }

        .info-table td {
            border: none;
            padding: 2px 15px 2px 0;
            vertical-align: top;
            line-height: 1.4;
        }

        .info-table .col-3 {
            width: 35%;
        }

        .info-table .col-2 {
            width: 30%;
        }

        .signature-table {
            border: none;
            margin-top: 5px;
        }

        .signature-table td {
            border: none;
            padding: 8px 8px 0 0;
            vertical-align: bottom;
            width: 50%;
            text-align: center;
        }

        .signature-name {
            min-height: 18px;
            margin-bottom: 0;
            font-weight: bold;
            text-align: center;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            width: 100%;
            margin-bottom: 4px;
        }

        .signature-label {
            font-size: 9px;
            color: #333;
            text-align: center;
        }

        .eligibility-table {
            border: none;
        }

        .eligibility-table td {
            border: none;
            padding: 2px 12px 2px 0;
            vertical-align: top;
        }

        .underline {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            text-align: center;
        }

        .semester-section {
            margin-bottom: 14px;
        }

        .grading-scale table {
            border: none;
        }

        .grading-scale th,
        .grading-scale td {
            border: none;
            padding: 2px 8px;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        .mb-2 {
            margin-bottom: 4px;
        }

        .mb-3 {
            margin-bottom: 6px;
        }

        .mb-4 {
            margin-bottom: 8px;
        }

        .mt-3 {
            margin-top: 6px;
        }

        .container {
            border: 1px solid #000;
            padding: 5px;
            width: 70%;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="text-center fw-bold mb-3">K to 12 Basic Education Program</h2>


        {{-- Student info: 3-2-3 columns --}}
        <table class="info-table mb-3">
            <tr>
                <td class="col-3">
                    <strong>Name:</strong> {{ $grade->name ?? '—' }}<br>
                    <strong>Sex:</strong> {{ $grade->sex ?? '—' }}<br>
                    <strong>Grade Level:</strong> {{ $grade->grade ?? '—' }}
                </td>
                <td class="col-2">
                    <strong>School Year:</strong> {{ $grade->school_year ?? '—' }}
                    <br>
                    <br>
                    <strong>Section:</strong> {{ $grade->section ?? '—' }}
                </td>
                <td class="col-3">
                    <strong>Age:</strong> {{ $grade->age ?? '—' }}<br>
                    <strong>LRN:</strong> {{ $grade->lrn ?? '—' }}<br>

                    <strong>Date of Birth:</strong>
                    {{ $grade->date_of_birth ? $grade->date_of_birth->format('m/d/Y') : '—' }}
                </td>
            </tr>
        </table>

        <div class="mb-2">
            <p class="mb-1 fw-bold">Dear Parent:</p>
            <p class="mb-0">This report card shows the ability and progress your child has made in the different
                learning areas as well as his/her core values. The school welcomes you, should you desire to know more
                about your child's progress.</p>
        </div>

        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-name">&nbsp;</div>
                    <div class="signature-line"></div>
                    <div class="signature-label">Principal</div>
                </td>
                <td>
                    <div class="signature-name">
                        {{ $grade->teacher_name ?? ($grade->teacher && $grade->teacher->user ? $grade->teacher->user->name : '') }}
                    </div>
                    <div class="signature-line"></div>
                    <div class="signature-label">Class Adviser</div>
                </td>
            </tr>
        </table>

        <hr style="border: 1px solid #000; margin: 3px 0;">
        <hr style="border: 1px solid #000; margin: 3px 0;">

        <h3 class="text-center text-uppercase fw-bold mb-3 mt-3">Report on Learning Progress and Achievement</h3>

        @if($grade->grade >= 7 && $grade->grade <= 10)
            {{-- Grade 7-10: Single table with quarters --}}
            <div class="semester-section">
                <table>
                    <thead>
                        <tr>
                            <th>Subjects</th>
                            <th class="text-center">Quarter 1</th>
                            <th class="text-center">Quarter 2</th>
                            <th class="text-center">Quarter 3</th>
                            <th class="text-center">Quarter 4</th>
                            <th class="text-center">Final Grade</th>
                            <th class="text-center">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grade->subjectGrades as $sg)
                            @php $gt = is_array($sg->grade_type) ? $sg->grade_type : (array) $sg->grade_type; @endphp
                            <tr>
                                <td>{{ $sg->subject_name ?? ($sg->subject->name ?? 'N/A') }}</td>
                                <td class="text-center">{{ $gt['quarter_1'] ?? '—' }}</td>
                                <td class="text-center">{{ $gt['quarter_2'] ?? '—' }}</td>
                                <td class="text-center">{{ $gt['quarter_3'] ?? '—' }}</td>
                                <td class="text-center">{{ $gt['quarter_4'] ?? '—' }}</td>
                                <td class="text-center">{{ $gt['final_grade'] ?? '—' }}</td>
                                <td class="text-center">{{ $gt['remarks'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                        <tr class="fw-bold">
                            <td colspan="5" class="text-center">General Average</td>
                            <td class="text-center">
                                @if($grade->general_average && is_array($grade->general_average) && isset($grade->general_average['overall']))
                                    {{ number_format($grade->general_average['overall'], 0) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-center">
                                @if($grade->general_average_remark && is_array($grade->general_average_remark) && isset($grade->general_average_remark['overall']))
                                    {{ $grade->general_average_remark['overall'] }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            {{-- Grade 11-12: Tables per semester --}}
            @php
                $gradesBySemester = [];
                foreach ($grade->subjectGrades as $sg) {
                    $sid = $sg->semester_id ?? 'none';
                    $sname = $sg->semester->name ?? 'Semester';
                    if (!isset($gradesBySemester[$sid])) {
                        $gradesBySemester[$sid] = ['name' => $sname, 'grades' => []];
                    }
                    $gradesBySemester[$sid]['grades'][] = $sg;
                }
                ksort($gradesBySemester);
            @endphp
            @foreach($gradesBySemester as $semesterId => $semesterGroup)
                <div class="semester-section">
                    <table>
                        <thead>
                            <tr>
                                <th rowspan="2">Subjects</th>
                                <th colspan="2" class="text-center">{{ $semesterGroup['name'] }}</th>
                                <th rowspan="2" class="text-center">Final Grade</th>
                                <th rowspan="2" class="text-center">Remarks</th>
                            </tr>
                            <tr>
                                <th class="text-center">Midterm</th>
                                <th class="text-center">Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($semesterGroup['grades'] as $sg)
                                @php
                                    $gt = is_array($sg->grade_type) ? $sg->grade_type : (array) $sg->grade_type;
                                @endphp
                                <tr>
                                    <td>{{ $sg->subject_name ?? ($sg->subject->name ?? 'N/A') }}</td>
                                    <td class="text-center">{{ $gt['midterm'] ?? '—' }}</td>
                                    <td class="text-center">{{ $gt['final_term'] ?? '—' }}</td>
                                    <td class="text-center">{{ $gt['final_grade'] ?? '—' }}</td>
                                    <td class="text-center">{{ $gt['remarks'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold">
                                <td colspan="3" class="text-center">General Average ({{ $semesterGroup['name'] }})</td>
                                <td class="text-center">
                                    @if($grade->general_average && is_array($grade->general_average) && isset($grade->general_average[(string) $semesterId]))
                                        {{ number_format($grade->general_average[(string) $semesterId], 0) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($grade->general_average_remark && is_array($grade->general_average_remark) && isset($grade->general_average_remark[(string) $semesterId]))
                                        {{ $grade->general_average_remark[(string) $semesterId] }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        @endif

        <div class="grading-scale mb-4">
            <p class="fw-bold mb-2">Grading Scale</p>
            <table>
                <thead>
                    <tr>
                        <th>Descriptor</th>
                        <th class="text-center">Grade Range</th>
                        <th class="text-center">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Outstanding</td>
                        <td class="text-center">90-100</td>
                        <td class="text-center">Passed</td>
                    </tr>
                    <tr>
                        <td>Very Satisfactory</td>
                        <td class="text-center">85-89</td>
                        <td class="text-center">Passed</td>
                    </tr>
                    <tr>
                        <td>Satisfactory</td>
                        <td class="text-center">80-84</td>
                        <td class="text-center">Passed</td>
                    </tr>
                    <tr>
                        <td>Fairly Satisfactory</td>
                        <td class="text-center">75-79</td>
                        <td class="text-center">Passed</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <table class="eligibility-table">
            <tr>
                <td style="width: 280px;">Eligible for transfer/admission to the next grade level:</td>
                <td><span class="underline">{{ $grade->eligible_to_advance_grade ? 'Yes' : 'No' }}</span></td>
            </tr>
            <tr>
                <td>Has advanced unit(s) in:</td>
                <td><span class="underline">{{ $grade->has_advance_unit_in ? 'Yes' : 'No' }}</span></td>
            </tr>
            <tr>
                <td>Has lacking unit(s) in:</td>
                <td><span class="underline">{{ $grade->has_lacking_unit_in ? 'Yes' : 'No' }}</span></td>
            </tr>
        </table>
    </div>
</body>

</html>