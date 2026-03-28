<?php

namespace App\Services;

use App\Models\AiGenerationJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;
    private int    $timeout;

    public function __construct()
    {
        $this->apiKey  = config('services.gemini.api_key');
        $this->baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $this->model   = config('services.gemini.model', 'gemini-2.0-flash-exp');
        $this->timeout = config('services.gemini.timeout', 120);
    }

    /**
     * Generate gambar desain packaging dari prompt.
     *
     * @return string  Storage path ke gambar hasil generate
     * @throws \Exception
     */
    public function generatePackagingDesign(AiGenerationJob $job): string
    {
        $prompt = $this->buildPrompt($job);

        Log::info('GeminiService: generating', [
            'job_id' => $job->id,
            'model'  => $this->model,
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout($this->timeout)->post(
            "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}",
            [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseModalities' => ['TEXT', 'IMAGE'],
                    'temperature'        => 0.7,
                ],
            ]
        );

        if ($response->failed()) {
            $body = $response->json();
            $errMsg = $body['error']['message'] ?? $response->body();
            throw new \Exception('Gemini API error: ' . $errMsg);
        }

        $data = $response->json();

        // Simpan raw response untuk debugging
        $job->update(['gemini_response' => $data]);

        // Ekstrak base64 image dari response
        $parts = $data['candidates'][0]['content']['parts'] ?? [];

        $imagePart = collect($parts)->first(fn($p) => isset($p['inlineData']));

        if (!$imagePart) {
            // Cek apakah ada text response (Gemini kadang nolak request)
            $textPart = collect($parts)->first(fn($p) => isset($p['text']));
            $reason   = $textPart['text'] ?? 'Gemini tidak menghasilkan gambar.';
            throw new \Exception($reason . ' Coba ubah atau perjelas promptmu.');
        }

        $imageBase64 = $imagePart['inlineData']['data'];
        $mimeType    = $imagePart['inlineData']['mimeType'] ?? 'image/png';
        $extension   = match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default      => 'png',
        };

        // Simpan ke storage
        $dir  = 'ai-generated/' . $job->user_id;
        $path = $dir . '/' . $job->id . '.' . $extension;

        Storage::disk('public')->put($path, base64_decode($imageBase64));

        Log::info('GeminiService: image saved', ['path' => $path]);

        return $path;
    }

    // ─── Build Prompt ─────────────────────────────────────────────────────────

    private function buildPrompt(AiGenerationJob $job): string
    {
        $parts = [
            'Create a professional packaging design label/artwork with the following specifications:',
            '',
            'Product description: ' . $job->prompt,
        ];

        if ($job->style) {
            $styleDescriptions = [
                'minimalist' => 'Clean, minimal, lots of whitespace, simple typography',
                'bold'       => 'Strong colors, heavy typography, high contrast, impactful',
                'elegant'    => 'Refined, luxury feel, gold/silver accents, sophisticated fonts',
                'playful'    => 'Fun, bright colors, rounded shapes, friendly and approachable',
                'modern'     => 'Contemporary, geometric, sans-serif fonts, clean layout',
                'vintage'    => 'Retro aesthetic, warm tones, hand-drawn elements, nostalgic',
                'eco'        => 'Nature-inspired, earthy tones, organic shapes, sustainability focus',
            ];
            $parts[] = 'Design style: ' . ucfirst($job->style) . ' — ' . ($styleDescriptions[$job->style] ?? '');
        }

        if ($job->color_palette) {
            $parts[] = 'Color palette: ' . $job->color_palette;
        }

        if ($job->target_audience) {
            $parts[] = 'Target audience: ' . $job->target_audience;
        }

        $parts = array_merge($parts, [
            '',
            'Technical requirements:',
            '- High resolution, print-ready flat artwork',
            '- Clean background (white or transparent)',
            '- Suitable as a label/wrap for product packaging',
            '- Include appropriate typography and hierarchy',
            '- Commercially viable and professional appearance',
            '- Output as a flat 2D label/artwork (not a 3D render)',
            '- Aspect ratio: approximately 4:3 or 16:10 for label wrapping',
            '',
            'Generate only the packaging design artwork, no mockup or 3D preview needed.',
        ]);

        return implode("\n", $parts);
    }
}