<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokenController extends Controller
{
    /**
     * Halaman info token & history transaksi user
     */
    public function index()
    {
        $user         = Auth::user();
        $transactions = $user->tokenTransactions()->paginate(20);

        return view('tokens.index', compact('user', 'transactions'));
    }

    /**
     * Cek status token via API (dipanggil frontend secara polling)
     */
    public function status()
    {
        $user = Auth::user()->fresh();

        return response()->json([
            'token_balance'    => $user->token_balance,
            'can_refill'       => $user->canRefill(),
            'refill_countdown' => $user->refillCountdown(),
            'plan'             => $user->plan,
            'is_premium'       => $user->isPremium(),
        ]);
    }

    /**
     * Manual refill (jika sudah waktunya)
     * Untuk user free: hanya bisa refill jika sudah 24 jam
     */
    public function refill()
    {
        $user = Auth::user();

        if (!$user->canRefill()) {
            return response()->json([
                'success'          => false,
                'error'            => 'Belum bisa refill. Coba lagi dalam ' . $user->refillCountdown(),
                'refill_countdown' => $user->refillCountdown(),
            ], 429);
        }

        $transaction = $user->performRefill();

        return response()->json([
            'success'          => true,
            'message'          => 'Token berhasil diisi ulang!',
            'token_balance'    => $user->fresh()->token_balance,
            'refill_countdown' => $user->fresh()->refillCountdown(),
        ]);
    }
}


// ─────────────────────────────────────────────────────────────────────────────
// GeminiService — Wrapper untuk Gemini Nano API (generate desain)
// ─────────────────────────────────────────────────────────────────────────────


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

    public function __construct()
    {
        $this->apiKey  = config('services.gemini.api_key');
        $this->baseUrl = config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta');
        $this->model   = config('services.gemini.model', 'gemini-2.0-flash-exp'); // gemini nano / flash
    }

    /**
     * Generate gambar desain packaging dari prompt
     *
     * @throws \Exception
     */
    public function generatePackagingDesign(AiGenerationJob $job): string
    {
        $prompt = $this->buildPrompt($job);

        Log::info('GeminiService: Generating design', ['job_id' => $job->id, 'prompt' => $prompt]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(120)->post(
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
            throw new \Exception('Gemini API error: ' . $response->body());
        }

        $data = $response->json();

        // Ekstrak base64 image dari response
        $imagePart = collect($data['candidates'][0]['content']['parts'] ?? [])
            ->firstWhere('inlineData');

        if (!$imagePart) {
            throw new \Exception('Gemini tidak menghasilkan gambar. Coba ubah prompt.');
        }

        $imageBase64 = $imagePart['inlineData']['data'];
        $mimeType    = $imagePart['inlineData']['mimeType'] ?? 'image/png';
        $extension   = str_replace('image/', '', $mimeType);

        // Simpan ke storage
        $path = 'ai-generated/' . $job->user_id . '/' . $job->id . '.' . $extension;
        Storage::disk('public')->put($path, base64_decode($imageBase64));

        return $path;
    }

    // ─── Build Prompt ─────────────────────────────────────────────────────────

    private function buildPrompt(AiGenerationJob $job): string
    {
        $parts = [
            'Create a professional packaging design label/artwork with the following description:',
            '',
            'Description: ' . $job->prompt,
        ];

        if ($job->style) {
            $parts[] = 'Design style: ' . $job->style;
        }

        if ($job->color_palette) {
            $parts[] = 'Color palette: ' . $job->color_palette;
        }

        if ($job->target_audience) {
            $parts[] = 'Target audience: ' . $job->target_audience;
        }

        $parts = array_merge($parts, [
            '',
            'Requirements:',
            '- High resolution, print-ready design',
            '- Clean transparent or white background',
            '- Suitable for product packaging label',
            '- Include typography appropriate to the style',
            '- Professional and commercially viable design',
            '- Output as a flat 2D artwork suitable for wrapping onto 3D packaging',
        ]);

        return implode("\n", $parts);
    }
}