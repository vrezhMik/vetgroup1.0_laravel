<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'document_id',
        'order_id',
        'vetgroup_user_id',
        'user_id',
        'created',
        'total',
        'products',
        'products_json',
        'complited',
        'published_at',
        'created_by_id',
        'updated_by_id',
        'locale',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created' => 'datetime',
            'products' => 'array',
            'products_json' => 'array',
            'complited' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        // Link orders to VetgroupUser via the vetgroup_user_id column.
        return $this->belongsTo(VetgroupUser::class, 'vetgroup_user_id');
    }
}

