<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'post_category_id' => ['sometimes', 'required', 'integer', 'exists:posts_categories,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'content' => ['sometimes', 'required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'], // 10MB max
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
            'user_id.required' => 'The user ID field is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'client_id.required' => 'The client ID field is required.',
            'client_id.exists' => 'The selected client does not exist.',
            'post_category_id.required' => 'The post category ID field is required.',
            'post_category_id.exists' => 'The selected post category does not exist.',
            'title.required' => 'The title field is required.',
            'content.required' => 'The content field is required.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, jpg, png, gif, webp.',
            'image.max' => 'The image may not be greater than 10MB.',
        ];
    }
}

