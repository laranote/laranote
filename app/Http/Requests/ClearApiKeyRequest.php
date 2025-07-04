<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClearApiKeyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'key_type' => 'required|in:gemini,fal,openrouter,magicmk'
        ];
    }
}
