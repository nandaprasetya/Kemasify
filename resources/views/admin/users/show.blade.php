@extends('admin.layouts.admin')
@section('page-title', 'Detail User: ' . $user->name)

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <h2 style="font-size:18px; font-weight:800;">{{ $user->name }}</h2>
    @if($user->is_admin) <span class="badge badge-red">Admin</span> @endif
    @if($user->isPremium()) <span class="badge badge-premium">✦ Premium</span> @endif
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;">

    {{-- Info dasar --}}
    <div class="card">
        <h3 style="font-size:13px; font-weight:700; margin-bottom:16px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Informasi Akun</h3>
        <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
            <div class="flex items-center justify-between"><span class="text-muted">ID</span><span>#{{ $user->id }}</span></div>
            <div class="flex items-center justify-between"><span class="text-muted">Nama</span><span style="font-weight:600;">{{ $user->name }}</span></div>
            <div class="flex items-center justify-between"><span class="text-muted">Email</span><span>{{ $user->email }}</span></div>
            <div class="flex items-center justify-between"><span class="text-muted">Plan</span>
                @if($user->isPremium())
                    <span class="badge badge-premium">✦ Premium</span>
                @else
                    <span class="badge badge-gray">Free</span>
                @endif
            </div>
            @if($user->plan_expires_at)
            <div class="flex items-center justify-between"><span class="text-muted">Exp. Plan</span><span>{{ $user->plan_expires_at->format('d M Y') }}</span></div>
            @endif
            <div class="flex items-center justify-between"><span class="text-muted">Bergabung</span><span>{{ $user->created_at->format('d M Y H:i') }}</span></div>
            <div class="flex items-center justify-between"><span class="text-muted">Last Active</span><span>{{ $user->updated_at->diffForHumans() }}</span></div>
        </div>
    </div>

    {{-- Token & Stats --}}
    <div class="card">
        <h3 style="font-size:13px; font-weight:700; margin-bottom:16px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Token & Statistik</h3>
        <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
            <div class="flex items-center justify-between"><span class="text-muted">Token Saldo</span><span style="color:var(--accent); font-weight:700; font-size:20px;">{{ $user->token_balance }}</span></div>
            <div class="flex items-center justify-between"><span class="text-muted">Total Earned</span><span>{{ $user->token_total_earned }}</span></div>
            <div class="flex items-center justify-between"><span class="text-muted">Refill Berikutnya</span>
                <span>{{ $user->canRefill() ? '✓ Sekarang' : $user->refillCountdown() }}</span>
            </div>
            <hr class="divider" style="margin:4px 0;">
            <div class="flex items-center justify-between"><span class="text-muted">Total Proyek</span><span style="font-weight:600;">{{ $user->design_projects_count }}</span></div>
            <div class="flex items-center justify-between"><span class="text-muted">Total AI Jobs</span><span>{{ $user->ai_generation_jobs_count }}</span></div>
            <div class="flex items-center justify-between"><span class="text-muted">Total Render Jobs</span><span>{{ $user->render_jobs_count }}</span></div>
        </div>
    </div>
</div>

{{-- Actions --}}
<div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:24px;">

    {{-- Edit --}}
    <div class="card">
        <h4 style="font-size:13px; font-weight:700; margin-bottom:12px;">Edit Data User</h4>
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-info w-full" style="justify-content:center;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit User
        </a>
    </div>

    {{-- Give Tokens --}}
    <div class="card">
        <h4 style="font-size:13px; font-weight:700; margin-bottom:12px;">Beri Bonus Token</h4>
        <form method="POST" action="{{ route('admin.users.give-tokens', $user) }}">
            @csrf
            <div style="display:flex; gap:8px; margin-bottom:8px;">
                <input type="number" name="amount" class="form-input" placeholder="Jumlah" min="1" max="9999" required style="flex:1;">
            </div>
            <input type="text" name="reason" class="form-input" placeholder="Alasan (opsional)" style="margin-bottom:8px;">
            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">Beri Token</button>
        </form>
    </div>

    {{-- Upgrade Premium --}}
    <div class="card">
        <h4 style="font-size:13px; font-weight:700; margin-bottom:12px;">Upgrade Premium</h4>
        <form method="POST" action="{{ route('admin.users.upgrade-premium', $user) }}">
            @csrf
            <select name="days" class="form-select" style="margin-bottom:8px;">
                <option value="7">7 hari</option>
                <option value="30" selected>30 hari</option>
                <option value="90">90 hari</option>
                <option value="365">1 tahun</option>
            </select>
            <button type="submit" class="btn btn-primary w-full" style="justify-content:center; background:linear-gradient(135deg,#f8b803,#ff6b35); color:#08080a;">
                ✦ Upgrade Premium
            </button>
        </form>

        @if($user->id !== auth()->id())
        <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" style="margin-top:8px;">
            @csrf
            <button type="submit" class="btn {{ $user->is_admin ? 'btn-warning' : 'btn-ghost' }} w-full btn-sm" style="justify-content:center;">
                {{ $user->is_admin ? '✗ Cabut Admin' : '✓ Jadikan Admin' }}
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Proyek terbaru --}}
<div class="mb-6">
    <h3 style="font-size:14px; font-weight:700; margin-bottom:12px;">Proyek Terbaru</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Model</th><th>Status</th><th>Dibuat</th></tr></thead>
            <tbody>
            @forelse($projects as $project)
            <tr>
                <td style="font-weight:500;">{{ $project->name }}</td>
                <td class="text-sm text-muted">{{ $project->productModel?->name ?? '—' }}</td>
                <td>
                    @php $sc = ['draft'=>'badge-gray','completed'=>'badge-green','rendering'=>'badge-yellow','failed'=>'badge-red']; @endphp
                    <span class="badge {{ $sc[$project->status] ?? 'badge-gray' }}">{{ ucfirst($project->status) }}</span>
                </td>
                <td class="text-sm text-muted">{{ $project->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="text-sm text-muted" style="text-align:center; padding:24px;">Belum ada proyek.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Transaksi token --}}
<div>
    <h3 style="font-size:14px; font-weight:700; margin-bottom:12px;">Riwayat Token</h3>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Waktu</th><th>Tipe</th><th>Deskripsi</th><th style="text-align:right">Jumlah</th><th style="text-align:right">Saldo</th></tr></thead>
            <tbody>
            @forelse($transactions as $tx)
            <tr>
                <td class="text-sm text-muted">{{ $tx->created_at->format('d M Y H:i') }}</td>
                <td><span class="badge {{ $tx->amount > 0 ? 'badge-green' : 'badge-gray' }}">{{ $tx->type_label }}</span></td>
                <td class="text-sm">{{ $tx->description ?: '—' }}</td>
                <td style="text-align:right; font-weight:600; color:{{ $tx->amount > 0 ? 'var(--accent)' : 'var(--text)' }}">{{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}</td>
                <td style="text-align:right; font-weight:600;">{{ $tx->balance_after }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-sm text-muted" style="text-align:center; padding:24px;">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:12px;">{{ $transactions->links() }}</div>
</div>

@endsection