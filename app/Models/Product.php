<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;

/**
 * @property int $id
 */

#[Fillable(['id', 'category_id', 'name', 'description', 'price', 'stock', 'old_price'])]
class Product extends Model
{
    use HasFactory;



    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images():HasMany{
        return $this->hasMany(ProductImage::class);
    }

    public function scopeFilters(Builder $builder, Request $request): Builder
    {
        return $builder
            ->when($request->search, function (Builder $builder) use ($request) {
                $builder->where('name', 'like', "%{$request->search}%")
                    ->orWhere('description', 'like', "%{$request->search}%");
            })
            ->when($request->category_id, function (Builder $builder) use ($request) {
                $builder->where('category_id', $request->input('category_id') );
            })
            ->when($request->stock, function (Builder $builder) use ($request) {
                $builder->where('stock', (int) $request->input('stock') );
            });
    }
}
