<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'document_id',
        'name',
        'code',
        'description',
        'price',
        'backend_id',
        'stock',
        'pack_price',
        'image',
        'published_at',
        'created_by_id',
        'updated_by_id',
        'locale',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'products_category_lnk',
            'product_code',
            'category_id',
            'code',
            'id',
        );
    }
}
