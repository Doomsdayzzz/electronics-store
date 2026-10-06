<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id', 'product_id', 'path', 'is_main'])]
class ProductImage extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
