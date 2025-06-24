<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class AdminCategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:categories,name']);
        return Category::create(['name' => $request->name]);
    }

    public function update(Request $request, Category $category)
    {
        $request->validate(['name' => 'required|string|unique:categories,name,' . $category->id]);
        $category->update(['name' => $request->name]);
        return $category;
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(['message' => 'Категория удалена']);
    }
}