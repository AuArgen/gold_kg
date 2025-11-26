<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $productsData = $request->input('products', []);

        foreach ($productsData as $productData) {
            // Пропускаем, если нет product_id
            if (empty($productData['id'])) { // 'id' из JS данных теперь наш product_id
                continue;
            }

            // Очищаем значение скидки, оставляя только цифры
            $discount = $productData['discountPercentage'] ?? null;
            if ($discount) {
                // Удаляем все, кроме цифр (и знака минуса, если он есть)
                $discount = (int) preg_replace('/[^0-9-]/', '', $discount);
            }

            Product::updateOrCreate(
                ['product_id' => $productData['id']], // Ищем по product_id
                [
                    'url' => $productData['url'] ?? null,
                    'imageUrl' => $productData['imageUrl'] ?? null,
                    'brand' => $productData['brand'] ?? null,
                    'name' => $productData['name'] ?? 'Без названия',
                    'title' => $productData['title'] ?? 'Без названия',
                    'currentPrice' => $productData['currentPrice'] ?? 0,
                    'oldPrice' => $productData['oldPrice'] ?? null,
                    'discountPercentage' => $discount, // Сохраняем очищенное число
                    'isNew' => $productData['isNew'] ?? false,
                    'isGoodPrice' => $productData['isGoodPrice'] ?? false,
                    'actionPromotion' => $productData['actionPromotion'] ?? null,
                    'rating' => $productData['rating'] ?? null,
                    'reviewCount' => $productData['reviewCount'] ?? null,
                ]
            );
        }

        return response()->json(['message' => 'Products stored successfully']);
    }

    public function index(Request $request)
    {
        $query = Product::query();

        // Фильтр по поисковому запросу (ищет в title и brand)
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Фильтр по бренду
        if ($brand = $request->query('brand')) {
            $query->where('brand', $brand);
        }

        // Фильтр по минимальной скидке
        if ($minDiscount = $request->query('min_discount')) {
            $query->where('discountPercentage', '>=', $minDiscount);
        }

        // Сортируем по дате создания от новых к старым и добавляем пагинацию
        $products = $query->latest()->paginate(50);

        return response()->json($products);
    }

    public function getLatest(Request $request)
    {
        $lastId = $request->query('lastId', 0); // lastId здесь - это наш внутренний ID
        $products = Product::where('id', '>', $lastId)->latest()->get();
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
