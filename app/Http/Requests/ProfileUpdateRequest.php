<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $emailHash = hash('sha256', strtolower(trim((string) $value)));

                    $query = User::query()->where('email_hash', $emailHash);

                    if ($this->user()) {
                        $query->whereKeyNot($this->user()->id);
                    }

                    if ($query->exists()) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
        ];
    }
}
