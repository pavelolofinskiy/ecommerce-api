<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class CartController extends Controller
{
    // Получить корзину
    public function getCart(Request $request)
    {
        if ($request->user()) {
            $cart = Cart::with('items.product')->where('user_id', $request->user()->id)->first();

            $items = $cart ? $cart->items->map(function ($item) {
                return [
                    'product' => $item->product,
                    'quantity' => $item->quantity,
                ];
            }) : collect();

            $total = $items->sum(function ($item) {
                return $item['product']->price * $item['quantity'];
            });

            return response()->json([
                'items' => $items,
                'total' => $total,
            ]);
        }

        // Гость
        $sessionCart = session('cart', []);
        if (empty($sessionCart)) {
            return response()->json(['items' => [], 'total' => 0]);
        }

        $products = Product::whereIn('id', array_keys($sessionCart))->get();

        $items = $products->map(function ($product) use ($sessionCart) {
            return [
                'product' => $product,
                'quantity' => $sessionCart[$product->id]['quantity'],
            ];
        });

        $total = $items->sum(function ($item) {
            return $item['product']->price * $item['quantity'];
        });

        return response()->json([
            'items' => $items,
            'total' => $total,
        ]);
    }

    // Добавить товар
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;

        if ($request->user()) {
            $user = $request->user();
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);

            $item = $cart->items()->where('product_id', $productId)->first();

            if ($item) {
                $item->quantity += $quantity;
                $item->save();
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }

            return response()->json([
                'message' => 'Товар добавлен в корзину (авторизован)',
                'items' => $cart->load('items.product')->items,
            ]);
        }

        // Гость
        $cart = session('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = ['quantity' => $quantity];
        }

        session(['cart' => $cart]);

        $products = Product::whereIn('id', array_keys($cart))->get();

        $items = $products->map(function ($product) use ($cart) {
            return [
                'product' => $product,
                'quantity' => $cart[$product->id]['quantity'],
            ];
        });

        return response()->json([
            'message' => 'Товар добавлен в корзину (гость)',
            'items' => $items,
        ]);
    }

    // Удалить товар
    public function removeItem(Request $request, $productId)
    {
        if ($request->user()) {
            $cart = Cart::where('user_id', $request->user()->id)->first();
            if (!$cart) return response()->json(['message' => 'Корзина не найдена'], 404);

            $item = $cart->items()->where('product_id', $productId)->first();
            if (!$item) return response()->json(['message' => 'Товар не найден'], 404);

            $item->delete();
            return response()->json(['message' => 'Товар удалён (авторизован)']);
        }

        // Гость
        $cart = session('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session(['cart' => $cart]);
        }

        return response()->json(['message' => 'Товар удалён (гость)']);
    }

    public function updateItemQuantity(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->product_id;
        $quantity = $request->quantity;

        if ($request->user()) {
            $cart = Cart::where('user_id', $request->user()->id)->first();

            if (!$cart) {
                return response()->json(['message' => 'Корзина не найдена'], 404);
            }

            $item = $cart->items()->where('product_id', $productId)->first();

            if (!$item) {
                return response()->json(['message' => 'Товар не найден в корзине'], 404);
            }

            $item->quantity = $quantity;
            $item->save();

            return response()->json([
                'message' => 'Количество товара обновлено (авторизован)',
                'items' => $cart->load('items.product')->items,
            ]);
        }

        // Гость
        $cart = session('cart', []);

        if (!isset($cart[$productId])) {
            return response()->json(['message' => 'Товар не найден в корзине'], 404);
        }

        $cart[$productId]['quantity'] = $quantity;
        session(['cart' => $cart]);

        $products = Product::whereIn('id', array_keys($cart))->get();

        $items = $products->map(function ($product) use ($cart) {
            return [
                'product' => $product,
                'quantity' => $cart[$product->id]['quantity'],
            ];
        });

        return response()->json([
            'message' => 'Количество товара обновлено (гость)',
            'items' => $items,
        ]);
    }
}