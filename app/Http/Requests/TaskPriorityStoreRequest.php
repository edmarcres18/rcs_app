<?php

namespace App\Http\Requests;

use App\Models\Instruction;
use Illuminate\Foundation\Http\FormRequest;

class TaskPriorityStoreRequest extends FormRequest
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
        $user = auth()->user();

        return [
            'instruction_id' => [
                'required',
                'exists:instructions,id',
                function ($attribute, $value, $fail) use ($user) {
                    // Verify the user is a recipient of this instruction
                    $isRecipient = Instruction::whereHas('recipients', function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    })->where('id', $value)->exists();

                    if (! $isRecipient) {
                        $fail('You can only create task priorities for instructions assigned to you.');
                    }
                },
            ],
            'items' => 'required|array|min:1',
            'items.*.priority_title' => 'required|string|max:191',
            'items.*.priority_level' => 'required|in:high,normal,low',
            'items.*.start_date' => 'required|date',
            'items.*.target_deadline' => 'required|date|after_or_equal:items.*.start_date',
            'items.*.status' => 'nullable|in:Not Started,Processing,Accomplished',
            'items.*.notes' => 'nullable|string|max:1000',
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
            'instruction_id.required' => 'Please select an instruction.',
            'instruction_id.exists' => 'The selected instruction does not exist.',
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

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default status for items that don't have one
        if ($this->has('items')) {
            $items = $this->input('items', []);
            foreach ($items as $key => $item) {
                if (! isset($item['status']) || empty($item['status'])) {
                    $items[$key]['status'] = 'Not Started';
                }
            }
            $this->merge(['items' => $items]);
        }
    }
}
