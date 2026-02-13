<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Grading\StudentInfoGrade;
use App\Models\Teacher\Teacher;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GradePrintController extends Controller
{
    /**
     * Load grade and authorize (shared logic).
     *
     * @param int $id
     * @return StudentInfoGrade|null
     */
    private function loadAndAuthorize(int $id): ?StudentInfoGrade
    {
        $grade = StudentInfoGrade::with([
            'subjectGrades.semester',
            'subjectGrades.subject',
            'teacher.user',
            'studentInfo.user',
        ])->find($id);

        if (!$grade) {
            return null;
        }

        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $isSuperAdmin = $user instanceof User && $user->hasRole('super-admin');
        $teacher = Teacher::where('user_id', $user->id)->first();
        
        // Authorization: super-admin, owner teacher, or the student themselves
        $isOwnerTeacher = $teacher && (int) $grade->teacher_id === (int) $teacher->id;
        $isStudent = (int) $grade->studentInfo->user_id === (int) $user->id;

        if (!$isSuperAdmin && !$isOwnerTeacher && !$isStudent) {
            return null;
        }

        return $grade;
    }

    /**
     * Stream PDF report card for use in iframe (K-12 SHS style, portrait, black border).
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id StudentInfoGrade id
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function pdf(Request $request, int $id)
    {
        $grade = $this->loadAndAuthorize($id);

        if (!$grade) {
            abort(404, 'Grade record not found or unauthorized.');
        }

        try {
            $pdf = Pdf::loadView('teacher.grading.print-report-card', compact('grade'));
            $pdf->setPaper('legal', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', false);

            $filename = 'report-card-' . Str::slug($grade->name) . '-' . $grade->school_year . '.pdf';

            return $pdf->stream($filename);
        } catch (\Exception $e) {
            Log::error('Grade print PDF error: ' . $e->getMessage());
            abort(500, 'Error generating PDF.');
        }
    }

    /**
     * Show the grade report card print view (HTML, for direct print).
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id StudentInfoGrade id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function print(Request $request, int $id)
    {
        $grade = $this->loadAndAuthorize($id);

        if (!$grade) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Grade record not found.'], 404);
            }
            return redirect()->back()->with('error', 'Grade record not found.');
        }

        return view('teacher.grading.print-report-card', [
            'grade' => $grade,
        ]);
    }
}
