<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGenerationJob;
use App\Models\RenderJob;
use Illuminate\Http\Request;

class AiJobController extends Controller
{
    public function index(Request $request)
    {
        $query = AiGenerationJob::with(['user', 'designProject']);

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
            );
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $jobs = $query->latest()->paginate(20)->withQueryString();

        $statusCounts = AiGenerationJob::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        return view('admin.ai-jobs.index', compact('jobs', 'statusCounts'));
    }

    public function show(AiGenerationJob $aiJob)
    {
        $aiJob->load(['user', 'designProject.productModel']);
        return view('admin.ai-jobs.show', compact('aiJob'));
    }

    public function retry(AiGenerationJob $aiJob)
    {
        if (!in_array($aiJob->status, ['failed', 'cancelled'])) {
            return back()->with('error', 'Hanya job yang gagal/dibatalkan yang bisa di-retry.');
        }

        $aiJob->update([
            'status'        => 'pending',
            'error_message' => null,
            'started_at'    => null,
            'completed_at'  => null,
        ]);

        \App\Jobs\ProcessAiGenerationJob::dispatch($aiJob)
            ->onQueue($aiJob->priority === 'high' ? 'high' : 'default');

        return back()->with('success', 'Job #' . $aiJob->id . ' berhasil di-retry.');
    }

    public function destroy(AiGenerationJob $aiJob)
    {
        // Refund token jika masih pending/processing
        if (in_array($aiJob->status, ['pending', 'processing'])) {
            $aiJob->user->addTokens(
                amount: $aiJob->tokens_consumed,
                type: 'refund',
                description: 'Refund - job dihapus oleh admin'
            );
        }

        $aiJob->delete();
        return redirect()->route('admin.ai-jobs.index')
            ->with('success', 'AI Job #' . $aiJob->id . ' berhasil dihapus.');
    }
}