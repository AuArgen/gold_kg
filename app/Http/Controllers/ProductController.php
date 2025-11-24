<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $productsData = $request->input('products', []);

        foreach ($productsData as $productData) {
            // Проверяем, есть ли ID, без него запись бесполезна
            if (empty($productData['id'])) {
                continue;
            }

            Product::updateOrCreate(
                ['id' => $productData['id']],
                [
                    'name' => Arr::get($productData, 'name', 'Без названия'),
                    'brand' => Arr::get($productData, 'brand', 'Неизвестный бренд'),
                    'brandId' => Arr::get($productData, 'brandId', 0),
                    'feedbacks' => Arr::get($productData, 'feedbacks', 0),
                    'reviewRating' => Arr::get($productData, 'reviewRating', 0.0),
                    // Безопасно получаем вложенную цену, по умолчанию 0
                    'price' => data_get($productData, 'sizes.0.price.product', 0),
                    'supplier' => Arr::get($productData, 'supplier', 'Неизвестный поставщик'),
                    'supplierId' => Arr::get($productData, 'supplierId', 0),
                    'supplierRating' => Arr::get($productData, 'supplierRating', 0.0),
                    'totalQuantity' => Arr::get($productData, 'totalQuantity', 0),
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
