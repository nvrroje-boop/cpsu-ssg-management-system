<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Contracts\View\View;

class AttendanceController extends Controller
{
    public function index(AttendanceService $attendanceService): View
    {
        return view('admin.attendance.index', [
            'summary' => $attendanceService->getSummary(),
            'recentAttendance' => $attendanceService->getRecentAttendance(),
            'sessions' => $attendanceService->getSessions(),
        ]);
    }
}
