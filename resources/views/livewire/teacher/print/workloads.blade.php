<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Workloads - {{ now()->format('Y-m-d') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            background: #fff;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #1a1a1a;
        }

        .header .subtitle {
            font-size: 13px;
            color: #666;
            margin-top: 3px;
        }

        .info-section {
            margin-bottom: 15px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .info-table td {
            padding: 4px 0;
            border: none;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            width: 140px;
            padding-right: 8px;
        }

        .info-value {
            color: #333;
        }

        .table-container {
            margin-top: 15px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 10px;
        }

        thead {
            background: #343a40;
            color: #fff;
        }

        th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #495057;
        }

        td {
            padding: 7px 6px;
            border: 1px solid #dee2e6;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tbody tr:hover {
            background-color: #e9ecef;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #6c757d;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        @media print {
            body {
                padding: 15px;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>Teacher Workloads</h1>
        <div class="subtitle">Generated on {{ now()->format('M d, Y h:i A') }}</div>
    </div>

    <!-- Filter Information -->
    @if(!empty($filters))
        <div class="info-section">
            <h3 style="font-size: 13px; margin-bottom: 6px; color: #495057;">Applied Filters:</h3>
            <table class="info-table">
                @foreach($filters as $label => $value)
                    <tr>
                        <td class="info-label">{{ $label }}:</td>
                        <td class="info-value">{{ $value }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <!-- Summary -->
    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-label">Total Workloads:</td>
                <td class="info-value"><strong>{{ $workloads->count() }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Workloads Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 18%;">Teacher</th>
                    <th style="width: 20%;">Subject</th>
                    <th style="width: 14%;">Section / Grade</th>
                    <th style="width: 12%;">Semester</th>
                    <th style="width: 10%;">School Year</th>
                    <th style="width: 10%;">Room</th>
                    <th style="width: 6%;">Units</th>
                    <th style="width: 16%;">Schedule</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workloads as $index => $workload)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            @if($workload->teacher && $workload->teacher->user)
                                <strong>{{ $workload->teacher->user->name }}</strong><br>
                                <span class="text-muted">{{ $workload->teacher->user->email }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($workload->subject)
                                <strong>{{ $workload->subject->code ?? '' }}</strong>
                                @if($workload->subject->code && $workload->subject->name)
                                    - 
                                @endif
                                {{ $workload->subject->name ?? '' }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($workload->section)
                                {{ $workload->section->name }}
                                @if($workload->section->year_level)
                                    @php
                                        $gradeLevel = $workload->section->year_level instanceof \App\Enums\YearLevel
                                            ? $workload->section->year_level->label()
                                            : 'Grade ' . $workload->section->year_level;
                                    @endphp
                                    <br><span class="text-muted">{{ $gradeLevel }}</span>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($workload->semester)
                                {{ $workload->semester->name ?? 'Semester' }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($workload->semester && $workload->semester->school_year)
                                {{ $workload->semester->school_year }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($workload->classroom)
                                {{ $workload->classroom->name ?? 'Classroom' }}
                                @if($workload->classroom->class_code)
                                    <br><span class="text-muted">{{ $workload->classroom->class_code }}</span>
                                @endif
                            @else
                                @php
                                    $schedule = is_array($workload->schedule) ? $workload->schedule : [];
                                @endphp
                                @if(!empty($schedule['room'] ?? null))
                                    Room {{ $schedule['room'] }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $workload->load_units !== null ? number_format($workload->load_units, 2) : '0.00' }}
                        </td>
                        <td>
                            @php
                                $schedule = is_array($workload->schedule) ? $workload->schedule : [];
                            @endphp
                            @if(!empty($schedule['text'] ?? null))
                                {{ $schedule['text'] }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 18px; color: #999;">
                            No workloads found matching the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This document was generated automatically by the eSMART System.</p>
        <p>Page 1 of 1</p>
    </div>
</body>

</html>

