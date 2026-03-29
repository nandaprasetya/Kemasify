<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RenderJob;
use Illuminate\Http\Request;

class RenderJobController extends Controller
{
    public function index(Request $request)
    {
        $query = RenderJob::with(['user', 'designProject']);

        if ($request->filled('search')) {
            $query->whereHas('user', fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
            );
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $jobs = $query->latest()->paginate(20)->withQueryString();

        $statusCounts = RenderJob::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        return view('admin.render-jobs.index', compact('jobs', 'statusCounts'));
    }

    public function show(RenderJob $renderJob)
    {
        $renderJob->load(['user', 'designProject.productModel']);
        return view('admin.render-jobs.show', compact('renderJob'));
    }

    public function retry(RenderJob $renderJob)
    {
        if ($renderJob->status !== 'failed') {
            return back()->with('error', 'Hanya render job yang gagal yang bisa di-retry.');
        }

        $renderJob->update([
            'status'        => 'pending',
            'error_message' => null,
            'started_at'    => null,
            'completed_at'  => null,
        ]);
        $renderJob->designProject?->update(['status' => 'rendering']);

        \App\Jobs\ProcessRenderJob::dispatch($renderJob)->onQueue('high');

        return back()->with('success', 'Render job #' . $renderJob->id . ' berhasil di-retry.');
    }

    public function destroy(RenderJob $renderJob)
    {
        if (in_array($renderJob->status, ['pending', 'processing'])) {
            $renderJob->user->addTokens(
                amount: $renderJob->tokens_consumed,
                type: 'refund',
                description: 'Refund - render job dihapus oleh admin'
            );
        }

        $renderJob->delete();
        return redirect()->route('admin.render-jobs.index')
            ->with('success', 'Render Job #' . $renderJob->id . ' berhasil dihapus.');
    }
}