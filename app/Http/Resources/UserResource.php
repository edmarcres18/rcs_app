<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'position' => $this->position,
            'avatar_url' => $this->avatar ? asset('storage/avatars/' . $this->avatar) : null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
