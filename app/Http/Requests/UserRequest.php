<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorize based on role or use middleware for authorization
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'nickname' => ['nullable', 'string', 'max:255'],
            'roles' => ['required', new Enum(UserRole::class)],
        ];

        if ($this->isMethod('POST')) {
            // Create operation (additional rules for new users)
            $rules['email'] = ['required', 'string', 'email', 'max:255', 'unique:users'];
            $rules['password'] = ['required', Password::defaults()];
        } else {
            // Update operation
            $userId = $this->route('user')->id;
            $rules['email'] = ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $userId];
            $rules['password'] = ['nullable', Password::defaults()];

            // Make fields optional during update
            $rules['first_name'] = ['sometimes', 'string', 'max:255'];
            $rules['last_name'] = ['sometimes', 'string', 'max:255'];
            $rules['roles'] = ['sometimes', new Enum(UserRole::class)];
        }

        return $rules;
    }
}
