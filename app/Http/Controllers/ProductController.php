<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $productsData = $request->input('products', []);
        $updatedCount = 0;
        $createdCount = 0;

        foreach ($productsData as $productData) {
            if (empty($productData['id'])) {
                continue;
            }

            $discount = $productData['discountPercentage'] ?? null;
            if ($discount) {
                $discount = (int) preg_replace('/[^0-9-]/', '', $discount);
            }

            $newPrice = $productData['currentPrice'] ?? 0;

            $product = Product::where('product_id', $productData['id'])->first();

            $dataToInsert = [
                'url' => $productData['url'] ?? null,
                'imageUrl' => $productData['imageUrl'] ?? null,
                'brand' => $productData['brand'] ?? null,
                'name' => $productData['name'] ?? 'Без названия',
                'title' => $productData['title'] ?? 'Без названия',
                'currentPrice' => $newPrice,
                'oldPrice' => $productData['oldPrice'] ?? null,
                'discountPercentage' => $discount,
                'isNew' => $productData['isNew'] ?? false,
                'isGoodPrice' => $productData['isGoodPrice'] ?? false,
                'actionPromotion' => $productData['actionPromotion'] ?? null,
                'rating' => $productData['rating'] ?? null,
                'reviewCount' => $productData['reviewCount'] ?? null,
            ];

            if ($product) {
                if ($product->currentPrice != $newPrice) {
                    $product->update($dataToInsert);
                    $updatedCount++;
                }
            } else {
                Product::create(['product_id' => $productData['id']] + $dataToInsert);
                $createdCount++;
            }
        }

        return response()->json([
            'message' => "Processing complete. Created: $createdCount, Updated: $updatedCount."
        ]);
    }

    public function index(Request $request)
    {
        $query = Product::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($brand = $request->query('brand')) {
            $query->where('brand', $brand);
        }

        if ($minDiscount = $request->query('min_discount')) {
            $query->where('discountPercentage', '>=', $minDiscount);
        }

        $products = $query->latest('updated_at')->paginate(50);

        return response()->json($products);
    }

    public function getLatest(Request $request)
    {
        $lastTimestamp = $request->query('lastTimestamp');

        $query = Product::query();

        if ($lastTimestamp) {
            // Добавляем 1 секунду, чтобы не получать ту же запись снова
            $query->where('updated_at', '>', Carbon::parse($lastTimestamp)->addSecond());
        }

        $products = $query->latest('updated_at')->get();

        return response()->json($products);
    }

    public function getBrands()
    {
        $brands = Product::whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return response()->json($brands);
    }
}
