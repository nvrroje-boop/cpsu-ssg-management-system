<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        /** @var User $student */
        $student = Auth::user();

        return view('student.announcements.index', [
            'announcements' => Announcement::query()
                ->published()
                ->visibleToUser($student)
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (Announcement $announcement): array => [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'description' => $announcement->description ?: str($announcement->message)->limit(160)->toString(),
                    'visibility' => ucfirst($announcement->visibility),
                    'created_at' => $announcement->created_at->format('M d, Y'),
                ])
                ->all(),
        ]);
    }

    public function show(int $announcement): View
    {
        /** @var User $student */
        $student = Auth::user();

        $record = Announcement::query()
            ->published()
            ->visibleToUser($student)
            ->findOrFail($announcement);

        return view('student.announcements.show', [
            'announcement' => $record,
        ]);
    }
}
