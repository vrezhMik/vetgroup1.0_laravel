<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VetgroupUser extends Model
{
    protected $fillable = [
        'username',
        'email',
        'password',
        'company',
        'first_name',
        'last_name',
        'location',
        'code',
        'user_id',
    ];

    protected $hidden = [
        'password',
    ];
    protected $casts = [
        'password' => 'hashed',
    ];
}
