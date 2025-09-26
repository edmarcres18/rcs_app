<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskPriorityGroupUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->roles->value, ['EMPLOYEE', 'SUPERVISOR']);
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.priority_title' => 'required|string|max:191',
            'items.*.priority_level' => 'required|in:high,normal,low',
            'items.*.start_date' => 'required|date',
            'items.*.target_deadline' => 'required|date|after_or_equal:items.*.start_date',
            'items.*.status' => 'nullable|in:Not Started,Processing,Accomplished',
            'items.*.notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one task priority item is required.',
            'items.min' => 'At least one task priority item is required.',
            'items.*.priority_title.required' => 'Priority title is required.',
            'items.*.priority_title.max' => 'Priority title cannot exceed 191 characters.',
            'items.*.priority_level.required' => 'Priority level is required.',
            'items.*.priority_level.in' => 'Priority level must be high, normal, or low.',
            'items.*.start_date.required' => 'Start date is required.',
            'items.*.start_date.date' => 'Start date must be a valid date.',
            'items.*.target_deadline.required' => 'Target deadline is required.',
            'items.*.target_deadline.date' => 'Target deadline must be a valid date.',
            'items.*.target_deadline.after_or_equal' => 'Target deadline must be on or after the start date.',
            'items.*.status.in' => 'Status must be Not Started, Processing, or Accomplished.',
            'items.*.notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
