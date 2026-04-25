<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    private const CACHE_TTL = 3600; // 1 hour

    public function getAllActive(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = 'products:active:' . md5(serialize($filters) . $perPage . request()->get('page', 1));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters, $perPage) {
            $query = Product::query()->active();

            if (!empty($filters['search'])) {
                $query->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            }

            return $query->with('vendor:id,name,store_name')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
    }

    public function findByVendorId(int $vendorId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query()->byVendor($vendorId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return Cache::remember('product:' . $id, self::CACHE_TTL, function () use ($id) {
            return Product::with('vendor:id,name,store_name')->find($id);
        });
    }

    public function create(array $data): Product
    {
        $product = Product::create($data);
        $this->clearCache();
        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        Cache::forget('product:' . $product->id);
        $this->clearCache();
        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        Cache::forget('product:' . $product->id);
        $this->clearCache();
        return $product->delete();
    }

    public function decrementStock(Product $product, int $quantity): bool
    {
        $updated = Product::where('id', $product->id)
            ->where('stock_quantity', '>=', $quantity)
            ->decrement('stock_quantity', $quantity);

        if ($updated) {
            Cache::forget('product:' . $product->id);
            $this->clearCache();
        }

        return $updated > 0;
    }

    private function clearCache(): void
    {
        Cache::tags(['products'])->flush();
    }
}