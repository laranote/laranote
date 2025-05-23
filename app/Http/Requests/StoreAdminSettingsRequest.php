<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            "default_role" => "nullable",
            "remove_logo" => "boolean"
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->remove_logo === 'false') {
            $this->merge(['remove_logo' => false]);
        }
        
        if ($this->remove_logo === 'true') {
            $this->merge(['remove_logo' => true]);
        }
    }
}
