<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $electronicsProducts = [
            [
                'name' => 'Wireless Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation',
                'price' => 99.99,
                'stock_quantity' => 50,
                'status' => 'active',
                'sku' => 'WH-001-JOHN',
            ],
            [
                'name' => 'Smart Watch',
                'description' => 'Feature-rich smart watch with health monitoring',
                'price' => 299.99,
                'stock_quantity' => 30,
                'status' => 'active',
                'sku' => 'SW-002-JOHN',
            ],
            // Add more products...
        ];

        $fashionProducts = [
            [
                'name' => 'Denim Jacket',
                'description' => 'Classic denim jacket for all seasons',
                'price' => 79.99,
                'stock_quantity' => 45,
                'status' => 'active',
                'sku' => 'DJ-001-JANE',
            ],
            [
                'name' => 'Summer Dress',
                'description' => 'Floral summer dress perfect for casual occasions',
                'price' => 49.99,
                'stock_quantity' => 60,
                'status' => 'active',
                'sku' => 'SD-002-JANE',
            ],
            // Add more products...
        ];

        foreach ($electronicsProducts as $product) {
            Product::create(array_merge(['vendor_id' => 1], $product));
        }

        foreach ($$fashionProducts as $product) {
            Product::create(array_merge(['vendor_id' => 2], $product));
        }
    }
}