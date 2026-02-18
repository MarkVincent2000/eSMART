<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report - {{ $attendance->title ?? 'Untitled' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            background: #fff;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .header .subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .info-table td {
            padding: 5px 0;
            border: none;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            width: 110px;
            padding-right: 10px;
        }

        .info-value {
            color: #333;
        }


        .table-container {
            margin-top: 25px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        thead {
            background: #343a40;
            color: #fff;
        }

        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #495057;
        }

        td {
            padding: 8px;
            border: 1px solid #dee2e6;
        }

        tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        tbody tr:hover {
            background: #e9ecef;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-present {
            background: #28a745;
            color: #fff;
        }

        .status-absent {
            background: #dc3545;
            color: #fff;
        }

        .status-late {
            background: #ffc107;
            color: #333;
        }

        .status-excused {
            background: #17a2b8;
            color: #fff;
        }

        .status-partial {
            background: #6c757d;
            color: #fff;
        }

        .status-leave {
            background: #007bff;
            color: #fff;
        }

        .status-pending {
            background: #6c757d;
            color: #fff;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
        }

        @media print {
            body {
                padding: 10px;
            }

            .header {
                page-break-after: avoid;
            }

            table {
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Attendance Report</h1>
        <div class="subtitle">{{ $attendance->title ?? 'Untitled Attendance Session' }}</div>
        <div class="subtitle">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tbody>
                <tr>
                    <td class="info-label">Date:</td>
                    <td class="info-value">
                        @php
                            try {
                                echo $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('F j, Y') : 'N/A';
                            } catch (\Exception $e) {
                                echo $attendance->date ?? 'N/A';
                            }
                        @endphp
                    </td>


                    @if($attendance->start_time || $attendance->end_time)

                        <td class="info-label">Time:</td>
                        <td class="info-value">
                            @php
                                try {
                                    if ($attendance->start_time && $attendance->end_time) {
                                        $startTime = is_string($attendance->start_time)
                                            ? \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->start_time)
                                            : $attendance->start_time;
                                        $endTime = is_string($attendance->end_time)
                                            ? \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->end_time)
                                            : $attendance->end_time;
                                        echo $startTime->format('g:i A') . ' - ' . $endTime->format('g:i A');
                                    } elseif ($attendance->start_time) {
                                        $startTime = is_string($attendance->start_time)
                                            ? \Carbon\Carbon::parse($attendance->date . ' ' . $attendance->start_time)
                                            : $attendance->start_time;
                                        echo $startTime->format('g:i A');
                                    }
                                } catch (\Exception $e) {
                                    echo ($attendance->start_time ?? '') . ($attendance->end_time ? ' - ' . $attendance->end_time : '');
                                }
                            @endphp
                        </td>
                    @endif
                </tr>

                <tr>
                    @if($attendance->location || $attendance->semester)

                        @if($attendance->location)
                            <td class="info-label">Location:</td>
                            <td class="info-value">{{ $attendance->location }}</td>
                        @endif

                        @if($attendance->semester)
                            <td class="info-label">Semester:</td>
                            <td class="info-value">{{ $attendance->semester->name ?? 'N/A' }}
                                ({{ $attendance->semester->school_year ?? '' }})</td>
                        @endif

                    @endif
                </tr>
                <tr>
                    @if($attendance->sections && $attendance->sections->count() > 0 || $attendance->category)

                        <td class="info-label">Section(s):</td>
                        <td class="info-value">{{ $attendance->sections->pluck('name')->join(', ') }}</td>

                        <td class="info-label">Category:</td>
                        <td class="info-value">{{ $attendance->category->name ?? 'N/A' }}</td>

                    @endif
                </tr>
                <tr>
                    @if($attendance->attendance_type || $attendance->description)

                        <td class="info-label">Type:</td>
                        <td class="info-value">{{ ucfirst($attendance->attendance_type) }}</td>

                        <td class="info-label">Description:</td>
                        <td class="info-value">{{ $attendance->description }}</td>

                    @endif
                </tr>
            </tbody>
        </table>
    </div>

    @if($studentAttendances && $studentAttendances->count() > 0)
        <div class="table-container">
            <h2 style="margin-bottom: 15px; font-size: 16px;">Student Attendance List</h2>
            <table>
                <thead>
                    <tr>
                        <!-- <th>#</th> -->
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Status</th>
                        <th>Check-In Time</th>
                        <th>Check-Out Time</th>
                        <th>Duration</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentAttendances as $index => $studentAttendance)
                        @php
                            $student = $studentAttendance->user ?? null;
                            $studentInfo = $student->studentInfo ?? $student->student_info ?? null;
                            $studentName = $student->name ??
                                (($student->first_name ?? $student->firstName ?? '') . ' ' . ($student->last_name ?? $student->lastName ?? '')) ??
                                'N/A';
                            $studentId = $studentInfo->student_number ??
                                $studentInfo->studentNumber ??
                                $studentInfo->student_id ??
                                $studentInfo->studentId ??
                                $studentInfo->id_number ??
                                $studentInfo->idNumber ??
                                'N/A';
                        @endphp
                        <tr>
                            <!-- <td>{{ $index + 1 }}</td> -->
                            <td>{{ $studentId }}</td>
                            <td>{{ $studentName }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($studentAttendance->status ?? 'pending') }}">
                                    {{ ucfirst($studentAttendance->status ?? 'Pending') }}
                                </span>
                            </td>
                            <td>
                                @if($studentAttendance->check_in_time)
                                    @php
                                        try {
                                            $checkIn = $studentAttendance->check_in_time instanceof \Carbon\Carbon
                                                ? $studentAttendance->check_in_time
                                                : \Carbon\Carbon::parse($studentAttendance->check_in_time);
                                            echo $checkIn->format('M j, Y g:i A');
                                        } catch (\Exception $e) {
                                            echo $studentAttendance->check_in_time;
                                        }
                                    @endphp
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($studentAttendance->check_out_time)
                                    @php
                                        try {
                                            $checkOut = $studentAttendance->check_out_time instanceof \Carbon\Carbon
                                                ? $studentAttendance->check_out_time
                                                : \Carbon\Carbon::parse($studentAttendance->check_out_time);
                                            echo $checkOut->format('M j, Y g:i A');
                                        } catch (\Exception $e) {
                                            echo $studentAttendance->check_out_time;
                                        }
                                    @endphp
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($studentAttendance->duration_minutes)
                                    @php
                                        $hours = floor($studentAttendance->duration_minutes / 60);
                                        $minutes = $studentAttendance->duration_minutes % 60;
                                    @endphp
                                    @if($hours > 0)
                                        {{ $hours }}h {{ $minutes }}m
                                    @else
                                        {{ $minutes }}m
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $studentAttendance->remarks ?? $studentAttendance->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="no-data">
            <p>No student attendance records found for this session.</p>
        </div>
    @endif

    <div class="footer">
        <p>This is a system-generated attendance report.</p>
        <p>Report generated by: {{ $attendance->creator->name ?? 'System' }} | Date:
            {{ now()->format('F j, Y \a\t g:i A') }}
        </p>
    </div>
</body>

</html>