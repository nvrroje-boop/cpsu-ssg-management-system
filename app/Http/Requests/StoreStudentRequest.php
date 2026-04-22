<?php

namespace App\Http\Requests;

use App\Models\Section;
use App\Models\User;
use App\Services\StudentService;
use App\Support\StudentSectionRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = (int) $this->route('student');

        return [
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(
                    fn ($query) => $query->whereIn('role_name', StudentService::manageableRoleNames())
                ),
            ],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'year' => ['required', 'integer', 'in:1,2,3,4'],
            'section_id' => [
                'required',
                'integer',
                Rule::exists('sections', 'id'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $section = Section::query()->find($value);
                    $year = $this->integer('year');
                    $departmentId = $this->integer('department_id');

                    if ($section === null) {
                        $fail('The selected section is invalid.');
                        return;
                    }

                    if ((int) $section->department_id !== $departmentId) {
                        $fail('The selected section does not belong to the selected department.');
                    }

                    if ((int) $section->year_level !== $year && ! StudentSectionRules::sectionMatchesYear($section, $year)) {
                        $fail('The selected section does not match the selected year level.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'student_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'student_number')->ignore($studentId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($studentId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'course' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/'],
        ];
    }

    public function messages(): array
    {
        return [
            'department_id.required' => 'Department is required.',
            'department_id.exists' => 'The selected department is invalid.',
            'year.required' => 'Year is required.',
            'year.in' => 'Year must be between 1 and 4.',
            'section_id.required' => 'Section is required.',
            'section_id.exists' => 'The selected section is invalid.',
            'password.regex' => 'Password must contain uppercase, lowercase, and numeric characters.',
        ];
    }
}
