<?php

namespace App\Http\Requests;

use App\Services\ConcernService;
use Illuminate\Foundation\Http\FormRequest;

class StoreConcernRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_reference' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = $this->user();

                    if ($user === null || ! is_string($value)) {
                        $fail('Please choose a valid announcement or event title.');
                        return;
                    }

                    $source = app(ConcernService::class)->resolveStudentSource($user, $value);

                    if ($source === null) {
                        $fail('Please choose a valid announcement or event title.');
                    }
                },
            ],
            'description' => ['required', 'string', 'min:10'],
        ];
    }
}
