@extends('admin.layouts.admin')
@section('page-title', 'Render Jobs')

@section('content')

{{-- Status summary --}}
<div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
    @php
    $statusConfig = [
        'pending'    => ['label'=>'Pending',    'color'=>'var(--warning)', 'bg'=>'rgba(255,165,2,0.1)'],
        'processing' => ['label'=>'Processing', 'color'=>'var(--info)',    'bg'=>'rgba(61,156,245,0.1)'],
        'completed'  => ['label'=>'Completed',  'color'=>'var(--accent)',  'bg'=>'rgba(200,245,66,0.1)'],
        'failed'     => ['label'=>'Failed',     'color'=>'var(--danger)',  'bg'=>'rgba(255,71,87,0.1)'],
    ];
    @endphp
    @foreach($statusConfig as $st => $cfg)
    <a href="{{ route('admin.render-jobs.index', ['status' => $st]) }}"
        style="padding:8px 16px; border-radius:var(--radius-sm); border:1px solid {{ request('status') === $st ? $cfg['color'] : 'var(--border)' }}; background:{{ request('status') === $st ? $cfg['bg'] : 'transparent' }}; text-decoration:none; color:{{ $cfg['color'] }}; font-size:13px; font-weight:600; display:flex; align-items:center; gap:6px;">
        {{ $cfg['label'] }}
        <span style="background:{{ $cfg['bg'] }}; padding:1px 7px; border-radius:99px; font-size:11px;">{{ $statusCounts[$st] ?? 0 }}</span>
    </a>
    @endforeach
    @if(request('status'))
    <a href="{{ route('admin.render-jobs.index') }}" class="btn btn-ghost btn-sm">✕ Reset</a>
    @endif
</div>

{{-- Filters --}}
<form method="GET" class="filters-bar">
    <input type="hidden" name="status" value="{{ request('status') }}">
    <input type="text" name="search" class="form-input" placeholder="Cari nama / email user..." value="{{ request('search') }}" style="min-width:220px;">
    <button type="submit" class="btn btn-primary">Filter</button>
</form>

<div class="table-wrap">
    <table>
        <thead><tr>
            <th>#</th>
            <th>User</th>
            <th>Proyek</th>
            <th>Model Produk</th>
            <th>Status</th>
            <th>Token</th>
            <th>Durasi</th>
            <th>Dibuat</th>
            <th style="text-align:right">Aksi</th>
        </tr></thead>
        <tbody>
        @forelse($jobs as $job)
        @php
        $bc = ['completed'=>'badge-green','pending'=>'badge-yellow','processing'=>'badge-blue','failed'=>'badge-red'];
        $duration = null;
        if ($job->started_at && $job->completed_at) {
            $duration = $job->started_at->diffInSeconds($job->completed_at) . 's';
        }
        @endphp
        <tr>
            <td class="text-sm text-muted">#{{ $job->id }}</td>
            <td>
                @if($job->user)
                <a href="{{ route('admin.users.show', $job->user) }}" style="color:var(--text); text-decoration:none; font-weight:500;">
                    {{ $job->user->name }}
                </a>
                <div class="text-sm text-muted">{{ $job->user->email }}</div>
                @else <span class="text-muted">—</span> @endif
            </td>
            <td style="font-weight:500; font-size:13px;">{{ $job->designProject?->name ?? '—' }}</td>
            <td class="text-sm text-muted">{{ $job->designProject?->productModel?->name ?? '—' }}</td>
            <td><span class="badge {{ $bc[$job->status] ?? 'badge-gray' }}">{{ ucfirst($job->status) }}</span></td>
            <td style="color:var(--warning); font-weight:600; font-size:13px;">{{ $job->tokens_consumed }}</td>
            <td class="text-sm text-muted">{{ $duration ?? '—' }}</td>
            <td class="text-sm text-muted">
                {{ $job->created_at->format('d M H:i') }}
                @if($job->completed_at)
                <div style="color:var(--accent); font-size:11px;">✓ {{ $job->completed_at->format('H:i') }}</div>
                @endif
            </td>
            <td>
                <div style="display:flex; gap:4px; justify-content:flex-end;">
                    <a href="{{ route('admin.render-jobs.show', $job) }}" class="btn btn-ghost btn-xs">Detail</a>
                    @if($job->status === 'failed')
                    <form method="POST" action="{{ route('admin.render-jobs.retry', $job) }}">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-xs">Retry</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.render-jobs.destroy', $job) }}"
                        onsubmit="return confirm('Hapus render job #{{ $job->id }}?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-xs">✕</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center; padding:40px; color:var(--text-muted);">Tidak ada render jobs ditemukan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">{{ $jobs->links() }}</div>

@endsection