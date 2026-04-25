<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function place(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'customer_email' => 'nullable|email',
            'customer_name' => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $product = Product::findOrFail($validated['product_id']);

            if ($product->status !== 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product is not available for ordering',
                ], 422);
            }

            if ($product->stock_quantity < $validated['quantity']) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Insufficient stock. Available: {$product->stock_quantity}, Requested: {$validated['quantity']}",
                ], 422);
            }

            $order = Order::create([
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $product->price,
                'total_amount' => $product->price * $validated['quantity'],
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'status' => 'completed',
            ]);

            $product->decrement('stock_quantity', $validated['quantity']);

            return response()->json([
                'status' => 'success',
                'message' => 'Order placed successfully',
                'data' => $order->load('product'),
            ], 201);
        });
    }

    public function history(Request $request): JsonResponse
    {
        $query = Order::with('product');

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $orders,
        ]);
    }
}