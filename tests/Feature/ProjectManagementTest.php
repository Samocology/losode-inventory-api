<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->vendor = Vendor::factory()->create();
        $this->token = $this->vendor->createToken('test-token')->plainTextToken;
    }

    public function test_vendor_can_create_product(): void
    {
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 99.99,
            'stock_quantity' => 100,
            'status' => 'active',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/vendor/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'stock_quantity',
                    'status',
                    'sku',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'vendor_id' => $this->vendor->id,
        ]);
    }

    public function test_guest_can_view_active_products(): void
    {
        Product::factory()->count(5)->create([
            'vendor_id' => $this->vendor->id,
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.data');
    }

    public function test_order_reduces_stock_atomically(): void
    {
        $product = Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'stock_quantity' => 10,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(201);
        
        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }

    public function test_cannot_order_more_than_stock(): void
    {
        $product = Product::factory()->create([
            'vendor_id' => $this->vendor->id,
            'stock_quantity' => 5,
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(422);
        
        // Stock should remain unchanged
        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }
}