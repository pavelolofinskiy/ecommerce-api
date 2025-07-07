<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['category', 'images', 'prices', 'attributes']);

        // Фильтрация по категории
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Фильтрация по цене
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

        // Фильтрация по атрибутам
        if ($request->has('filter')) {
            foreach ($request->get('filter') as $attrName => $value) {
                $query->whereHas('attributes', function ($q) use ($attrName, $value) {
                    $q->where('attributes.name', $attrName)
                    ->where('attribute_product.value', $value);
                });
            }
        }

        // Поиск
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Сортировка
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'price_asc':
                    $query->join('prices', function ($join) {
                        $join->on('products.id', '=', 'prices.product_id')
                            ->where('prices.type', 'default');
                    })->orderBy('prices.amount', 'asc');
                    break;

                case 'price_desc':
                    $query->join('prices', function ($join) {
                        $join->on('products.id', '=', 'prices.product_id')
                            ->where('prices.type', 'default');
                    })->orderBy('prices.amount', 'desc');
                    break;

                default:
                    // Можно добавить сортировку по популярности или дате
                    break;
            }
        }

        // Количество товаров на странице
        $perPage = $request->input('per_page', 10);

        // Получить пагинированный результат
        $products = $query->paginate($perPage);

        // Убрать дубликаты из-за join с ценами
        $products->setCollection($products->getCollection()->unique('id'));

        return response()->json([
            'data' => $products->items(),
            'total' => $products->total(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
        ]);
    }

    public function show($id)
    {
        return Product::with(['images', 'prices'])->findOrFail($id);
    }
}