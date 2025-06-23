<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;

class CartController extends Controller
{
    // Получить текущую корзину пользователя с товарами
    public function getCart(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['items' => []]);
        }

        return response()->json($cart);
    }

    // Добавить товар в корзину (если есть — увеличить количество)
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = $request->user();

        // Получаем или создаём корзину
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Проверяем, есть ли уже товар в корзине
        $item = $cart->items()->where('product_id', $request->product_id)->first();

        if ($item) {
            $item->quantity += $request->quantity;
            $item->save();
        } else {
            $cart->items()->create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json(['message' => 'Товар добавлен в корзину']);
    }

    // Удалить товар из корзины
    public function removeItem(Request $request, $itemId)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json(['message' => 'Корзина не найдена'], 404);
        }

        $item = $cart->items()->where('id', $itemId)->first();

        if (!$item) {
            return response()->json(['message' => 'Товар в корзине не найден'], 404);
        }

        $item->delete();

        return response()->json(['message' => 'Товар удалён из корзины']);
    }
}
