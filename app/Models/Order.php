<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Order extends Model
{
    protected $fillable = ['user_id', 'status', 'total', 'shipping_address'];

    protected $casts = [
        'shipping_address' => 'array',
    ];

    // Связь с пользователем
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Связь с элементами заказа (товарами)
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}