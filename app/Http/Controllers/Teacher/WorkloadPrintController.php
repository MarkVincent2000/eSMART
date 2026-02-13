<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher\Workload;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\Semester;
use App\Models\Subject;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WorkloadPrintController extends Controller
{
    /**
     * Generate PDF for teacher workloads list.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function print(Request $request)
    {
        try {
            $query = Workload::with(['teacher.user', 'subject', 'section', 'semester', 'classroom']);

            // Section filter
            if ($request->has('section_id') && !empty($request->section_id)) {
                $query->where('section_id', (int) $request->section_id);
            }

            // Semester filter
            if ($request->has('semester_id') && !empty($request->semester_id)) {
                $query->where('semester_id', (int) $request->semester_id);
            }

            // Subject filter
            if ($request->has('subject_id') && !empty($request->subject_id)) {
                $query->where('subject_id', (int) $request->subject_id);
            }

            // Order workloads by teacher then subject
            $workloads = $query->orderBy('teacher_id')
                ->orderBy('subject_id')
                ->get();

            // Build filters summary
            $filters = [];

            if ($request->has('section_id') && !empty($request->section_id)) {
                $section = Section::find((int) $request->section_id);
                if ($section) {
                    $label = $section->name;
                    if ($section->year_level) {
                        $gradeLevel = $section->year_level instanceof \App\Enums\YearLevel
                            ? $section->year_level->label()
                            : 'Grade ' . $section->year_level;
                        $label .= ' (' . $gradeLevel . ')';
                    }
                    $filters['Section'] = $label;
                }
            }

            if ($request->has('semester_id') && !empty($request->semester_id)) {
                $semester = Semester::find((int) $request->semester_id);
                if ($semester) {
                    $label = $semester->name;
                    if ($semester->school_year) {
                        $label .= ' (' . $semester->school_year . ')';
                    }
                    $filters['Semester'] = $label;
                }
            }

            if ($request->has('subject_id') && !empty($request->subject_id)) {
                $subject = Subject::find((int) $request->subject_id);
                if ($subject) {
                    $filters['Subject'] = $subject->display_name
                        ?? ($subject->code ? $subject->code . ' - ' . $subject->name : $subject->name);
                }
            }

            // Generate PDF
            $pdf = Pdf::loadView('livewire.teacher.print.workloads', compact('workloads', 'filters'));

            // Use landscape for wider table
            $pdf->setPaper('a4', 'landscape');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', false);

            $filename = 'teacher-workloads-' . now()->format('Y-m-d') . '.pdf';

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Error generating teacher workloads PDF: ' . $e->getMessage());
            abort(500, 'Error generating PDF: ' . $e->getMessage());
        }
    }
}

