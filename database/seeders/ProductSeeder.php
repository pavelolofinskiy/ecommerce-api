<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Price;
use App\Models\Attribute;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $product = Product::create([
            'name' => 'Футболка красная',
            'description' => 'Хлопковая футболка',
            'category_id' => 1,
        ]);

        // Изображения
        ProductImage::create([
            'product_id' => $product->id,
            'url' => 'https://via.placeholder.com/400x400.png?text=Футболка',
        ]);

        // Цена
        Price::create([
            'product_id' => $product->id,
            'type' => 'default',
            'amount' => 499.00,
            'currency' => 'UAH',
        ]);

        // Атрибуты
        $color = Attribute::where('name', 'Цвет')->first();
        $size = Attribute::where('name', 'Размер')->first();

        $product->attributes()->attach($color->id, ['value' => 'Красный']);
        $product->attributes()->attach($size->id, ['value' => 'L']);
    }
}
