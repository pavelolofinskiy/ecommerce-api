<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {


        $query = Product::with(['category', 'images', 'prices', 'attributes']);

        // 🔸 Фильтр по категории
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 🔸 Фильтр по цене
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('prices', function ($q) use ($request) {
                $q->where('type', 'default');
                if ($request->filled('min_price')) {
                    $q->where('amount', '>=', $request->min_price);
                }
                if ($request->filled('max_price')) {
                    $q->where('amount', '<=', $request->max_price);
                }
            });
        }

        // 🔸 Фильтр по атрибутам через filter[Цвет]=Черный&filter[Вес]=5
        if ($request->has('filter')) {
            foreach ($request->get('filter') as $attrName => $value) {
                $query->whereHas('attributes', function ($q) use ($attrName, $value) {
                    $q->where('attributes.name', $attrName)
                    ->where('attribute_product.value', $value);
                });
            }
        }

        // 🔸 Поиск по имени
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 🔸 Сортировка
        if ($request->filled('sort_by')) {
            if ($request->sort_by === 'price_asc') {
                $query->with(['prices' => function ($q) {
                    $q->where('type', 'default');
                }])->orderByPriceAsc();
            } elseif ($request->sort_by === 'price_desc') {
                $query->with(['prices' => function ($q) {
                    $q->where('type', 'default');
                }])->orderByPriceDesc();
            }
        }

        return $query->paginate(10);
    }

    public function show($id)
    {
        return Product::with(['images', 'prices'])->findOrFail($id);
    }
}