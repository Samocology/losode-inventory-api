<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InsufficientStockException;

class OrderRepository
{
    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $product = Product::where('id', $data['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (!$product->hasEnoughStock($data['quantity'])) {
                throw new InsufficientStockException(
                    "Insufficient stock. Available: {$product->stock_quantity}, Requested: {$data['quantity']}"
                );
            }

            $order = Order::create([
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'unit_price' => $product->price,
                'total_amount' => $product->price * $data['quantity'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_name' => $data['customer_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
            ]);

            // Pessimistic locking to prevent race conditions
            $product->decrement('stock_quantity', $data['quantity']);

            return $order->load('product');
        });
    }

    public function getOrderHistory(array $filters = [], int $perPage = 15)
    {
        $query = Order::query()->with('product');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}