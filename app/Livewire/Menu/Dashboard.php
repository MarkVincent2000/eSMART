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
    public $selectedPeriod = '1year'; // '1month', '6months', '1year'
    public $selectedYear = null;

    public function mount()
    {
        // Set default year to current year
        $this->selectedYear = Carbon::now()->year;
    }

    public function setPeriod($period)
    {
        $this->selectedPeriod = $period;
    }

    public function updatedSelectedYear()
    {
        // Reset period when year changes
    }

    public function getAvailableYears()
    {
        $years = StudentInfo::whereNotNull('enrolled_at')
            ->selectRaw('YEAR(enrolled_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();
        
        // If no years found, return current year
        if (empty($years)) {
            return [Carbon::now()->year];
        }
        
        return $years;
    }

    public function getEnrollmentTrends()
    {
        $enrollmentTrends = [];
        $yearToUse = $this->selectedYear ? (int) $this->selectedYear : Carbon::now()->year;
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;
        
        // Handle 1 year period - ALWAYS generate all 12 months
        if ($this->selectedPeriod === '1year') {
            for ($month = 1; $month <= 12; $month++) {
                $monthStart = Carbon::create($yearToUse, $month, 1)->startOfMonth();
                $monthEnd = Carbon::create($yearToUse, $month, 1)->endOfMonth();
                
                $enrolled = StudentInfo::whereBetween('enrolled_at', [$monthStart, $monthEnd])
                    ->where('status', 'enrolled')->count();
                $pending = StudentInfo::whereBetween('enrolled_at', [$monthStart, $monthEnd])
                    ->where('status', 'pending')->count();
                $inactive = StudentInfo::whereBetween('enrolled_at', [$monthStart, $monthEnd])
                    ->where('status', 'inactive')->count();
                
                $enrollmentTrends[] = [
                    'month' => Carbon::create($yearToUse, $month, 1)->format('M Y'),
                    'enrolled' => $enrolled,
                    'pending' => $pending,
                    'inactive' => $inactive,
                ];
            }
            return $enrollmentTrends;
        }
        
        // Handle 1 month period
        if ($this->selectedPeriod === '1month') {
            if ($yearToUse == $currentYear) {
                $startMonth = Carbon::create($yearToUse, $currentMonth, 1);
            } else {
                $startMonth = Carbon::create($yearToUse, 12, 1);
            }
            
            $monthStart = $startMonth->copy()->startOfMonth();
            $monthEnd = $startMonth->copy()->endOfMonth();
            
            $enrolled = StudentInfo::whereBetween('enrolled_at', [$monthStart, $monthEnd])
                ->where('status', 'enrolled')->count();
            $pending = StudentInfo::whereBetween('enrolled_at', [$monthStart, $monthEnd])
                ->where('status', 'pending')->count();
            $inactive = StudentInfo::whereBetween('enrolled_at', [$monthStart, $monthEnd])
                ->where('status', 'inactive')->count();
            
            $enrollmentTrends[] = [
                'month' => $startMonth->format('M Y'),
                'enrolled' => $enrolled,
                'pending' => $pending,
                'inactive' => $inactive,
            ];
            
            return $enrollmentTrends;
        }
        
        // Handle 6 months period (default)
        if ($yearToUse == $currentYear) {
            $startMonth = Carbon::create($yearToUse, $currentMonth, 1)->subMonths(5);
        } else {
            $startMonth = Carbon::create($yearToUse, 7, 1);
        }
        
        $current = $startMonth->copy()->startOfMonth();
        for ($i = 0; $i < 6; $i++) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();
            
            $enrolled = StudentInfo::whereBetween('enrolled_at', [$monthStart, $monthEnd])
                ->where('status', 'enrolled')->count();
            $pending = StudentInfo::whereBetween('enrolled_at', [$monthStart, $monthEnd])
                ->where('status', 'pending')->count();
            $inactive = StudentInfo::whereBetween('enrolled_at', [$monthStart, $monthEnd])
                ->where('status', 'inactive')->count();
            
            $enrollmentTrends[] = [
                'month' => $current->format('M Y'),
                'enrolled' => $enrolled,
                'pending' => $pending,
                'inactive' => $inactive,
            ];
            
            $current->addMonth();
        }
        
        return $enrollmentTrends;
    }

    public function getTotalEnrollmentForYear()
    {
        $yearToUse = $this->selectedYear ? (int) $this->selectedYear : Carbon::now()->year;
        $yearStart = Carbon::create($yearToUse, 1, 1)->startOfYear();
        $yearEnd = Carbon::create($yearToUse, 12, 31)->endOfYear();
        
        // Filter by status: enrolled, inactive, pending
        $statusFilter = ['enrolled', 'inactive', 'pending'];
        
        return StudentInfo::whereBetween('enrolled_at', [$yearStart, $yearEnd])
            ->whereIn('status', $statusFilter)
            ->count();
    }

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
        
        // Enrollment Trends (based on selected period and year)
        $enrollmentTrends = $this->getEnrollmentTrends();
        
        // Get available years for dropdown
        $availableYears = $this->getAvailableYears();
        
        // Get total enrollment for selected year
        $totalEnrollmentForYear = $this->getTotalEnrollmentForYear();
        
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
        
        // Don't override enrollment trends with "No Data" - always show the actual months
        // The getEnrollmentTrends() method already ensures correct number of months
        
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
            'availableYears' => $availableYears,
            'totalEnrollmentForYear' => $totalEnrollmentForYear,
        ]);
    }
}
