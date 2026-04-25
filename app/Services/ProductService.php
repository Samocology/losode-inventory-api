<?php

namespace App\Services;

use App\Exceptions\ProductException;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Validator;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {}

    public function createProduct(int $vendorId, array $data): Product
    {
        $this->validateProductData($data);
        
        $data['vendor_id'] = $vendorId;
        $data['sku'] = $data['sku'] ?? $this->generateSku($vendorId, $data['name']);
        
        if ($this->skuExists($data['sku'])) {
            throw new ProductException('SKU already exists');
        }

        return $this->productRepository->create($data);
    }

    public function updateProduct(Product $product, array $data): Product
    {
        $this->validateProductData($data, true);

        // Handle stock update with validation
        if (isset($data['stock_quantity'])) {
            if ($data['stock_quantity'] < 0) {
                throw new ProductException('Stock quantity cannot be negative');
            }
        }

        return $this->productRepository->update($product, $data);
    }

    public function deleteProduct(Product $product): bool
    {
        // Check if there are pending orders before deleting
        if ($product->orders()->pending()->exists()) {
            throw new ProductException('Cannot delete product with pending orders');
        }

        return $this->productRepository->delete($product);
    }

    private function validateProductData(array $data, bool $isUpdate = false): void
    {
        $rules = [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'in:active,inactive'],
            'sku' => ['nullable', 'string', 'unique:products,sku' . ($isUpdate ? ',' . request()->route('product')->id : '')],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ProductException($validator->errors()->first());
        }
    }

    private function generateSku(int $vendorId, string $productName): string
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $productName), 0, 3));
        return $prefix . '-' . $vendorId . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function skuExists(string $sku): bool
    {
        return Product::where('sku', $sku)->exists();
    }
}