<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Ai\RcsAssistant;

class AiAssistantController extends Controller
{
    public function __invoke(Request $request, RcsAssistant $assistant)
    {
        if (!config('app.ai_assistant_enabled', false)) {
            return response()->json([
                'success' => false,
                'message' => 'AI assistant is disabled.'
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'question' => ['required', 'string', 'max:5000']
        ]);

        $question = trim($request->input('question'));

        try {
            $answer = $assistant->answer($question, $request->user());

            return response()->json([
                'success' => true,
                'answer' => $answer,
            ]);
        } catch (\Throwable $e) {
            Log::error('AI Assistant error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to process your request at the moment.'
            ], Response::HTTP_BAD_GATEWAY);
        }
    }
}
