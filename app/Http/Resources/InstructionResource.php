<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructionResource extends JsonResource
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
            'subject' => $this->subject,
            'content' => $this->content,
            'classification' => $this->classification,
            'status' => $this->status,
            'target_deadline' => $this->target_deadline,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'sender' => new UserResource($this->whenLoaded('sender')),
            'recipients' => UserResource::collection($this->whenLoaded('users')),
            'replies' => InstructionReplyResource::collection($this->whenLoaded('replies')),
            'activities' => $this->whenLoaded('activities'), // Assuming a simple array structure for activities
        ];
    }
}
