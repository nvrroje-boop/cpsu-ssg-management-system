<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateConcernRequest;
use App\Mail\ConcernReplyMail;
use App\Models\Concern;
use App\Models\EmailLog;
use App\Models\User;
use App\Services\SystemNotificationService;
use App\Support\AppUrl;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ConcernController extends Controller
{
    public function index(): View
    {
        return view('admin.concerns.index', [
            'concerns' => Concern::query()
                ->with(['submitter', 'replier', 'source'])
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function show(int $concern): View
    {
        $record = Concern::query()
            ->with(['submitter', 'assignee', 'replier', 'source'])
            ->findOrFail($concern);

        return view('admin.concerns.show', [
            'concern' => $record,
            'assignees' => User::query()
                ->with('role')
                ->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('role_name', [User::ROLE_ADMIN, User::ROLE_OFFICER, User::ROLE_SSG_OFFICER]))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateConcernRequest $request, int $concern): RedirectResponse
    {
        $record = Concern::query()->findOrFail($concern);
        $validated = $request->validated();

        $replyMessage = trim((string) ($validated['reply_message'] ?? ''));
        $status = $replyMessage !== '' ? 'resolved' : ($validated['status'] ?? $record->status);

        $record->update([
            'status' => $status,
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'reply_message' => $replyMessage !== '' ? $replyMessage : $record->reply_message,
            'replied_by_user_id' => $replyMessage !== '' ? Auth::id() : $record->replied_by_user_id,
            'replied_at' => $replyMessage !== '' ? now() : $record->replied_at,
        ]);

        if (($validated['send_reply_email'] ?? false) && $replyMessage !== '' && filled($record->submitter?->email)) {
            $this->sendReplyEmail($record->fresh(['submitter']));
        }

        if ($replyMessage !== '' && $record->submitter !== null) {
            app(SystemNotificationService::class)->createForUser(
                $record->submitter,
                'Concern updated',
                'Your concern "'.$record->title.'" has a new update.',
                'concern',
                route('student.concerns.show', $record->id),
            );
        }

        return redirect()
            ->route($this->portalRouteName('concerns.index'))
            ->with('success', 'Concern updated successfully.');
    }

    private function sendReplyEmail(Concern $concern): void
    {
        try {
            Mail::to($concern->submitter->email)->send(
                new ConcernReplyMail(
                    studentName: $concern->submitter->name,
                    concernTitle: $concern->title,
                    replyMessage: $concern->reply_message ?? '',
                    concernsUrl: AppUrl::route('student.concerns.index'),
                )
            );

            EmailLog::query()->create([
                'user_id' => $concern->submitter->id,
                'email' => $concern->submitter->email,
                'subject' => 'Update on Your SSG Concern',
                'message' => $concern->reply_message,
                'status' => 'sent',
                'email_type' => 'concern_reply',
                'sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            EmailLog::query()->create([
                'user_id' => $concern->submitter->id,
                'email' => $concern->submitter->email,
                'subject' => 'Update on Your SSG Concern',
                'message' => $concern->reply_message,
                'status' => 'failed',
                'email_type' => 'concern_reply',
                'error_message' => $exception->getMessage(),
                'sent_at' => now(),
                'last_attempt_at' => now(),
            ]);
        }
    }
}
