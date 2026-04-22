@extends('layouts.admin')

@section('title', 'Attendance Analytics')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">📊 Attendance Analytics Dashboard</h1>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Events</p>
                    <p class="text-3xl font-bold">{{ $totalEvents }}</p>
                </div>
                <div class="text-blue-500 text-4xl">📅</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Students</p>
                    <p class="text-3xl font-bold">{{ $totalStudents }}</p>
                </div>
                <div class="text-green-500 text-4xl">👥</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Total Attendances</p>
                    <p class="text-3xl font-bold">{{ $totalAttendances }}</p>
                </div>
                <div class="text-purple-500 text-4xl">✅</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm">Overall Rate</p>
                    <p class="text-3xl font-bold">{{ $overallAttendanceRate }}%</p>
                </div>
                <div class="text-orange-500 text-4xl">📈</div>
            </div>
        </div>
    </div>

    <!-- QR Code Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-6">
            <p class="text-blue-600 font-semibold text-sm mb-2">QR Codes Generated</p>
            <p class="text-2xl font-bold text-blue-700">{{ $qrStats['total_generated'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow p-6">
            <p class="text-green-600 font-semibold text-sm mb-2">Valid QR Codes</p>
            <p class="text-2xl font-bold text-green-700">{{ $qrStats['valid_remaining'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow p-6">
            <p class="text-yellow-600 font-semibold text-sm mb-2">Used QR Codes</p>
            <p class="text-2xl font-bold text-yellow-700">{{ $qrStats['used'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow p-6">
            <p class="text-red-600 font-semibold text-sm mb-2">Expired QR Codes</p>
            <p class="text-2xl font-bold text-red-700">{{ $qrStats['expired'] }}</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Attendance Trend Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">📈 Attendance Trend (Last 7 Days)</h2>
            <canvas id="attendanceTrendChart" height="80"></canvas>
        </div>

        <!-- Department Distribution -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">🏢 Attendance by Department</h2>
            <canvas id="departmentChart" height="80"></canvas>
        </div>
    </div>

    <!-- Recent Events Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">📅 Recent Events</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Event Title</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Attendances</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Attendance Rate</th>
                        <th class="px-6 py-3 text-center text-sm font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentEvents as $event)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm">{{ $event['title'] }}</td>
                            <td class="px-6 py-4 text-sm">{{ $event['date'] }}</td>
                            <td class="px-6 py-4 text-center text-sm font-semibold">
                                {{ $event['attendances'] }} / {{ $event['total_students'] }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-32 bg-gray-200 rounded-full h-2">
                                        <div
                                            class="bg-blue-600 h-2 rounded-full"
                                            style="width: {{ $event['rate'] }}%"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-semibold">{{ $event['rate'] }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a
                                    href="{{ route('admin.attendance.event', $event['id']) }}"
                                    class="text-blue-600 hover:text-blue-800 font-semibold text-sm"
                                >
                                    View Details →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-600">
                                No events found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Attendance Trend Chart
    const trendCtx = document.getElementById('attendanceTrendChart')?.getContext('2d');
    if (trendCtx) {
        const trendData = @json($attendanceTrend);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'Attendances',
                    data: trendData.map(d => d.count),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 6,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                }
            }
        });
    }

    // Department Chart
    const deptCtx = document.getElementById('departmentChart')?.getContext('2d');
    if (deptCtx) {
        const deptData = @json($departmentStats);

        new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: deptData.map(d => d.department),
                datasets: [{
                    data: deptData.map(d => d.attendances),
                    backgroundColor: [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#06b6d4',
                    ],
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
</script>
@endsection
