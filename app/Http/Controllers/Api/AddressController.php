<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;

class AddressController extends Controller
{
    // Получить список адресов пользователя
    public function index(Request $request)
    {
        return $request->user()->addresses()->get();
    }

    // Добавить новый адрес
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'is_default' => 'boolean',
        ]);

        if (!empty($validated['is_default']) && $validated['is_default']) {
            // Сделать все остальные адреса не дефолтными
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($validated);

        return response()->json($address, 201);
    }

    // Обновить адрес
    public function update(Request $request, Address $address)
    {
        $this->authorize('update', $address);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'address' => 'sometimes|string',
            'is_default' => 'sometimes|boolean',
        ]);

        if (!empty($validated['is_default']) && $validated['is_default']) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json($address);
    }

    // Удалить адрес
    public function destroy(Request $request, Address $address)
    {
        $this->authorize('delete', $address);

        $address->delete();

        return response()->json(['message' => 'Адрес удалён']);
    }
}
