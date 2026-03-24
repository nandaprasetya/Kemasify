<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DesignProject extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'product_model_id', 'name', 'slug',
        'status', 'canvas_data', 'design_file_path',
        'design_source', 'render_preview_path', 'render_output_path',
        'settings', 'is_public',
    ];

    protected $casts = [
        'settings'   => 'array',
        'is_public'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productModel()
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function aiGenerationJobs()
    {
        return $this->hasMany(AiGenerationJob::class);
    }

    public function latestAiJob()
    {
        return $this->hasOne(AiGenerationJob::class)->latestOfMany();
    }

    public function renderJobs()
    {
        return $this->hasMany(RenderJob::class);
    }

    public function latestRenderJob()
    {
        return $this->hasOne(RenderJob::class)->latestOfMany();
    }

    protected static function booted(): void
    {
        static::creating(function (self $project) {
            $project->slug = Str::slug($project->name) . '-' . Str::random(6);
        });
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function isRendering(): bool { return $this->status === 'rendering'; }
    public function isDraft(): bool     { return $this->status === 'draft'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function markAsRendering(): void
    {
        $this->update(['status' => 'rendering']);
    }

    public function markAsCompleted(string $renderPath, string $previewPath): void
    {
        $this->update([
            'status'              => 'completed',
            'render_output_path'  => $renderPath,
            'render_preview_path' => $previewPath,
        ]);
    }

    public function markAsFailed(): void
    {
        $this->update(['status' => 'failed']);
    }

    public function getDesignUrlAttribute(): ?string
    {
        return $this->design_file_path ? asset('storage/' . $this->design_file_path) : null;
    }

    public function getRenderUrlAttribute(): ?string
    {
        return $this->render_output_path ? asset('storage/' . $this->render_output_path) : null;
    }
}