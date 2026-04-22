@extends('layouts.admin')

@section('title', 'Event Attendance - ' . $event->event_title)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('admin.attendance.index') }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-block">
            ← Back to Analytics
        </a>
        <h1 class="text-3xl font-bold">{{ $event->event_title }}</h1>
        <p class="text-gray-600 mt-2">
            📅 {{ $event->event_date?->format('F j, Y') ?? 'TBA' }} at
            {{ substr((string) $event->event_time, 0, 5) }}
        </p>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm mb-1">Total Students</p>
            <p class="text-3xl font-bold">{{ $totalStudents }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm mb-1">Present</p>
            <p class="text-3xl font-bold text-green-600">{{ $attendanceCount }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm mb-1">Absent</p>
            <p class="text-3xl font-bold text-red-600">{{ $totalStudents - $attendanceCount }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-600 text-sm mb-1">Attendance Rate</p>
            <p class="text-3xl font-bold text-blue-600">{{ $attendanceRate }}%</p>
        </div>
    </div>

    <!-- Data Visualization -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Attendance Overview -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">📊 Attendance Overview</h2>
            <canvas id="attendanceOverviewChart" height="100"></canvas>
        </div>

        <!-- Department Breakdown -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">🏢 Department Breakdown</h2>
            <canvas id="departmentBreakdownChart" height="100"></canvas>
        </div>

        <!-- Hourly Distribution (if available) -->
        @if (count($hourlyDistribution) > 0)
            <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
                <h2 class="text-xl font-semibold mb-4">⏰ Hourly Distribution</h2>
                <canvas id="hourlyDistributionChart" height="80"></canvas>
            </div>
        @endif
    </div>

    <!-- Attendees List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">👥 Students Who Attended</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Student Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Student Number</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Department</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Scanned At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-medium">{{ $attendance->student->name }}</td>
                            <td class="px-6 py-4 text-sm">{{ $attendance->student->student_number }}</td>
                            <td class="px-6 py-4 text-sm">{{ $attendance->student->department?->department_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $attendance->student->email }}</td>
                            <td class="px-6 py-4 text-sm">
                                {{ $attendance->scanned_at?->format('M d, Y H:i A') ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-600">
                                No attendances recorded for this event
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Export Actions -->
    <div class="mt-8 flex gap-4">
        <a
            href="{{ route('admin.attendance.export-event', $event->id) }}"
            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
        >
            📥 Export as CSV
        </a>
        <a
            href="{{ route('admin.attendance.print-event', $event->id) }}"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
            🖨️ Print Report
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Attendance Overview Chart (Pie)
    const overviewCtx = document.getElementById('attendanceOverviewChart')?.getContext('2d');
    if (overviewCtx) {
        const present = {{ $attendanceCount }};
        const absent = {{ $totalStudents - $attendanceCount }};

        new Chart(overviewCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Absent'],
                datasets: [{
                    data: [present, absent],
                    backgroundColor: ['#10b981', '#ef4444'],
                    borderColor: '#fff',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }

    // Department Breakdown Chart (Bar)
    const deptCtx = document.getElementById('departmentBreakdownChart')?.getContext('2d');
    if (deptCtx) {
        const deptData = @json($departmentBreakdown);

        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(deptData),
                datasets: [{
                    label: 'Attendances',
                    data: Object.values(deptData),
                    backgroundColor: '#3b82f6',
                    borderColor: '#1e40af',
                    borderWidth: 1,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    x: {
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    // Hourly Distribution Chart (Line)
    const hourlyCtx = document.getElementById('hourlyDistributionChart')?.getContext('2d');
    if (hourlyCtx) {
        const hourlyData = @json($hourlyDistribution);

        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: Object.keys(hourlyData),
                datasets: [{
                    label: 'Scans',
                    data: Object.values(hourlyData),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#f59e0b',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                }
            }
        });
    }
</script>
@endsection
