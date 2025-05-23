<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Already handled by auth middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpeg,png,gif,webp', 'max:10240'], // 10MB max
            'post_id' => ['required', 'exists:posts,id']
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'No file was uploaded.',
            'file.file' => 'The uploaded file is invalid.',
            'file.mimes' => 'The file must be an image (jpeg, png, gif, or webp).',
            'file.max' => 'The file size must not exceed 10MB.',
            'post_id.required' => 'Post ID is required.',
            'post_id.exists' => 'The specified post does not exist.'
        ];
    }
}
