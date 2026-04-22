<?php

namespace App\Http\Requests;

use App\Models\Section;
use App\Support\StudentSectionRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:20'],
            'description' => ['nullable', 'string', 'max:255'],
            'visibility' => ['nullable', Rule::in(['public', 'private'])],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'created_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'filter_year' => ['nullable', 'integer', 'in:1,2,3,4'],
            'filter_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'filter_section_id' => [
                'nullable',
                'integer',
                'exists:sections,id',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value) {
                        return;
                    }

                    $section = Section::query()->find($value);
                    $filterYear = $this->integer('filter_year');
                    $filterDepartmentId = $this->integer('filter_department_id');

                    if ($section === null) {
                        $fail('The selected section is invalid.');
                        return;
                    }

                    if ($filterDepartmentId > 0 && (int) $section->department_id !== $filterDepartmentId) {
                        $fail('The selected section does not belong to the selected department filter.');
                    }

                    if ($filterYear > 0 && (int) $section->year_level !== $filterYear && ! StudentSectionRules::sectionMatchesYear($section, $filterYear)) {
                        $fail('The selected section does not match the selected year filter.');
                    }
                },
            ],
        ];
    }
}
