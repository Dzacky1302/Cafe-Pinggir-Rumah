<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where('name', 'like', "%{$q}%")
                ->orWhere('variant', 'like', "%{$q}%");
        }
        $products = $query->orderBy('name')->paginate(12)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['image'] = $this->handleImage($request, null);
        Product::create($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request);
        $data['image'] = $this->handleImage($request, $product->image);
        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Hapus file lokal kalau ada
        if ($product->image && str_starts_with($product->image, '/storage/')) {
            $path = str_replace('/storage/', '', $product->image);
            Storage::disk('public')->delete($path);
        }
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk dihapus.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:150',
            'variant' => 'nullable|string|max:100',
            'price' => 'required|integer|min:0',
            'status' => 'nullable|in:active,inactive',
            'image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_url' => 'nullable|url|max:500',
        ]);
    }

    private function handleImage(Request $request, ?string $current): ?string
    {
        if ($request->hasFile('image_file')) {
            // Hapus file lokal lama
            if ($current && str_starts_with($current, '/storage/')) {
                $path = str_replace('/storage/', '', $current);
                Storage::disk('public')->delete($path);
            }
            $stored = $request->file('image_file')->store('products', 'public');

            return '/storage/'.$stored;
        }

        if ($request->filled('image_url')) {
            return $request->image_url;
        }

        return $current;
    }
}
