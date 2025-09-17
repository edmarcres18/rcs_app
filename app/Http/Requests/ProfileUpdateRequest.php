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
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user()->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
            'telegram_username' => ['nullable', 'string', 'max:255'],
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
            'first_name.required' => 'The first name field is required.',
            'last_name.required' => 'The last name field is required.',
            'email.required' => 'The email address field is required.',
            'email.email' => 'The email address must be a valid email address.',
            'email.unique' => 'The email address has already been taken.',
        ];
    }
}
