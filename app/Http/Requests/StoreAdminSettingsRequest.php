<?php

namespace App\Http\Requests;

use App\Enums\UserRoles;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminSettingsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            "project_name" => "required|string",
            "project_logo" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048",
            "default_role" => ["nullable", Rule::enum(UserRoles::class)],
            "gemini_api_key" => "nullable|string",
            "fal_api_key" => "nullable|string",
            "openrouter_api_key" => "nullable|string",
        ];
    }
}
