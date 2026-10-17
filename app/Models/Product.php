<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

/**
 * @property int $id
 */
#[Fillable(['id', 'category_id', 'name', 'description', 'price', 'stock', 'old_price'])]
class Product extends Model
{
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function scopeFilters(Builder $builder, Request $request): Builder
    {
        return $builder
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $term = '%'.$request->input('search').'%';

                $builder->where(function (Builder $builder) use ($term) {
                    $builder->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->when($request->filled('category_id'), function (Builder $builder) use ($request) {
                $builder->where('category_id', $request->input('category_id'));
            })
            ->when($request->filled('stock'), function (Builder $builder) use ($request) {
                // 1 — «в наличии» (остаток больше нуля), 0 — «нет в наличии»
                $builder->where('stock', $request->boolean('stock') ? '>' : '=', 0);
            });
    }
}
