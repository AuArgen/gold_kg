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
                    'discountPercentage' => $productData['discountPercentage'] ?? null,
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

    public function index()
    {
        // Сортируем по дате создания от новых к старым и добавляем пагинацию по 50 товаров на страницу
        $products = Product::latest()->paginate(50);
        return response()->json($products);
    }

    public function getLatest(Request $request)
    {
        $lastId = $request->query('lastId', 0); // lastId здесь - это наш внутренний ID
        $products = Product::where('id', '>', $lastId)->latest()->get();
        return response()->json($products);
    }
}
