<?php

namespace App\Http\Controllers;

use App\Models\Product;

/**
 * Katalog publik.
 *
 * CATATAN (Fase 2): pengelompokan varian masih bergantung pada format nama
 * produk ('Nama (Varian)') lewat regex. Bila admin mengubah nama produk jadi
 * 'Cappuccino Large (Iced)', produk bisa jatuh ke grup yang salah. Perilaku ini
 * sengaja dipertahankan apa adanya di sini dan sudah dikunci oleh
 * tests/Feature/ExampleTest::test_variants_are_grouped_...
 */
final class MenuController extends Controller
{
    public function index()
    {
        $products = Product::query()->orderBy('name')->get();

        $grouped = [];

        foreach ($products as $product) {
            $base = preg_replace('/\s*\(.*\)$/', '', $product->name);
            $grouped[$base][] = $product;
        }

        return view('coffee', ['grouped' => $grouped]);
    }
}
