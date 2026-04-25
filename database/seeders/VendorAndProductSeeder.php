<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VendorAndProductSeeder extends Seeder
{
    public function run(): void
    {
        // Insert vendors
        DB::table('vendors')->insert([
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'store_name' => 'John\'s Electronics',
                'store_description' => 'Best electronics in town',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
                'store_name' => 'Jane\'s Fashion',
                'store_description' => 'Trendy fashion for everyone',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert products for vendor 1
        DB::table('products')->insert([
            [
                'vendor_id' => 1,
                'name' => 'Wireless Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation',
                'price' => 99.99,
                'stock_quantity' => 50,
                'status' => 'active',
                'sku' => 'WH-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => 1,
                'name' => 'Smart Watch',
                'description' => 'Feature-rich smart watch with health monitoring',
                'price' => 299.99,
                'stock_quantity' => 30,
                'status' => 'active',
                'sku' => 'SW-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => 1,
                'name' => 'USB-C Hub',
                'description' => '7-in-1 USB-C hub with HDMI, USB 3.0, and SD card reader',
                'price' => 49.99,
                'stock_quantity' => 100,
                'status' => 'active',
                'sku' => 'UH-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Insert products for vendor 2
        DB::table('products')->insert([
            [
                'vendor_id' => 2,
                'name' => 'Denim Jacket',
                'description' => 'Classic denim jacket for all seasons',
                'price' => 79.99,
                'stock_quantity' => 45,
                'status' => 'active',
                'sku' => 'DJ-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => 2,
                'name' => 'Summer Dress',
                'description' => 'Floral summer dress perfect for casual occasions',
                'price' => 49.99,
                'stock_quantity' => 60,
                'status' => 'active',
                'sku' => 'SD-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'vendor_id' => 2,
                'name' => 'Leather Handbag',
                'description' => 'Genuine leather handbag with multiple compartments',
                'price' => 129.99,
                'stock_quantity' => 25,
                'status' => 'active',
                'sku' => 'LH-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}