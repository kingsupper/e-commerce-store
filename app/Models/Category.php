<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // التصنيف الأب
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // التصنيفات الفرعية
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // منتجات التصنيف
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
