<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $productsData = $request->input('products', []);
        $changedProducts = [];

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

            $url = $dataToInsert['url'] ?? '#';
            $imageUrl = $dataToInsert['imageUrl'] ? "[.]( {$dataToInsert['imageUrl']} )" : ""; // Невидимая ссылка на картинку

            if ($product) {
                if ($product->currentPrice != $newPrice) {
                    $product->update($dataToInsert);
                    $title = $product->title;
                    $changedProducts[] = "✏️ *[{$title}]({$url})*{$imageUrl}\n_Цена изменилась:_ {$product->currentPrice} -> {$newPrice}";
                }
            } else {
                $newProduct = Product::create(['product_id' => $productData['id']] + $dataToInsert);
                $title = $newProduct->title;
                $changedProducts[] = "✨ *[{$title}]({$url})*{$imageUrl}\n_Новый товар по цене:_ {$newPrice}";
            }
        }

        if (!empty($changedProducts)) {
            $this->sendTelegramNotification($changedProducts);
        }

        return response()->json([
            'message' => "Processing complete. Changes detected: " . count($changedProducts)
        ]);
    }

    private function sendTelegramNotification(array $products)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$token || !$chatId) {
            return;
        }

        $message = "🔔 *Обновления по товарам:*\n\n";
        $message .= implode("\n\n", $products);

        Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'MarkdownV2', // Используем MarkdownV2 для лучшей поддержки
            'disable_web_page_preview' => false, // Включаем превью для ссылок
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

        $products = $query->latest()->paginate(2000);

        return response()->json($products);
    }

    public function getLatest(Request $request)
    {
        $lastId = $request->query('lastId', 0);
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
