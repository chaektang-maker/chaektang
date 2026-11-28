<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    protected function prepareForValidation(): void
    {
        $raw = trim((string) $this->input('email', ''));
        $digitsOnly = preg_replace('/[^0-9]/', '', $raw);

        if (!str_contains($raw, '@') && preg_match('/^0[0-9]{9}$/', $digitsOnly)) {
            $this->merge(['email' => $digitsOnly]);
        } else {
            $this->merge(['email' => strtolower($raw)]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $str = (string) $value;
                    if (!str_contains($str, '@')) {
                        if (!preg_match('/^0[0-9]{9}$/', $str)) {
                            $fail('กรุณากรอกเบอร์โทรศัพท์มือถือ 10 หลัก หรืออีเมลให้ถูกต้อง');
                        }
                    } elseif (!filter_var($str, FILTER_VALIDATE_EMAIL)) {
                        $fail('รูปแบบอีเมลไม่ถูกต้อง');
                    }
                },
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
