<div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card crm-widget">
                <div class="card-body p-0">
                    <div class="row row-cols-xxl-5 row-cols-md-3 row-cols-1 g-0">
                        <div class="col">
                            <div class="py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Total Students <i
                                        class="ri-user-line text-primary fs-18 float-end align-middle"></i>
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-group-line display-6 text-muted cfs-22"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0 cfs-22"><span class="counter-value"
                                                data-target="{{ $totalStudents }}">0</span>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end col -->
                        <div class="col">
                            <div class="mt-3 mt-md-0 py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Active Students <i
                                        class="ri-checkbox-circle-line text-success fs-18 float-end align-middle"></i>
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-user-star-line display-6 text-muted cfs-22"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0 cfs-22"><span class="counter-value"
                                                data-target="{{ $activeStudents }}">0</span></h2>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end col -->
                        <div class="col">
                            <div class="mt-3 mt-md-0 py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Recent Enrollments <i
                                        class="ri-user-add-line text-info fs-18 float-end align-middle"></i>
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-calendar-check-line display-6 text-muted cfs-22"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0 cfs-22"><span class="counter-value"
                                                data-target="{{ $recentEnrollments }}">0</span></h2>
                                        <p class="text-muted mb-0 fs-12">Last 30 days</p>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end col -->
                        <div class="col">
                            <div class="mt-3 mt-lg-0 py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Total Programs <i
                                        class="ri-book-open-line text-warning fs-18 float-end align-middle"></i>
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-book-2-line display-6 text-muted cfs-22"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0 cfs-22"><span class="counter-value"
                                                data-target="{{ $totalPrograms }}">0</span></h2>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end col -->
                        <div class="col">
                            <div class="mt-3 mt-lg-0 py-4 px-3">
                                <h5 class="text-muted text-uppercase fs-13">Total Sections <i
                                        class="ri-building-line text-secondary fs-18 float-end align-middle"></i>
                                </h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="ri-building-2-line display-6 text-muted cfs-22"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h2 class="mb-0 cfs-22"><span class="counter-value"
                                                data-target="{{ $totalSections }}">0</span>
                                        </h2>
                                    </div>
                                </div>
                            </div>
                        </div><!-- end col -->
                    </div><!-- end row -->
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->


    <div class="row">
        <div class="col-xxl-4 col-md-6">
            <div class="card">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Students by Program</h4>
                </div><!-- end card header -->
                <div class="card-body pb-0">
                    <div id="students-by-program-chart"
                        data-series='{{ json_encode($programChartData->pluck("total")->values()) }}'
                        data-labels='{{ json_encode($programChartData->pluck("name")->values()) }}'
                        data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info", "--vz-secondary"]'
                        class="apex-charts" dir="ltr"></div>
                </div>
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xxl-4 col-md-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Students by Status</h4>
                </div><!-- end card header -->
                <div class="card-body pb-0">
                    <div id="students-by-status-chart"
                        data-series='{{ json_encode($studentsByStatus->pluck("total")->values()) }}'
                        data-labels='{{ json_encode($studentsByStatus->pluck("label")->values()) }}'
                        data-colors='["--vz-success", "--vz-warning", "--vz-danger", "--vz-info", "--vz-secondary"]'
                        class="apex-charts" dir="ltr"></div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->

        <div class="col-xxl-4 col-md-6">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Students by Year Level</h4>
                </div><!-- end card header -->
                <div class="card-body pb-0">
                    <div id="students-by-year-level-chart"
                        data-series='{{ json_encode($studentsByYearLevel->pluck("total")->values()) }}'
                        data-labels='{{ json_encode($studentsByYearLevel->pluck("label")->values()) }}'
                        data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info", "--vz-secondary"]'
                        class="apex-charts" dir="ltr"></div>
                </div><!-- end card body -->
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->

    <div class="row">
        <div class="col-xxl-12">
            <div class="card card-height-100">
                <div class="card-header align-items-center d-flex">
                    <h4 class="card-title mb-0 flex-grow-1">Enrollment Trends</h4>
                    <div class="flex-shrink-0">
                        <span class="text-muted fs-12">Last 6 Months</span>
                    </div>
                </div><!-- end card header -->
                <div class="card-body px-0">
                    <div id="enrollment-trends-chart"
                        data-categories='{{ json_encode(collect($enrollmentTrends)->pluck("month")->values()) }}'
                        data-series='{{ json_encode([["name" => "Enrollments", "data" => collect($enrollmentTrends)->pluck("count")->values()]]) }}'
                        data-colors='["--vz-primary"]' class="apex-charts" dir="ltr"></div>
                </div>
            </div><!-- end card -->
        </div><!-- end col -->
    </div><!-- end row -->
</div>

