<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'description',
        'price',
        'category_id',
        'image',
        'slug',
        'stock',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    
    public function attributes()
    {
        return $this->belongsToMany(\App\Models\Attribute::class, 'attribute_product')
            ->withPivot('value')
            ->withTimestamps();
    }

    
    public function scopeOrderByPriceAsc($query)
    {
        return $query->leftJoin('prices', function($join) {
            $join->on('products.id', '=', 'prices.product_id')
                ->where('prices.type', '=', 'default');
        })->orderBy('prices.amount', 'asc')->select('products.*');
    }

    public function scopeOrderByPriceDesc($query)
    {
        return $query->leftJoin('prices', function($join) {
            $join->on('products.id', '=', 'prices.product_id')
                ->where('prices.type', '=', 'default');
        })->orderBy('prices.amount', 'desc')->select('products.*');
    }
}
