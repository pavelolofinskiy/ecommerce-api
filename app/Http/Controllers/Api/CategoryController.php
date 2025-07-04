<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category; // Модель категории, замените на свою модель, если отличается

class CategoryController extends Controller
{
    public function index()
    {
        // Вернуть список всех категорий (можно добавить сортировку или фильтрацию)
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        return response()->json(['data' => $categories]);
    }
}