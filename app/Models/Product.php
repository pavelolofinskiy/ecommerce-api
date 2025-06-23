<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'category_id'];

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
        return $this->belongsToMany(Attribute::class)
                    ->withPivot('value') // значение атрибута (например, цвет = красный)
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
