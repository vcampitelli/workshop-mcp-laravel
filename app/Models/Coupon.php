<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount',
        'valid_from',
        'valid_to',
        'usage_limit',
        'discount',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'valid_from' => 'datetime',
            'valid_to'   => 'datetime',
        ];
    }
}
