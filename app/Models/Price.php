<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = ['product_id', 'type', 'amount', 'currency'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}