<script>
    // get colors array from the string
    function getChartColorsArray(chartId) {
        if (document.getElementById(chartId) !== null) {
            const colorAttr = "data-colors" + ("-" + document.documentElement.getAttribute("data-theme") ?? "");
            var colors = document.getElementById(chartId).getAttribute(colorAttr) ?? document.getElementById(chartId).getAttribute("data-colors");
            if (colors) {
                colors = JSON.parse(colors);
                return colors.map(function (value) {
                    var newValue = value.replace(" ", "");
                    if (newValue.indexOf(",") === -1) {
                        var color = getComputedStyle(document.documentElement).getPropertyValue(newValue);
                        if (color) return color;
                        else return newValue;;
                    } else {
                        var val = value.split(',');
                        if (val.length == 2) {
                            var rgbaColor = getComputedStyle(document.documentElement).getPropertyValue(val[0]);
                            rgbaColor = "rgba(" + rgbaColor + "," + val[1] + ")";
                            return rgbaColor;
                        } else {
                            return newValue;
                        }
                    }
                });
            } else {
                console.warn('data-colors attributes not found on', chartId);
            }
        }
    }

    function loadStudentDashboardCharts() {
        // Students by Program Chart (Donut)
        var programChartColors = getChartColorsArray("students-by-program-chart");
        if (programChartColors && document.getElementById("students-by-program-chart")) {
            var programChartElement = document.getElementById("students-by-program-chart");
            var series = JSON.parse(programChartElement.getAttribute("data-series"));
            var labels = JSON.parse(programChartElement.getAttribute("data-labels"));

            var options = {
                series: series,
                chart: {
                    type: 'donut',
                    height: 320,
                },
                labels: labels,
                colors: programChartColors,
                legend: {
                    show: true,
                    position: 'bottom',
                    horizontalAlign: 'center',
                    offsetX: 0,
                    offsetY: 0
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val.toFixed(1) + "%";
                    }
                }
            };
            var chart = new ApexCharts(document.querySelector("#students-by-program-chart"), options);
            chart.render();
        }

        // Students by Status Chart (Pie)
        var statusChartColors = getChartColorsArray("students-by-status-chart");
        if (statusChartColors && document.getElementById("students-by-status-chart")) {
            var statusChartElement = document.getElementById("students-by-status-chart");
            var series = JSON.parse(statusChartElement.getAttribute("data-series"));
            var labels = JSON.parse(statusChartElement.getAttribute("data-labels"));

            var options = {
                series: series,
                chart: {
                    type: 'pie',
                    height: 320,
                },
                labels: labels,
                colors: statusChartColors,
                legend: {
                    show: true,
                    position: 'bottom',
                    horizontalAlign: 'center',
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val.toFixed(1) + "%";
                    }
                }
            };
            var chart = new ApexCharts(document.querySelector("#students-by-status-chart"), options);
            chart.render();
        }

        // Students by Year Level Chart (Bar)
        var yearLevelChartColors = getChartColorsArray("students-by-year-level-chart");
        if (yearLevelChartColors && document.getElementById("students-by-year-level-chart")) {
            var yearLevelChartElement = document.getElementById("students-by-year-level-chart");
            var series = JSON.parse(yearLevelChartElement.getAttribute("data-series"));
            var labels = JSON.parse(yearLevelChartElement.getAttribute("data-labels"));

            var options = {
                series: [{
                    name: 'Students',
                    data: series
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false,
                    },
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: true
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: labels,
                },
                yaxis: {
                    title: {
                        text: 'Number of Students'
                    }
                },
                fill: {
                    opacity: 1
                },
                colors: yearLevelChartColors,
            };
            var chart = new ApexCharts(document.querySelector("#students-by-year-level-chart"), options);
            chart.render();
        }

        // Enrollment Trends Chart (Line)
        var trendsChartColors = getChartColorsArray("enrollment-trends-chart");
        if (trendsChartColors && document.getElementById("enrollment-trends-chart")) {
            var trendsChartElement = document.getElementById("enrollment-trends-chart");
            var categories = JSON.parse(trendsChartElement.getAttribute("data-categories"));
            var seriesData = JSON.parse(trendsChartElement.getAttribute("data-series"));

            var options = {
                series: seriesData,
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: false,
                    }
                },
                dataLabels: {
                    enabled: true
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                },
                xaxis: {
                    categories: categories,
                },
                yaxis: {
                    title: {
                        text: 'Number of Enrollments'
                    }
                },
                colors: trendsChartColors,
                fill: {
                    opacity: 0.06,
                    colors: trendsChartColors,
                    type: 'solid'
                }
            };
            var chart = new ApexCharts(document.querySelector("#enrollment-trends-chart"), options);
            chart.render();
        }
    }

    // Initialize charts when page loads
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof ApexCharts !== 'undefined') {
            loadStudentDashboardCharts();
        } else {
            // Wait for ApexCharts to load
            setTimeout(function () {
                if (typeof ApexCharts !== 'undefined') {
                    loadStudentDashboardCharts();
                }
            }, 500);
        }
    });

    // Re-initialize charts on Livewire update
    if (typeof Livewire !== 'undefined') {
        Livewire.hook('morph.updated', ({ el, component }) => {
            if (typeof ApexCharts !== 'undefined') {
                setTimeout(function () {
                    loadStudentDashboardCharts();
                }, 100);
            }
        });
    }
</script>