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
        ]);

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
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
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
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

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->image = '/storage/' . $path;
        }

        $product->save();

        if (isset($validated['price'])) {
            // Обновить цену или создать новую запись, если нужно
            $product->prices()->updateOrCreate(
                ['type' => 'default'],
                ['amount' => $validated['price']]
            );
        }

        return response()->json($product);
    }

}
