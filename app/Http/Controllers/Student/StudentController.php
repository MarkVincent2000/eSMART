<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentDetails\StudentInfo;
use App\Models\StudentDetails\Semester;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\Program;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * Generate PDF for students list
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function print(Request $request)
    {
        try {
            $query = StudentInfo::with(['user', 'program', 'section']);
            
            // Status filter
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
            
            // School year filter
            if ($request->has('school_years') && !empty($request->school_years)) {
                $schoolYears = array_filter(explode(',', $request->school_years));
                if (!empty($schoolYears)) {
                    $query->whereIn('school_year', $schoolYears);
                }
            }
            
            // Section filter
            if ($request->has('sections') && !empty($request->sections)) {
                $sectionIds = array_filter(explode(',', $request->sections));
                if (!empty($sectionIds)) {
                    $query->whereIn('section_id', $sectionIds);
                }
            }
            
            // Program filter
            if ($request->has('programs') && !empty($request->programs)) {
                $programIds = array_filter(explode(',', $request->programs));
                if (!empty($programIds)) {
                    $query->whereIn('program_id', $programIds);
                }
            }
            
            // Search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('student_number', 'like', '%' . $search . '%')
                      ->orWhere('school_year', 'like', '%' . $search . '%')
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', '%' . $search . '%')
                                    ->orWhere('email', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('program', function($programQuery) use ($search) {
                          $programQuery->where('name', 'like', '%' . $search . '%')
                                       ->orWhere('code', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('section', function($sectionQuery) use ($search) {
                          $sectionQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            }
            
            // Get students ordered by name
            $students = $query->orderBy('created_at', 'desc')->get();
            
            // Get filter details for display
            $filters = [];
            if ($request->has('status') && $request->status !== 'all') {
                $filters['Status'] = ucfirst($request->status);
            }
            if ($request->has('school_years') && !empty($request->school_years)) {
                $schoolYears = array_filter(explode(',', $request->school_years));
                if (!empty($schoolYears)) {
                    $filters['School Years'] = implode(', ', $schoolYears);
                }
            }
            if ($request->has('sections') && !empty($request->sections)) {
                $sectionIds = array_filter(explode(',', $request->sections));
                if (!empty($sectionIds)) {
                    $sections = Section::whereIn('id', $sectionIds)->get();
                    $filters['Sections'] = $sections->map(fn($s) => $s->name)->join(', ');
                }
            }
            if ($request->has('programs') && !empty($request->programs)) {
                $programIds = array_filter(explode(',', $request->programs));
                if (!empty($programIds)) {
                    $programs = Program::whereIn('id', $programIds)->get();
                    $filters['Programs'] = $programs->map(fn($p) => $p->code . ' - ' . $p->name)->join(', ');
                }
            }
            
            // Generate PDF
            $pdf = Pdf::loadView('livewire.student.print.students', compact('students', 'filters'));
            
            // Set PDF options
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', false);
            
            // Generate filename
            $filename = 'students-list-' . now()->format('Y-m-d') . '.pdf';
            
            // Return PDF as stream for iframe
            return $pdf->stream($filename);
            
        } catch (\Exception $e) {
            Log::error('Error generating student PDF: ' . $e->getMessage());
            abort(500, 'Error generating PDF: ' . $e->getMessage());
        }
    }
}

