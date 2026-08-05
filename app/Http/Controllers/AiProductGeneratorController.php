<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AiProductGeneratorController extends Controller
{
    public function index(Request $request): View
    {
        if (!$request->session()->get('data_auth', false)) {
            return view('data', [
                'authenticated' => false,
                'error' => session('data_login_error'),
            ]);
        }

        return view('ai-product-generator');
    }

    public function generate(Request $request): JsonResponse
    {
        if (!$request->session()->get('data_auth', false)) {
            return response()->json(['message' => 'Unauthorized. Please login again.'], 403);
        }

        $data = $request->validate([
            'product_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'product_image.required' => 'Please upload a product image.',
            'product_image.image' => 'The uploaded file must be a valid image.',
            'product_image.mimes' => 'Only JPG, JPEG, PNG, and WEBP images are allowed.',
            'product_image.max' => 'The image must not be larger than 5MB.',
        ]);

        $apiKey = (string) config('services.gemini.key');
        if ($apiKey === '') {
            return response()->json(['message' => 'Gemini API key is not configured. Add GEMINI_API_KEY in .env.'], 500);
        }

        $image = $data['product_image'];
        if (!$image->isValid()) {
            return response()->json([
                'message' => 'The image could not be uploaded. Please use a JPG, PNG, or WEBP image under 5MB.',
            ], 422);
        }

        $base64Image = base64_encode((string) file_get_contents($image->getRealPath()));
        $mimeType = $image->getMimeType() ?: 'image/jpeg';
        $models = array_values(array_unique(array_filter([
            (string) config('services.gemini.model', 'gemini-2.5-flash'),
            ...((array) config('services.gemini.fallback_models', [])),
        ])));

        $prompt = <<<'PROMPT'
Analyze this product image and create product listing content.
Return ONLY valid JSON:
{
  "title": "",
  "short_description": "",
  "description": "",
  "category": "",
  "features": [],
  "tags": []
}

Rules:
- Create SEO-friendly product title.
- Description should be simple and attractive.
- Do not guess brand unless visible.
- Do not guess price, weight, size, or warranty.
- Keep title under 80 characters.
- Keep description under 120 words.
PROMPT;

        $response = null;
        $lastStatus = null;
        $lastErrorMessage = 'Gemini API request was rejected.';
        $attempts = [];

        foreach ($models as $model) {
            try {
                $response = Http::timeout(45)
                    ->retry(1, 700)
                    ->withHeaders([
                        'x-goog-api-key' => $apiKey,
                    ])
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                        'contents' => [[
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $base64Image,
                                    ],
                                ],
                            ],
                        ]],
                        'generationConfig' => [
                            'temperature' => 0.4,
                            'response_mime_type' => 'application/json',
                        ],
                    ]);
            } catch (\Throwable $exception) {
                $attempts[] = [
                    'model' => $model,
                    'status' => null,
                    'error' => $exception->getMessage(),
                    'response' => null,
                ];

                Log::warning('Gemini product generator request failed.', [
                    'model' => $model,
                    'message' => $exception->getMessage(),
                ]);

                $lastErrorMessage = 'Gemini API request failed. Please try again.';
                continue;
            }

            if ($response->successful()) {
                break;
            }

            $lastStatus = $response->status();
            $lastErrorMessage = (string) data_get($response->json(), 'error.message', 'Gemini API request was rejected.');
            $attempts[] = [
                'model' => $model,
                'status' => $lastStatus,
                'error' => $lastErrorMessage,
                'response' => $response->json() ?: $response->body(),
            ];

            Log::warning('Gemini product generator API error.', [
                'model' => $model,
                'status' => $lastStatus,
                'error' => $lastErrorMessage,
            ]);

            if (!in_array($lastStatus, [404, 429, 500, 502, 503, 504], true)) {
                break;
            }
        }

        if (!$response || !$response->successful()) {
            return response()->json([
                'message' => "Gemini API error" . ($lastStatus ? " ({$lastStatus})" : '') . ": {$lastErrorMessage}",
                'status' => $lastStatus,
                'gemini_debug' => [
                    'attempted_models' => $attempts,
                ],
            ], 502);
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        if (!is_string($text) || trim($text) === '') {
            return response()->json(['message' => 'Gemini returned an empty response.'], 502);
        }

        $decoded = $this->decodeJsonResponse($text);
        if ($decoded === null) {
            return response()->json(['message' => 'Gemini returned invalid JSON. Please regenerate.'], 422);
        }

        return response()->json([
            'result' => [
                'title' => (string) ($decoded['title'] ?? ''),
                'short_description' => (string) ($decoded['short_description'] ?? ''),
                'description' => (string) ($decoded['description'] ?? ''),
                'category' => (string) ($decoded['category'] ?? ''),
                'features' => array_values(array_filter((array) ($decoded['features'] ?? []), 'is_string')),
                'tags' => array_values(array_filter((array) ($decoded['tags'] ?? []), 'is_string')),
            ],
        ]);
    }

    private function decodeJsonResponse(string $text): ?array
    {
        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\s*```$/', '', $clean) ?? $clean;

        $decoded = json_decode($clean, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $clean, $matches) !== 1) {
            return null;
        }

        $decoded = json_decode($matches[0], true);

        return is_array($decoded) ? $decoded : null;
    }
}
