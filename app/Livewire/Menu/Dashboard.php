<?php

namespace App\Livewire\Menu;

use Livewire\Component;
use App\Models\StudentDetails\StudentInfo;
use App\Models\StudentDetails\Program;
use App\Models\StudentDetails\Section;
use App\Models\StudentDetails\Semester;
use App\Models\Attendance\StudentAttendance;
use App\Enums\YearLevel;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    public function render()
    {
        // Get current active semester
        $activeSemester = Semester::where('is_active', true)->first();
        
        // Basic Statistics
        $totalStudents = StudentInfo::count();
        $activeStudents = StudentInfo::where('status', 'enrolled')->count();
        $pendingStudents = StudentInfo::where('status', 'pending')->count();
        $inactiveStudents = StudentInfo::where('status', 'inactive')->count();
        $graduatedStudents = StudentInfo::where('status', 'graduated')->count();
        
        // Recent enrollments (last 30 days)
        $recentEnrollments = StudentInfo::where('enrolled_at', '>=', Carbon::now()->subDays(30))
            ->count();
        
        // Students by Program (Top 5)
        $studentsByProgram = StudentInfo::select('program_id', DB::raw('count(*) as total'))
            ->whereNotNull('program_id')
            ->with('program:id,name,code')
            ->groupBy('program_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->program ? $item->program->code . ' - ' . $item->program->name : 'N/A',
                    'total' => $item->total
                ];
            });
        
        // Students by Year Level
        $studentsByYearLevel = StudentInfo::select('year_level', DB::raw('count(*) as total'))
            ->whereNotNull('year_level')
            ->groupBy('year_level')
            ->orderBy('year_level')
            ->get()
            ->map(function ($item) {
                $yearLevel = YearLevel::tryFrom($item->year_level);
                return [
                    'label' => $yearLevel ? $yearLevel->label() : "Grade {$item->year_level}",
                    'value' => $item->year_level,
                    'total' => $item->total
                ];
            });
        
        // Students by Status
        $studentsByStatus = StudentInfo::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => ucfirst($item->status ?? 'Unknown'),
                    'status' => $item->status ?? 'unknown',
                    'total' => $item->total
                ];
            });
        
        // Enrollment Trends (Last 6 months)
        $enrollmentTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            $count = StudentInfo::whereBetween('enrolled_at', [$startOfMonth, $endOfMonth])
                ->count();
            
            $enrollmentTrends[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
        
        // Chart Data for Programs (All programs, not just top 5)
        $programChartData = StudentInfo::select('program_id', DB::raw('count(*) as total'))
            ->whereNotNull('program_id')
            ->with('program:id,name,code')
            ->groupBy('program_id')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->program ? $item->program->code . ' - ' . $item->program->name : 'N/A',
                    'total' => (int) $item->total
                ];
            });
        
        // Ensure we have at least empty arrays for charts
        if ($programChartData->isEmpty()) {
            $programChartData = collect([['name' => 'No Data', 'total' => 0]]);
        }
        
        if ($studentsByYearLevel->isEmpty()) {
            $studentsByYearLevel = collect([['label' => 'No Data', 'value' => 0, 'total' => 0]]);
        }
        
        if ($studentsByStatus->isEmpty()) {
            $studentsByStatus = collect([['label' => 'No Data', 'status' => 'unknown', 'total' => 0]]);
        }
        
        if (empty($enrollmentTrends)) {
            $enrollmentTrends = [
                ['month' => 'No Data', 'count' => 0]
            ];
        }
        
        // Attendance Statistics (if available)
        $todayAttendance = null;
        $attendanceRate = null;
        if ($activeSemester) {
            $todayAttendance = StudentAttendance::whereHas('attendance', function ($query) {
                $query->whereDate('date', Carbon::today());
            })
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');
            
            $totalTodayAttendance = $todayAttendance->sum('total');
            $presentToday = $todayAttendance->get('present')?->total ?? 0;
            $attendanceRate = $totalTodayAttendance > 0 
                ? round(($presentToday / $totalTodayAttendance) * 100, 1) 
                : 0;
        }
        
        // Total Sections
        $totalSections = Section::where('active', true)->count();
        
        // Total Programs
        $totalPrograms = Program::where('active', true)->count();
        
        return view('livewire.menu.dashboard', [
            'totalStudents' => $totalStudents,
            'activeStudents' => $activeStudents,
            'pendingStudents' => $pendingStudents,
            'inactiveStudents' => $inactiveStudents,
            'graduatedStudents' => $graduatedStudents,
            'recentEnrollments' => $recentEnrollments,
            'studentsByProgram' => $studentsByProgram,
            'studentsByYearLevel' => $studentsByYearLevel,
            'studentsByStatus' => $studentsByStatus,
            'enrollmentTrends' => $enrollmentTrends,
            'programChartData' => $programChartData,
            'todayAttendance' => $todayAttendance,
            'attendanceRate' => $attendanceRate,
            'totalSections' => $totalSections,
            'totalPrograms' => $totalPrograms,
            'activeSemester' => $activeSemester,
        ]);
    }
}
