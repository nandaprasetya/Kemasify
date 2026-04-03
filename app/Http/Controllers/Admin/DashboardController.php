<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiGenerationJob;
use App\Models\DesignProject;
use App\Models\ProductModel;
use App\Models\RenderJob;
use App\Models\TokenTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // ── Stats utama ───────────────────────────────────────────────────────
        $stats = [
            'total_users'      => User::count(),
            'premium_users'    => User::where('plan', 'premium')->count(),
            'free_users'       => User::where('plan', 'free')->count(),
            'total_projects'   => DesignProject::count(),
            'total_ai_jobs'    => AiGenerationJob::count(),
            'total_renders'    => RenderJob::count(),
            'pending_ai_jobs'  => AiGenerationJob::where('status', 'pending')->count(),
            'pending_renders'  => RenderJob::where('status', 'pending')->count(),
            'total_tokens_consumed' => TokenTransaction::where('amount', '<', 0)->sum(DB::raw('ABS(amount)')),
            // Revenue
            'total_revenue'    => \App\Models\Order::where('status', 'paid')->sum('amount'),
            'pending_orders'   => \App\Models\Order::where('status', 'pending')->count(),
            'paid_orders_today'=> \App\Models\Order::where('status', 'paid')->whereDate('paid_at', today())->count(),
        ];

        // ── Registrasi 7 hari terakhir ────────────────────────────────────────
        $newUsersChart = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        // Isi hari yang kosong
        $chartDates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartDates[$date] = $newUsersChart[$date] ?? 0;
        }

        // ── AI Jobs per status ─────────────────────────────────────────────────
        $aiJobStats = AiGenerationJob::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Aktivitas terbaru ─────────────────────────────────────────────────
        $recentUsers    = User::latest()->take(5)->get();
        $recentAiJobs   = AiGenerationJob::with('user', 'designProject')->latest()->take(8)->get();
        $recentRenders  = RenderJob::with('user', 'designProject')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'stats', 'chartDates', 'aiJobStats',
            'recentUsers', 'recentAiJobs', 'recentRenders'
        ));
    }
}