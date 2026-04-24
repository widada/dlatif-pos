<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse|Response
    {
        // API endpoint for product forms
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(Category::orderBy('name')->get());
        }

        $categories = Category::withCount('products')->orderBy('name')->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Category::create($validated);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $oldName = $category->name;
        $category->update($validated);

        // Update product category names if changed
        if ($oldName !== $validated['name']) {
            Product::where('category', $oldName)
                ->update(['category' => $validated['name']]);
        }

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $productCount = Product::where('category', $category->name)->count();

        if ($productCount > 0) {
            return back()->with('error', "Tidak bisa hapus — masih ada {$productCount} produk di kategori ini.");
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}
