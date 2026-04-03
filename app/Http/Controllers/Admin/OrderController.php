<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Order::with('user');

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', fn($u) =>
                      $u->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%')
                  );
            });
        }

        // Filter type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        // ── Summary stats ─────────────────────────────────────────────────────
        $stats = [
            'total_revenue'  => Order::where('status', 'paid')->sum('amount'),
            'total_orders'   => Order::count(),
            'paid_orders'    => Order::where('status', 'paid')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'premium_sales'  => Order::where('status', 'paid')->where('type', 'premium_monthly')->count(),
            'token_sales'    => Order::where('status', 'paid')->where('type', 'token_purchase')->count(),
            'premium_revenue'=> Order::where('status', 'paid')->where('type', 'premium_monthly')->sum('amount'),
            'token_revenue'  => Order::where('status', 'paid')->where('type', 'token_purchase')->sum('amount'),
        ];

        // ── Revenue 7 hari ────────────────────────────────────────────────────
        $revenueChart = Order::where('status', 'paid')
            ->where('paid_at', '>=', now()->subDays(6))
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $chartDates = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartDates[$date] = $revenueChart[$date] ?? 0;
        }

        return view('admin.orders.index', compact('orders', 'stats', 'chartDates'));
    }

    // ─── Show ─────────────────────────────────────────────────────────────────

    public function show(Order $order)
    {
        $order->load('user');
        return view('admin.orders.show', compact('order'));
    }

    // ─── Manual mark as paid ──────────────────────────────────────────────────

    public function markPaid(Order $order)
    {
        if ($order->isPaid()) {
            return back()->with('error', 'Order ini sudah berstatus lunas.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $user = $order->user;

            if ($order->isPremiumOrder()) {
                app(\App\Services\TokenService::class)->upgradeToPremium($user, $order->quantity * 30);
            } elseif ($order->isTokenOrder()) {
                $user->addTokens(
                    amount:      $order->token_amount,
                    type:        'purchase',
                    description: 'Beli token manual (admin) — ' . $order->order_id,
                );
            }
        });

        return back()->with('success', 'Order #' . $order->order_id . ' berhasil ditandai sebagai lunas.');
    }

    // ─── Refund / Cancel ──────────────────────────────────────────────────────

    public function cancel(Order $order)
    {
        if ($order->isPaid()) {
            return back()->with('error', 'Order yang sudah lunas tidak bisa dibatalkan dari sini. Proses refund manual.');
        }

        $order->update(['status' => 'cancelled']);

        return back()->with('success', 'Order #' . $order->order_id . ' berhasil dibatalkan.');
    }
}