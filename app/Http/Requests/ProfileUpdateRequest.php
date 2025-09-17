<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-\'\.]+$/'],
            'nickname' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\s\-_\.]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'avatar' => [
                'nullable', 
                'image', 
                'mimes:jpeg,png,jpg,gif,webp', 
                'max:10240',
                'dimensions:min_width=50,min_height=50,max_width=2000,max_height=2000'
            ],
            'telegram_username' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9_]+$/'],
            'telegram_notifications_enabled' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'middle_name' => 'middle name',
            'nickname' => 'nickname',
            'email' => 'email address',
            'avatar' => 'profile picture',
            'telegram_username' => 'telegram username',
            'telegram_notifications_enabled' => 'telegram notifications',
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
            'avatar.image' => 'The profile picture must be an image file.',
            'avatar.mimes' => 'The profile picture must be a file of type: jpeg, png, jpg, gif, webp.',
            'avatar.max' => 'The profile picture must not be larger than 10MB.',
            'avatar.dimensions' => 'The profile picture must be between 50x50 and 2000x2000 pixels.',
            'first_name.required' => 'The first name field is required.',
            'first_name.regex' => 'The first name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.required' => 'The last name field is required.',
            'last_name.regex' => 'The last name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'middle_name.regex' => 'The middle name may only contain letters, spaces, hyphens, apostrophes, and periods.',
            'nickname.regex' => 'The nickname may only contain letters, numbers, spaces, hyphens, underscores, and periods.',
            'telegram_username.regex' => 'The telegram username may only contain letters, numbers, and underscores.',
            'email.required' => 'The email address field is required.',
            'email.email' => 'The email address must be a valid email address.',
            'email.unique' => 'The email address has already been taken.',
        ];
    }
}
