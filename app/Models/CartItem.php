<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    // Связь с корзиной
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Связь с товаром
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}