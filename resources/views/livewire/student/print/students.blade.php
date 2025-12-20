<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students List - {{ now()->format('Y-m-d') }}</title>
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
            width: 150px;
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
            font-size: 10px;
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
            background-color: #f8f9fa;
        }

        tbody tr:hover {
            background-color: #e9ecef;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #000;
        }

        .badge-enrolled {
            background-color: #28a745;
            color: #fff;
        }

        .badge-inactive {
            background-color: #dc3545;
            color: #fff;
        }

        .badge-graduated {
            background-color: #17a2b8;
            color: #fff;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
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
        <h1>Students List</h1>
        <div class="subtitle">
            Generated on {{ now()->format('F d, Y') }} at {{ now()->format('h:i A') }}
        </div>
    </div>

    <!-- Filter Information -->
    @if(!empty($filters))
        <div class="info-section">
            <h3 style="font-size: 14px; margin-bottom: 10px; color: #495057;">Applied Filters:</h3>
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
                <td class="info-label">Total Students:</td>
                <td class="info-value"><strong>{{ $students->count() }}</strong></td>
            </tr>
        </table>
    </div>

    <!-- Students Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 12%;">Student Number</th>
                    <th style="width: 20%;">Name</th>
                    <th style="width: 15%;">Program</th>
                    <th style="width: 8%;">Year Level</th>
                    <th style="width: 12%;">Section</th>
                    <th style="width: 10%;">School Year</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 8%;">Enrolled At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $index => $student)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $student->student_number }}</strong></td>
                        <td>{{ $student->user->name ?? 'N/A' }}</td>
                        <td>
                            @if($student->program)
                                {{ $student->program->code }} - {{ $student->program->name }}
                            @else
                                <span style="color: #999;">N/A</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($student->year_level)
                                Grade {{ $student->year_level }}
                            @else
                                <span style="color: #999;">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($student->section)
                                {{ $student->section->name }}
                            @else
                                <span style="color: #999;">N/A</span>
                            @endif
                        </td>
                        <td>{{ $student->school_year ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if($student->status === 'pending')
                                <span class="badge badge-pending">Pending</span>
                            @elseif($student->status === 'enrolled')
                                <span class="badge badge-enrolled">Enrolled</span>
                            @elseif($student->status === 'inactive')
                                <span class="badge badge-inactive">Inactive</span>
                            @elseif($student->status === 'graduated')
                                <span class="badge badge-graduated">Graduated</span>
                            @else
                                <span class="badge">{{ ucfirst($student->status) }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{ $student->enrolled_at ? $student->enrolled_at->format('M d, Y') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 20px; color: #999;">
                            No students found matching the selected filters.
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