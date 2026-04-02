<?php

namespace App\Http\Controllers;

use App\Models\AiGenerationJob;
use App\Models\DesignProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GeminiProxyController extends Controller
{
    /**
     * Proxy langsung ke Gemini API — tanpa queue, tanpa job processing.
     * Dipanggil dari JS editor, response langsung balik gambar base64.
     */
    public function generate(Request $request, string $slug)
    {
        $request->validate([
            'prompt'          => ['required', 'string', 'min:5', 'max:500'],
            'style'           => ['nullable', 'string'],
            'color_palette'   => ['nullable', 'string', 'max:100'],
            'target_audience' => ['nullable', 'string', 'max:100'],
        ]);

        $user    = Auth::user();
        $project = DesignProject::where('slug', $slug)
            ->where('user_id', $user->id)
            ->first();

        if (!$project) {
            return response()->json(['success' => false, 'error' => 'Proyek tidak ditemukan.'], 404);
        }

        // ── Cek token ─────────────────────────────────────────────────────────
        $cost = 10;
        if (!$user->hasEnoughTokens($cost)) {
            return response()->json([
                'success'       => false,
                'error'         => 'Token tidak cukup. Saldo: ' . $user->token_balance . ', dibutuhkan: ' . $cost,
                'token_balance' => $user->token_balance,
            ], 402);
        }

        // ── Build prompt ──────────────────────────────────────────────────────
        $prompt = $this->buildPrompt(
            $request->input('prompt'),
            $request->input('style'),
            $request->input('color_palette'),
            $request->input('target_audience'),
        );

        // ── Call Gemini API ───────────────────────────────────────────────────
        $apiKey  = config('services.gemini.api_key');
        $model   = config('services.gemini.model', 'gemini-2.0-flash-preview-image-generation');
        $baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'error'   => 'GEMINI_API_KEY belum diset di .env',
            ], 500);
        }

        try {
            Log::info('GeminiProxy: calling API', ['project' => $slug, 'model' => $model]);

            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])
                ->timeout(120)
                ->post("{$baseUrl}/models/{$model}:generateContent", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]);

            if ($response->failed()) {
                $errBody = $response->json();
                $errMsg  = $errBody['error']['message'] ?? $response->body();
                Log::error('GeminiProxy: API error', ['status' => $response->status(), 'error' => $errMsg]);
                return response()->json([
                    'success' => false,
                    'error'   => 'Gemini API error: ' . $errMsg,
                ], 500);
            }

            $data  = $response->json();
            $parts = $data['candidates'][0]['content']['parts'] ?? [];

            // Cari bagian image
            $imagePart = null;
            foreach ($parts as $part) {
                if (isset($part['inlineData'])) {
                    $imagePart = $part;
                    break;
                }
            }

            if (!$imagePart) {
                // Cek apakah ada text response (biasanya berisi penolakan)
                $textPart = null;
                foreach ($parts as $part) {
                    if (isset($part['text'])) { $textPart = $part; break; }
                }
                $reason = $textPart['text'] ?? 'Gemini tidak menghasilkan gambar. Coba ubah prompt.';
                return response()->json(['success' => false, 'error' => $reason], 422);
            }

            $imageBase64 = $imagePart['inlineData']['data'];
            $mimeType    = $imagePart['inlineData']['mimeType'] ?? 'image/png';
            $extension   = match($mimeType) {
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
                default      => 'png',
            };

            // ── Simpan ke storage ─────────────────────────────────────────────
            $path = 'ai-generated/' . $user->id . '/' . $project->id . '-' . time() . '.' . $extension;
            Storage::disk('public')->put($path, base64_decode($imageBase64));

            // ── Update project ────────────────────────────────────────────────
            $project->update([
                'design_file_path' => $path,
                'design_source'    => 'ai_generated',
            ]);

            // ── Konsumsi token ────────────────────────────────────────────────
            $user->consumeTokens(
                amount:      $cost,
                type:        'ai_generate',
                description: 'Generate AI desain: ' . $project->name,
            );

            // ── Catat AI job sebagai completed ────────────────────────────────
            AiGenerationJob::create([
                'user_id'             => $user->id,
                'design_project_id'   => $project->id,
                'prompt'              => $request->input('prompt'),
                'style'               => $request->input('style'),
                'color_palette'       => $request->input('color_palette'),
                'target_audience'     => $request->input('target_audience'),
                'priority'            => $user->isPremium() ? 'high' : 'normal',
                'status'              => 'completed',
                'result_image_path'   => $path,
                'tokens_consumed'     => $cost,
                'queued_at'           => now(),
                'started_at'          => now(),
                'completed_at'        => now(),
            ]);

            $freshUser = $user->fresh();

            Log::info('GeminiProxy: success', ['path' => $path]);

            return response()->json([
                'success'       => true,
                'image_url'     => asset('storage/' . $path),
                'image_base64'  => $imageBase64,
                'mime_type'     => $mimeType,
                'token_balance' => $freshUser->token_balance,
                'message'       => 'Desain berhasil dibuat!',
            ]);

        } catch (\Exception $e) {
            Log::error('GeminiProxy: exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Build Prompt ─────────────────────────────────────────────────────────

    private function buildPrompt(string $prompt, ?string $style, ?string $color, ?string $audience): string
    {
        $styleDescriptions = [
            'minimalist' => 'Clean, minimal, lots of whitespace, simple elegant typography',
            'bold'       => 'Strong vibrant colors, heavy typography, high contrast, impactful',
            'elegant'    => 'Refined luxury feel, gold/silver accents, sophisticated serif fonts',
            'playful'    => 'Fun bright colors, rounded shapes, friendly approachable design',
            'modern'     => 'Contemporary geometric, sans-serif fonts, clean structured layout',
            'vintage'    => 'Retro aesthetic, warm muted tones, hand-drawn elements, nostalgic',
            'eco'        => 'Nature-inspired, earthy green tones, organic shapes, sustainability',
        ];

        $lines = [
            'Create a professional product packaging label design artwork.',
            '',
            'Product: ' . $prompt,
        ];

        if ($style && isset($styleDescriptions[$style])) {
            $lines[] = 'Style: ' . ucfirst($style) . ' — ' . $styleDescriptions[$style];
        }
        if ($color) {
            $lines[] = 'Color palette: ' . $color;
        }
        if ($audience) {
            $lines[] = 'Target audience: ' . $audience;
        }

        $lines = array_merge($lines, [
            '',
            'Requirements:',
            '- Flat 2D label artwork, print-ready',
            '- Clean white or transparent background',
            '- Professional commercial quality',
            '- Include product name typography',
            '- Suitable for wrapping onto 3D packaging',
            '- High detail, vibrant colors',
        ]);

        return implode("\n", $lines);
    }
}