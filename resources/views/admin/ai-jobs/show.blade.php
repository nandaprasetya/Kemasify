@extends('admin.layouts.admin')
@section('page-title', 'AI Job #' . $aiJob->id)

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.ai-jobs.index') }}" class="btn btn-ghost btn-sm">← Kembali</a>
    <h2 style="font-size:18px; font-weight:800;">AI Job #{{ $aiJob->id }}</h2>
    @php $bc = ['completed'=>'badge-green','pending'=>'badge-yellow','processing'=>'badge-blue','failed'=>'badge-red','cancelled'=>'badge-gray']; @endphp
    <span class="badge {{ $bc[$aiJob->status] ?? 'badge-gray' }}" style="font-size:13px; padding:4px 12px;">{{ ucfirst($aiJob->status) }}</span>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">

    {{-- Info --}}
    <div class="card">
        <h3 style="font-size:13px; font-weight:700; margin-bottom:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Detail Job</h3>
        <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
            <div class="flex items-center justify-between">
                <span class="text-muted">ID</span><span>#{{ $aiJob->id }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">User</span>
                @if($aiJob->user)
                <a href="{{ route('admin.users.show', $aiJob->user) }}" style="color:var(--accent); text-decoration:none;">{{ $aiJob->user->name }}</a>
                @else <span>—</span> @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Proyek</span>
                <span>{{ $aiJob->designProject?->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Model Produk</span>
                <span>{{ $aiJob->designProject?->productModel?->name ?? '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Priority</span>
                @if($aiJob->priority === 'high')
                    <span class="badge badge-premium" style="font-size:11px;">⚡ High</span>
                @else
                    <span class="badge badge-gray">Normal</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Posisi Antrian</span>
                <span>{{ $aiJob->queue_position ? '#'.$aiJob->queue_position : '—' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-muted">Token Dikonsumsi</span>
                <span style="color:var(--warning); font-weight:600;">{{ $aiJob->tokens_consumed }}</span>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="card">
        <h3 style="font-size:13px; font-weight:700; margin-bottom:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Timeline</h3>
        <div style="display:flex; flex-direction:column; gap:12px;">
            @php
            $timeline = [
                ['label' => 'Dibuat',    'time' => $aiJob->created_at],
                ['label' => 'Di-queue',  'time' => $aiJob->queued_at],
                ['label' => 'Diproses',  'time' => $aiJob->started_at],
                ['label' => 'Selesai',   'time' => $aiJob->completed_at],
            ];
            @endphp
            @foreach($timeline as $t)
            <div style="display:flex; align-items:center; gap:12px; font-size:13px;">
                <div style="width:8px; height:8px; border-radius:50%; background:{{ $t['time'] ? 'var(--accent)' : 'var(--bg3)' }}; border:1px solid {{ $t['time'] ? 'var(--accent)' : 'var(--border)' }}; flex-shrink:0;"></div>
                <span class="{{ $t['time'] ? '' : 'text-muted' }}" style="width:80px;">{{ $t['label'] }}</span>
                <span class="text-muted">{{ $t['time'] ? $t['time']->format('d M Y H:i:s') : '—' }}</span>
            </div>
            @endforeach

            @if($aiJob->started_at && $aiJob->completed_at)
            <div style="font-size:12px; color:var(--text-muted); padding-left:20px;">
                Durasi proses: <strong style="color:var(--text);">{{ $aiJob->started_at->diffInSeconds($aiJob->completed_at) }}s</strong>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Prompt --}}
<div class="card mb-4">
    <h3 style="font-size:13px; font-weight:700; margin-bottom:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Prompt & Parameter</h3>
    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:16px;">
        <div>
            <div class="text-sm text-muted mb-1">Style</div>
            <div style="font-size:13px;">{{ $aiJob->style ? ucfirst($aiJob->style) : '—' }}</div>
        </div>
        <div>
            <div class="text-sm text-muted mb-1">Color Palette</div>
            <div style="font-size:13px;">{{ $aiJob->color_palette ?: '—' }}</div>
        </div>
        <div>
            <div class="text-sm text-muted mb-1">Target Audience</div>
            <div style="font-size:13px;">{{ $aiJob->target_audience ?: '—' }}</div>
        </div>
    </div>
    <div>
        <div class="text-sm text-muted mb-2">Prompt</div>
        <div style="background:var(--bg3); border:1px solid var(--border); border-radius:var(--radius-sm); padding:14px; font-size:13px; line-height:1.7; white-space:pre-wrap;">{{ $aiJob->prompt }}</div>
    </div>
</div>

{{-- Result --}}
@if($aiJob->result_image_path)
<div class="card mb-4">
    <h3 style="font-size:13px; font-weight:700; margin-bottom:14px; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.07em;">Hasil Generate</h3>
    <img src="{{ asset('storage/'.$aiJob->result_image_path) }}"
        style="max-width:500px; border-radius:var(--radius-sm); border:1px solid var(--border);"
        alt="Generated design">
    <div class="text-sm text-muted mt-2">Path: {{ $aiJob->result_image_path }}</div>
</div>
@endif

{{-- Error --}}
@if($aiJob->error_message)
<div class="alert alert-error mb-4">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
    <div>
        <strong>Error Message:</strong><br>
        {{ $aiJob->error_message }}
    </div>
</div>
@endif

{{-- Actions --}}
<div class="flex gap-2">
    @if(in_array($aiJob->status, ['failed', 'cancelled']))
    <form method="POST" action="{{ route('admin.ai-jobs.retry', $aiJob) }}">
        @csrf
        <button type="submit" class="btn btn-warning">↺ Retry Job</button>
    </form>
    @endif
    <form method="POST" action="{{ route('admin.ai-jobs.destroy', $aiJob) }}"
        onsubmit="return confirm('Hapus job ini? Token akan di-refund jika masih pending.')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-danger">Hapus Job</button>
    </form>
</div>

@endsection