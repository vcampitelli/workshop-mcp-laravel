<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductTag extends Model
{
    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /** @return BelongsToMany<Product, $this> */
    public function product(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tag');
    }
}
