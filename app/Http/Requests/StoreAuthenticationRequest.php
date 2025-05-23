<?php

namespace App\Http\Requests;

use App\Enums\AuthType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAuthenticationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'auth_type' => ['required', Rule::enum(AuthType::class)],
            'magicmk_slug' => ['nullable', 'required_if:auth_type,' . AuthType::MAGIC_MK->value, 'string'],
            'magicmk_api_key' => ['nullable', 'required_if:auth_type,' . AuthType::MAGIC_MK->value, 'string'],
        ];
    }
}
