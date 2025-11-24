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
            Product::updateOrCreate(
                ['id' => $productData['id']],
                [
                    'name' => $productData['name'],
                    'brand' => $productData['brand'],
                    'brandId' => $productData['brandId'],
                    'feedbacks' => $productData['feedbacks'],
                    'reviewRating' => $productData['reviewRating'],
                    'price' => $productData['price']['product'],
                    'supplier' => $productData['supplier'],
                    'supplierId' => $productData['supplierId'],
                    'supplierRating' => $productData['supplierRating'],
                    'totalQuantity' => $productData['totalQuantity'],
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
