@extends('admin.layouts.admin')
@section('page-title', 'Render Job #' . $renderJob->id)

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.render-jobs.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <h2 style="font-size:18px; font-weight:800;">Render Job #{{ $renderJob->id }}</h2>
    @php $bc = ['completed'=>'badge-green','pending'=>'badge-yellow','processing'=>'badge-blue','failed'=>'badge-red']; @endphp
    <span class="badge {{ $bc[$renderJob->status] ?? 'badge-gray' }}" style="font-size:13px; padding:4px 12px;">{{ ucfirst($renderJob->status) }}</span>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

    {{-- Info --}}
    <div class="card">
        <h3 style="font-size:13px; font-weight:700; margin-bottom:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Detail Render Job</h3>
        <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
            <div class="flex items-center justify-between">
                <span class="text-muted">ID</span><span>#{{ $renderJob->id }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">User</span>
                @if($renderJob->user)
                <a href="{{ route('admin.users.show', $renderJob->user) }}" style="color:var(--accent); text-decoration:none;">
                    {{ $renderJob->user->name }}
                </a>
                @else <span>—</span> @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Proyek</span>
                <span>{{ $renderJob->designProject?->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Model Produk</span>
                <span>{{ $renderJob->designProject?->productModel?->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Priority</span>
                <span class="badge badge-premium" style="font-size:11px;">⚡ High</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Token Dikonsumsi</span>
                <span style="color:var(--warning); font-weight:700;">{{ $renderJob->tokens_consumed }}</span>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="card">
        <h3 style="font-size:13px; font-weight:700; margin-bottom:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Timeline</h3>
        <div style="display:flex; flex-direction:column; gap:12px;">
            @php
            $timeline = [
                ['label' => 'Dibuat',   'time' => $renderJob->created_at],
                ['label' => 'Di-queue', 'time' => $renderJob->queued_at],
                ['label' => 'Diproses', 'time' => $renderJob->started_at],
                ['label' => 'Selesai',  'time' => $renderJob->completed_at],
            ];
            @endphp
            @foreach($timeline as $t)
            <div style="display:flex; align-items:center; gap:12px; font-size:13px;">
                <div style="width:8px; height:8px; border-radius:50%; background:{{ $t['time'] ? 'var(--accent)' : 'var(--bg3)' }}; border:1px solid {{ $t['time'] ? 'var(--accent)' : 'var(--border)' }}; flex-shrink:0;"></div>
                <span class="{{ $t['time'] ? '' : 'text-muted' }}" style="width:80px;">{{ $t['label'] }}</span>
                <span class="text-muted">{{ $t['time'] ? $t['time']->format('d M Y H:i:s') : '—' }}</span>
            </div>
            @endforeach
            @if($renderJob->started_at && $renderJob->completed_at)
            <div style="font-size:12px; color:var(--text-muted); padding-left:20px;">
                Durasi: <strong style="color:var(--text);">{{ $renderJob->started_at->diffInSeconds($renderJob->completed_at) }} detik</strong>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Result preview --}}
@if($renderJob->designProject?->render_output_path)
<div class="card mb-4">
    <h3 style="font-size:13px; font-weight:700; margin-bottom:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Hasil Render</h3>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
        <div>
            <div class="text-sm text-muted mb-2">Render Output</div>
            <img src="{{ asset('storage/'.$renderJob->designProject->render_output_path) }}"
                style="width:100%; max-width:400px; border-radius:var(--radius-sm); border:1px solid var(--border);"
                onerror="this.style.display='none'">
            <div class="text-sm text-muted mt-2">{{ $renderJob->output_path }}</div>
        </div>
        @if($renderJob->designProject->render_preview_path)
        <div>
            <div class="text-sm text-muted mb-2">Preview</div>
            <img src="{{ asset('storage/'.$renderJob->designProject->render_preview_path) }}"
                style="width:100%; max-width:400px; border-radius:var(--radius-sm); border:1px solid var(--border);"
                onerror="this.style.display='none'">
        </div>
        @endif
    </div>
</div>
@endif

{{-- Desain flat --}}
@if($renderJob->designProject?->design_file_path)
<div class="card mb-4">
    <h3 style="font-size:13px; font-weight:700; margin-bottom:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Desain Flat (Input)</h3>
    <img src="{{ asset('storage/'.$renderJob->designProject->design_file_path) }}"
        style="max-width:400px; border-radius:var(--radius-sm); border:1px solid var(--border);"
        onerror="this.style.display='none'">
</div>
@endif

{{-- Error --}}
@if($renderJob->error_message)
<div class="alert alert-error mb-4">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
    <div><strong>Error:</strong><br>{{ $renderJob->error_message }}</div>
</div>
@endif

{{-- Actions --}}
<div class="flex gap-2">
    @if($renderJob->status === 'failed')
    <form method="POST" action="{{ route('admin.render-jobs.retry', $renderJob) }}">
        @csrf
        <button type="submit" class="btn btn-warning">↺ Retry Render</button>
    </form>
    @endif
    <form method="POST" action="{{ route('admin.render-jobs.destroy', $renderJob) }}"
        onsubmit="return confirm('Hapus render job ini?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">Hapus Job</button>
    </form>
</div>

@endsection