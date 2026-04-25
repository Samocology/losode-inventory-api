<?php

namespace App\Services;

use App\Exceptions\OrderException;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Validator;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly ProductRepository $productRepository
    ) {}

    public function placeOrder(array $data)
    {
        $this->validateOrderData($data);

        $product = $this->productRepository->findById($data['product_id']);

        if (!$product) {
            throw new OrderException('Product not found');
        }

        if ($product->status !== 'active') {
            throw new OrderException('Product is not available for ordering');
        }

        if (!$product->hasEnoughStock($data['quantity'])) {
            throw new OrderException(
                "Insufficient stock. Available: {$product->stock_quantity}, Requested: {$data['quantity']}"
            );
        }

        return $this->orderRepository->create($data);
    }

    private function validateOrderData(array $data): void
    {
        $rules = [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'customer_email' => 'nullable|email',
            'customer_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new OrderException($validator->errors()->first());
        }
    }
}