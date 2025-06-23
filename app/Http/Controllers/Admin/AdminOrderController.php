<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index()
    {
        return Order::with('user', 'items.product')->orderBy('created_at', 'desc')->paginate(20);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate(['status' => 'required|string|in:new,processing,completed,canceled']);

        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Статус обновлён', 'order' => $order]);
    }
}