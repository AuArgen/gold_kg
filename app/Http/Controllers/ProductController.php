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
            // Пропускаем, если нет ID
            if (empty($productData['id'])) {
                continue;
            }

            Product::updateOrCreate(
                ['id' => $productData['id']],
                [
                    'name' => $productData['name'] ?? 'Без названия',
                    'brand' => $productData['brand'] ?? 'Неизвестный бренд',
                    'brandId' => $productData['brandId'] ?? 0,
                    'feedbacks' => $productData['feedbacks'] ?? 0,
                    'reviewRating' => $productData['reviewRating'] ?? 0.0,
                    'price' => data_get($productData, 'sizes.0.price.product') ?? 0,
                    'supplier' => $productData['supplier'] ?? 'Неизвестный поставщик',
                    'supplierId' => $productData['supplierId'] ?? 0,
                    'supplierRating' => $productData['supplierRating'] ?? 0.0,
                    'totalQuantity' => $productData['totalQuantity'] ?? 0,
                ]
            );
        }

        return response()->json(['message' => 'Products stored successfully']);
    }

    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }
}
