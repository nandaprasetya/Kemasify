<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RenderJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'design_project_id',
        'render_engine', 'render_settings', 'output_format', 'output_resolution',
        'priority', 'queue_position', 'queued_at', 'started_at', 'completed_at',
        'render_duration_seconds', 'status',
        'result_path', 'preview_path', 'error_message',
        'tokens_consumed',
    ];

    protected $casts = [
        'render_settings' => 'array',
        'queued_at'       => 'datetime',
        'started_at'      => 'datetime',
        'completed_at'    => 'datetime',
    ];

    const TOKEN_COST = 50;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function designProject()
    {
        return $this->belongsTo(DesignProject::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                     ->orderByRaw("FIELD(priority, 'high', 'normal')")
                     ->orderBy('queued_at');
    }

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing', 'started_at' => now()]);
    }

    public function markAsCompleted(string $resultPath, string $previewPath): void
    {
        $duration = $this->started_at ? now()->diffInSeconds($this->started_at) : null;

        $this->update([
            'status'                  => 'completed',
            'completed_at'            => now(),
            'render_duration_seconds' => $duration,
            'result_path'             => $resultPath,
            'preview_path'            => $previewPath,
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status'        => 'failed',
            'completed_at'  => now(),
            'error_message' => $error,
        ]);
    }

    public function getResultUrlAttribute(): ?string
    {
        return $this->result_path ? asset('storage/' . $this->result_path) : null;
    }
}