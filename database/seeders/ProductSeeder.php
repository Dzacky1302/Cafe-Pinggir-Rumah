<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan produk lama supaya tidak dobel setiap re-seed
        Product::truncate();

        // Harga disimpan langsung sebagai integer rupiah (tidak dikali 100)
        $coffees = [
            [
                'name' => 'Espresso',
                'description' => 'Shot espresso pekat dari biji Arabika Aceh Gayo dengan crema kaya dan aroma cokelat.',
                'image' => 'https://images.unsplash.com/photo-1510707577719-ae7c14805e3a?auto=format&fit=crop&w=600&q=80',
                'variants' => [
                    ['variant' => 'single', 'price' => 18000],
                    ['variant' => 'double', 'price' => 25000],
                ],
            ],
            [
                'name' => 'Cappuccino',
                'description' => 'Perpaduan sempurna espresso, steamed milk, dan foam susu tebal khas Italia.',
                'image' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=600&q=80',
                'variants' => [
                    ['variant' => 'regular', 'price' => 28000],
                    ['variant' => 'large',   'price' => 33000],
                ],
            ],
            [
                'name' => 'Cafe Latte',
                'description' => 'Espresso lembut dipadu susu segar dengan latte art indah di setiap cangkir.',
                'image' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=600&q=80',
                'variants' => [
                    ['variant' => 'hot',  'price' => 30000],
                    ['variant' => 'iced', 'price' => 32000],
                ],
            ],
            [
                'name' => 'Americano',
                'description' => 'Espresso dengan tambahan air panas — rasa bold dan bersih, cocok untuk pagi hari.',
                'image' => 'https://images.unsplash.com/photo-1497935586351-b67a49e012bf?auto=format&fit=crop&w=600&q=80',
                'variants' => [
                    ['variant' => 'hot',  'price' => 22000],
                    ['variant' => 'iced', 'price' => 24000],
                ],
            ],
            [
                'name' => 'Caramel Macchiato',
                'description' => 'Vanilla, susu steam, espresso, dan drizzle karamel manis — signature favorit kami.',
                'image' => 'https://images.unsplash.com/photo-1568649929103-28ffbefaca1e?auto=format&fit=crop&w=600&q=80',
                'variants' => [
                    ['variant' => 'hot',  'price' => 35000],
                    ['variant' => 'iced', 'price' => 38000],
                ],
            ],
            [
                'name' => 'Mocha',
                'description' => 'Kombinasi espresso, cokelat premium, dan susu — manis pahit yang seimbang.',
                'image' => 'https://images.unsplash.com/photo-1578314675249-a6910f80cc4e?auto=format&fit=crop&w=600&q=80',
                'variants' => [
                    ['variant' => 'hot',  'price' => 33000],
                    ['variant' => 'iced', 'price' => 36000],
                ],
            ],
            [
                'name' => 'Kopi Susu Gula Aren',
                'description' => 'Kopi lokal favorit dengan gula aren asli — manis alami dan aroma khas Nusantara.',
                'image' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=600&q=80',
                'variants' => [
                    ['variant' => 'regular', 'price' => 25000],
                    ['variant' => 'large',   'price' => 30000],
                ],
            ],
            [
                'name' => 'Vanilla Latte',
                'description' => 'Latte lembut dengan sirup vanilla premium — favorit para pencinta rasa manis halus.',
                'image' => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80',
                'variants' => [
                    ['variant' => 'hot',  'price' => 32000],
                    ['variant' => 'iced', 'price' => 35000],
                ],
            ],
        ];

        foreach ($coffees as $c) {
            foreach ($c['variants'] as $v) {
                Product::create([
                    'name' => $c['name'].' ('.ucfirst($v['variant']).')',
                    'description' => $c['description'],
                    'variant' => $v['variant'],
                    'price' => $v['price'],
                    'image' => $c['image'],
                    'status' => 'active',
                ]);
            }
        }
    }
}
