<?php

namespace App\Events;

use App\Models\Instruction;
use App\Models\InstructionReply;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InstructionRepliedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $instruction;
    public $reply;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Instruction $instruction, InstructionReply $reply, User $user)
    {
        $this->instruction = $instruction;
        $this->reply = $reply;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('instruction.' . $this->instruction->id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'reply' => [
                'id' => $this->reply->id,
                'content' => $this->reply->content,
                'attachment' => $this->reply->attachment_url,
                'user' => [
                    'id' => $this->user->id,
                    'name' => $this->user->full_name
                ],
                'created_at' => $this->reply->created_at->format('M d, Y g:i A')
            ]
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'instruction.new-reply';
    }
}
