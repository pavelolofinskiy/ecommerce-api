<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class AdminProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'tags' => 'nullable|array|min:1|max:3',
            'tags.*' => 'in:new,top,hit',
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'],
            'tags' => $validated['tags'] ?? [],
        ]);

        $product->prices()->create([
            'type' => 'default',
            'amount' => $validated['price'],
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = '/storage/' . $path;
            $product->save();
        }

        return response()->json($product, 201);
    }

    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->input('price_min'));
        }

        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }

        return $query->paginate(20);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'tags' => 'nullable|array|min:1|max:3',
            'tags.*' => 'in:new,top,hit',
        ]);

        if (isset($validated['name'])) {
            $product->name = $validated['name'];
        }

        if (array_key_exists('description', $validated)) {
            $product->description = $validated['description'];
        }

        if (isset($validated['category_id'])) {
            $product->category_id = $validated['category_id'];
        }

        if (isset($validated['tags'])) {
            $product->tags = $validated['tags'];
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = '/storage/' . $path;
        }

        $product->save();

        if (isset($validated['price'])) {
            $product->prices()->updateOrCreate(
                ['type' => 'default'],
                ['amount' => $validated['price']]
            );
        }

        return response()->json($product);
    }

    public function bulkUpdateType(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'type' => 'required|string',
        ]);

        Product::whereIn('id', $request->product_ids)->update(['type' => $request->type]);

        return response()->json(['message' => 'Тип товаров обновлен']);
    }
}