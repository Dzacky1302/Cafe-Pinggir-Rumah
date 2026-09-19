<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Smoke test storefront.
 *
 * Menggantikan scaffold bawaan Laravel yang selalu gagal — route '/'
 * bukan halaman, melainkan redirect ke katalog (lihat routes/web.php).
 */
class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_root_url_redirects_to_the_storefront(): void
    {
        $this->get('/')->assertRedirect(route('coffee.index'));
    }

    public function test_the_health_endpoint_reports_the_application_is_up(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_the_storefront_shows_every_available_product(): void
    {
        $product = Product::factory()->variant('single', 18000)->create();

        $response = $this->get(route('coffee.index'));

        $response->assertOk();
        $response->assertViewHas('grouped', function (array $grouped) use ($product): bool {
            foreach ($grouped as $variants) {
                foreach ($variants as $variant) {
                    if ($variant->id === $product->id) {
                        return true;
                    }
                }
            }

            return false;
        });
    }

    /**
     * Karakterisasi perilaku yang ada sekarang (dan rapuh).
     *
     * Katalog mengelompokkan produk lewat regex pada nama produk:
     * preg_replace('/\s*\(.*\)$/', '', $p->name) di CoffeeController::index().
     * Jadi grouping bergantung pada format penamaan 'Nama (Varian)' — inilah
     * dasar alasan kategori eksplisit diperlukan (Fase 2).
     */
    public function test_variants_are_grouped_by_the_product_name_without_its_parenthesis(): void
    {
        Product::factory()->create(['name' => 'Cappuccino (Regular)']);
        Product::factory()->create(['name' => 'Cappuccino (Large)']);

        $this->get(route('coffee.index'))
            ->assertOk()
            ->assertViewHas('grouped', function (array $grouped): bool {
                return array_key_exists('Cappuccino', $grouped)
                    && count($grouped['Cappuccino']) === 2;
            });
    }
}
