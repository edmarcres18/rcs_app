<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskPriorityUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() &&
               in_array(auth()->user()->roles->value, ['EMPLOYEE', 'SUPERVISOR']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'priority_title' => 'required|string|max:191',
            'priority_level' => 'required|in:high,normal,low',
            'start_date' => 'required|date',
            'target_deadline' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:Not Started,Processing,Accomplished',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'priority_title.required' => 'Priority title is required.',
            'priority_title.max' => 'Priority title cannot exceed 191 characters.',
            'priority_level.required' => 'Priority level is required.',
            'priority_level.in' => 'Priority level must be high, normal, or low.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be a valid date.',
            'target_deadline.required' => 'Target deadline is required.',
            'target_deadline.date' => 'Target deadline must be a valid date.',
            'target_deadline.after_or_equal' => 'Target deadline must be on or after the start date.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be Not Started, Processing, or Accomplished.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
