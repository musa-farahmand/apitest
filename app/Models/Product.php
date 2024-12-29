<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['name', 'price'];
    public function category(): BelongsTo{
        return $this->belongsTo(ProductCategory::class);
    }
    public function orderItems(): HasMany{
        return $this->hasMany(OrderItem::class);
    }

}
