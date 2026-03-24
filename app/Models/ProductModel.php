<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'category', 'description',
        'thumbnail_path', 'model_3d_path',
        'dimensions', 'printable_areas',
        'is_active', 'is_premium', 'sort_order',
    ];

    protected $casts = [
        'dimensions'      => 'array',
        'printable_areas' => 'array',
        'is_active'       => 'boolean',
        'is_premium'      => 'boolean',
    ];

    public function designProjects()
    {
        return $this->hasMany(DesignProject::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeAccessibleBy($query, User $user)
    {
        return $query->when($user->isFree(), fn($q) => $q->where('is_premium', false));
    }

    public function getThumbnailUrlAttribute(): string
    {
        return asset('storage/' . $this->thumbnail_path);
    }
}