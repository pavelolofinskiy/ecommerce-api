<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $fillable = ['name', 'type'];

   public function products()
    {
        return $this->belongsToMany(\App\Models\Product::class, 'attribute_product')
            ->withPivot('value')
            ->withTimestamps();
    }
}