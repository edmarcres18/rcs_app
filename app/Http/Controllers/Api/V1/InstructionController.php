<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstructionResource;
use App\Models\Instruction;
use App\Models\InstructionReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $instructions = Instruction::where('sender_id', $user->id)
            ->orWhereHas('users', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['sender', 'users', 'replies.user'])
            ->latest()
            ->paginate(15);

        return InstructionResource::collection($instructions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'classification' => 'required|string',
            'target_deadline' => 'nullable|date',
            'recipient_ids' => 'required|array',
            'recipient_ids.*' => 'exists:users,id',
        ]);

        $instruction = new Instruction($validated);
        $instruction->sender_id = Auth::id();
        $instruction->save();

        $instruction->users()->sync($validated['recipient_ids']);

        // Here you would typically dispatch events or notifications
        // e.g., event(new InstructionSent($instruction));

        return response(new InstructionResource($instruction->load(['sender', 'users'])), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Instruction $instruction)
    {
        $instruction->load(['sender', 'users', 'replies.user', 'activities']);
        return new InstructionResource($instruction);
    }

    /**
     * Add a reply to an instruction.
     */
    public function reply(Request $request, Instruction $instruction)
    {
        $validated = $request->validate([
            'content' => 'required|string',
            // Add validation for attachments if needed
        ]);

        $reply = new InstructionReply($validated);
        $reply->user_id = Auth::id();
        $reply->instruction_id = $instruction->id;
        $reply->save();

        // Here you would dispatch events or notifications
        // e.g., event(new InstructionReplied($reply));

        return response()->json([
            'message' => 'Reply added successfully.',
            'reply' => $reply->load('user')
        ], 201);
    }
}
