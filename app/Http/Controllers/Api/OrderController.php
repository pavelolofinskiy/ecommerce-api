<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;

class OrderController extends Controller
{
    // Оформление заказа
    public function checkout(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|array',
            'shipping_address.name' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_address.address' => 'required|string',
        ]);

        $user = $request->user();
        $cart = Cart::with('items.product.prices')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Корзина пуста'], 400);
        }

        $total = 0;
        foreach ($cart->items as $item) {
            $price = $item->product->prices->where('type', 'default')->first();
            if (!$price) {
                return response()->json(['message' => 'Цена для товара не найдена'], 400);
            }
            $total += $price->amount * $item->quantity;
        }

        // Создаём заказ
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'new',
            'total' => $total,
            'shipping_address' => $request->shipping_address,
        ]);

        // Добавляем товары в заказ
        foreach ($cart->items as $item) {
            $price = $item->product->prices->where('type', 'default')->first();

            $order->items()->create([
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $price->amount,
            ]);
        }

        // Очищаем корзину
        $cart->items()->delete();

        return response()->json(['message' => 'Заказ успешно создан', 'order_id' => $order->id]);
    }

    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items.product')->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($orders);
    }

    // Получить детали одного заказа
    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $order->load('items.product');

        return response()->json($order);
    }
}