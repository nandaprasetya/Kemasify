<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // ─── Index ────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = User::withCount(['designProjects', 'aiGenerationJobs', 'renderJobs']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter plan
        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        // Filter admin
        if ($request->filled('role')) {
            $query->where('is_admin', $request->role === 'admin');
        }

        // Sort
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['name', 'email', 'created_at', 'token_balance', 'plan'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    // ─── Show ─────────────────────────────────────────────────────────────────
    public function show(User $user)
    {
        $user->loadCount(['designProjects', 'aiGenerationJobs', 'renderJobs']);
        $transactions = $user->tokenTransactions()->paginate(15);
        $projects     = $user->designProjects()->with('productModel')->latest()->take(10)->get();
        $aiJobs       = $user->aiGenerationJobs()->latest()->take(10)->get();

        return view('admin.users.show', compact('user', 'transactions', 'projects', 'aiJobs'));
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // ─── Update ───────────────────────────────────────────────────────────────
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email,' . $user->id],
            'plan'     => ['required', 'in:free,premium'],
            'is_admin' => ['boolean'],
            'token_balance' => ['required', 'integer', 'min:0', 'max:99999'],
            'plan_expires_at' => ['nullable', 'date', 'after:today'],
            'password' => ['nullable', Password::min(8)->letters()->numbers()],
        ]);

        $updateData = [
            'name'            => $validated['name'],
            'email'           => $validated['email'],
            'plan'            => $validated['plan'],
            'is_admin'        => $request->boolean('is_admin'),
            'token_balance'   => $validated['token_balance'],
            'plan_expires_at' => $validated['plan_expires_at'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User "' . $user->name . '" berhasil diupdate.');
    }

    // ─── Destroy ──────────────────────────────────────────────────────────────
    public function destroy(User $user)
    {
        // Jangan hapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $name = $user->name;
        $user->delete(); // soft delete

        return redirect()->route('admin.users.index')
            ->with('success', 'User "' . $name . '" berhasil dihapus.');
    }

    // ─── Give Tokens ──────────────────────────────────────────────────────────
    public function giveTokens(Request $request, User $user)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:9999'],
            'reason' => ['nullable', 'string', 'max:200'],
        ]);

        app(TokenService::class)->giveBonus(
            $user,
            $request->amount,
            $request->reason ?: 'Bonus dari admin'
        );

        return back()->with('success', $request->amount . ' token berhasil diberikan ke ' . $user->name . '.');
    }

    // ─── Toggle Admin ─────────────────────────────────────────────────────────
    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa mengubah role diri sendiri.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $status = $user->fresh()->is_admin ? 'dijadikan admin' : 'dicabut hak adminnya';
        return back()->with('success', 'User "' . $user->name . '" berhasil ' . $status . '.');
    }

    // ─── Upgrade Premium ─────────────────────────────────────────────────────
    public function upgradePremium(Request $request, User $user)
    {
        $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        app(TokenService::class)->upgradeToPremium($user, $request->days);

        return back()->with('success', $user->name . ' berhasil diupgrade ke Premium selama ' . $request->days . ' hari.');
    }
}