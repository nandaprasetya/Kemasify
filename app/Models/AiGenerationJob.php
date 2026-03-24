<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AiGenerationJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'design_project_id',
        'prompt', 'style', 'color_palette', 'target_audience', 'additional_params',
        'priority', 'queue_position', 'queued_at', 'started_at', 'completed_at',
        'status', 'result_image_path', 'gemini_response', 'error_message',
        'tokens_consumed',
    ];

    protected $casts = [
        'additional_params' => 'array',
        'gemini_response'   => 'array',
        'queued_at'         => 'datetime',
        'started_at'        => 'datetime',
        'completed_at'      => 'datetime',
    ];

    const TOKEN_COST = 10;

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
                     ->orderBy('priority', 'desc')
                     ->orderBy('queued_at');
    }

    public function scopeByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'high', 'normal')")
                     ->orderBy('queued_at');
    }

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isProcessing(): bool { return $this->status === 'processing'; }
    public function isCompleted(): bool  { return $this->status === 'completed'; }
    public function isFailed(): bool     { return $this->status === 'failed'; }

    public function markAsProcessing(): void
    {
        $this->update(['status' => 'processing', 'started_at' => now()]);
    }

    public function markAsCompleted(string $imagePath, array $geminiResponse = []): void
    {
        $this->update([
            'status'            => 'completed',
            'completed_at'      => now(),
            'result_image_path' => $imagePath,
            'gemini_response'   => $geminiResponse,
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
        return $this->result_image_path ? asset('storage/' . $this->result_image_path) : null;
    }
}