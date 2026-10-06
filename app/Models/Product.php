<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['id', 'category_id', 'name', 'description', 'price', 'stock', 'old_price'])]
class Product extends Model
{
    use HasFactory;

    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
