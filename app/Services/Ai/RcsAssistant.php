<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\Authenticatable;

class RcsAssistant
{
    protected string $model;
    protected string $apiKey;

    public function __construct()
    {
        $this->model = (string) config('services.ai.model', 'gemini-1.5-flash');
        $this->apiKey = (string) config('services.ai.api_key', '');
    }

    /**
     * Answer a user question using help.blade.php as knowledge base.
     */
    public function answer(string $question, ?Authenticatable $user = null): string
    {
        if (empty($this->apiKey)) {
            return 'AI assistant is not configured. Please set GOOGLE_AI_API_KEY in .env';
        }

        // Load KB from help page by rendering the Blade view so Blade/PHP values are resolved
        try {
            $kbHtml = view('help')->render();
        } catch (\Throwable $e) {
            // Fallback to raw file contents if rendering fails
            $kbPath = resource_path('views/help.blade.php');
            $kbHtml = is_file($kbPath) ? file_get_contents($kbPath) : '';
        }
        $kb = $this->cleanBladeToPlainText($kbHtml);
        $images = $this->extractImageUrls($kbHtml);
        // Truncate to keep prompt under limits
        $kb = mb_substr($kb, 0, 120000);

        $system = $this->buildSystemPrompt();

        $imagesContext = '';
        if (!empty($images)) {
            $imagesContext = "\n\nImages from Help (use when relevant):\n";
            $i = 1;
            foreach ($images as $img) {
                $imagesContext .= $i . '. ' . $img['url'] . (!empty($img['alt']) ? (' — alt: ' . $img['alt']) : '') . "\n";
                $i++;
                if ($i > 12) break; // limit
            }
        }

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [ 'text' => $system ],
                        [ 'text' => "Knowledge Base (help.blade.php):\n" . $kb . $imagesContext ],
                        [ 'text' => "User Question: \n" . trim($question) ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'topK' => 40,
                'topP' => 0.9,
                'maxOutputTokens' => 1024,
            ],
        ];

        try {
            $endpoint = sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
                $this->model,
                urlencode($this->apiKey)
            );

            $response = Http::timeout(20)
                ->acceptJson()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $payload);

            if (!$response->ok()) {
                Log::warning('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
                return 'Sorry, I could not generate a response right now.';
            }

            $data = $response->json();
            // Parse Gemini response structure
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if (!$text) {
                return 'Sorry, I could not find an answer in the current context.';
            }
            // Replace any Blade-like config placeholders the model might echo back
            $text = preg_replace_callback(
                '/\{\{\s*config\(\s*[\"\']mail\\.from\\.address[\"\']\s*\)\s*\}\}/',
                function () {
                    return (string) config('mail.from.address', '');
                },
                $text
            );
            return trim($text);
        } catch (\Throwable $e) {
            Log::error('Gemini API exception', ['error' => $e->getMessage()]);
            return 'An error occurred while processing your request.';
        }
    }

    protected function cleanBladeToPlainText(string $blade): string
    {
        // Remove Blade directives like @section, @if, etc.
        $text = preg_replace('/@[a-zA-Z_]+\([^)]*\)/', ' ', $blade);
        $text = preg_replace('/@[a-zA-Z_]+/', ' ', $text);
        // Remove script/style blocks
        $text = preg_replace('/<script[\s\S]*?<\/script>/i', ' ', $text);
        $text = preg_replace('/<style[\s\S]*?<\/style>/i', ' ', $text);
        // Convert breaks and list items to newlines for readability
        $text = preg_replace('/<(br|li|p)[^>]*>/i', "\n", $text);
        // Strip remaining tags
        $text = strip_tags($text);
        // Normalize whitespace
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[\t ]{2,}/', ' ', $text);
        return trim($text);
    }

    protected function buildSystemPrompt(): string
    {
        return trim(<<<PROMPT
You are RCS Assistant, the in-app assistant for the RCS Laravel application. Answer only about this application. Use exact UI labels and flows that match the app. Prefer instructions that align with the provided Help page content. If a detail is not present, say you are not certain and suggest opening the in-app Help at /help.

Formatting Requirements:
1. Write in a clean, professional style with short sections that have clear headings (e.g., "Overview", "Steps", "Notes").
2. Use dotted numbering for procedures (e.g., "1.", "2."). For optional sub-steps use dotted decimals (e.g., "1.1", "1.2"). Do not use asterisks (*) or hyphens (-) anywhere in the response.
3. Keep paragraphs short and scannable. Avoid long walls of text.
4. Include precise page or menu names when useful (e.g., Instructions → Create; Profile → Edit).
5. Do not use Markdown headings (#, ##, ###) or bold markers (**). Use plain section labels followed by a colon (e.g., "Overview:") and rely on the client to style them. If emphasis is required, keep it minimal and inline without **.

 Images:
 - If relevant image URLs are provided in context, reference them by emitting tokens in the form: [IMG src="<absolute_url>" alt="..."] on their own lines under an "Images:" section. Do not embed raw HTML or Markdown; use only these IMG tokens.

Content Rules:
- For “How to create instructions?”, explain the path (Instructions → Create), typical fields, and how to send.
- For Telegram and notifications, mention settings in Profile and Users edit pages where applicable.
- Do not invent features. If unsure based on context, say so and refer to /help.
PROMPT);
    }

    /**
     * Extract image URLs and alt text from rendered HTML, returning absolute URLs.
     *
     * @param string $html
     * @return array<int, array{url:string, alt:string}>
     */
    protected function extractImageUrls(string $html): array
    {
        $results = [];
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $idx => $src) {
                $alt = '';
                if (preg_match('/<img[^>]*src=["\']' . preg_quote($src, '/') . '["\'][^>]*alt=["\']([^"\']*)["\'][^>]*>/i', $html, $am)) {
                    $alt = $am[1] ?? '';
                }
                // Make absolute URL
                if (str_starts_with($src, '//')) {
                    $src = (request()->isSecure() ? 'https:' : 'http:') . $src;
                } elseif (str_starts_with($src, '/')) {
                    $src = url($src);
                } elseif (!preg_match('/^https?:\/\//i', $src)) {
                    $src = url('/' . ltrim($src, '/'));
                }
                $results[] = ['url' => $src, 'alt' => $alt];
            }
        }
        return $results;
    }
}
