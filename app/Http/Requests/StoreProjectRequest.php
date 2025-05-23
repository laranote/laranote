<?php

namespace App\Http\Requests;

use App\Enums\AuthType;
use App\Enums\UserRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:1024|mimes:jpeg,png,jpg,gif', // max 1MB
            'default_user_role' => ['required', 'integer', Rule::in(UserRoles::cases())],
            'auth_type' => ['required', 'integer', Rule::in(AuthType::cases())],
            'magicmk_slug' => [
                'nullable',
                'string',
                'max:255',
            ],
            'magicmk_api_key' => [
                'nullable',
                'string',
                'max:255',
            ]
        ];
    }
}
