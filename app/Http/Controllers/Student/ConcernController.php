<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConcernRequest;
use App\Models\Concern;
use App\Services\ConcernService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ConcernController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        return view('student.concerns.index', [
            'concerns' => Concern::query()
                ->with(['replier', 'source'])
                ->where('submitted_by_user_id', $student->id)
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function create(ConcernService $concernService): View
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        return view('student.concerns.create', [
            'titleOptions' => $concernService->titleOptionsForStudent($student),
        ]);
    }

    public function store(StoreConcernRequest $request, ConcernService $concernService): RedirectResponse
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        $sourceReference = $request->string('source_reference')->toString();
        $sourceModel = $concernService->resolveStudentSource($student, $sourceReference);

        if ($sourceModel === null) {
            return back()
                ->withErrors(['source_reference' => 'Please choose a valid announcement or event title.'])
                ->withInput();
        }

        Concern::query()->create([
            'title' => $sourceModel instanceof \App\Models\Announcement
                ? $sourceModel->title
                : $sourceModel->event_title,
            'source_type' => $sourceModel::class,
            'source_id' => $sourceModel->id,
            'description' => $request->string('description')->toString(),
            'submitted_by_user_id' => $student->id,
        ]);

        return redirect()
            ->route('student.concerns.index')
            ->with('success', 'Concern submitted successfully.');
    }

    public function show(int $concern): View
    {
        /** @var \App\Models\User $student */
        $student = Auth::user();

        return view('student.concerns.show', [
            'concern' => Concern::query()
                ->with(['replier', 'source'])
                ->where('submitted_by_user_id', $student->id)
                ->findOrFail($concern),
        ]);
    }
}
