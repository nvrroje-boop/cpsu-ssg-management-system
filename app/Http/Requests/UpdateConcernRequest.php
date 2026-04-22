<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConcernRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,in_review,resolved'],
            'assigned_to_user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query): void {
                    $query->whereIn('role_id', function ($roleQuery): void {
                        $roleQuery->select('id')
                            ->from('roles')
                            ->whereIn('role_name', [User::ROLE_ADMIN, User::ROLE_OFFICER, User::ROLE_SSG_OFFICER]);
                    });
                }),
            ],
            'reply_message' => ['nullable', 'string', 'min:5'],
            'send_reply_email' => ['nullable', 'boolean'],
        ];
    }
}
