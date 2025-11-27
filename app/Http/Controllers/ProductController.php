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

            $newPrice = str_replace(' ', '', $productData['currentPrice'])  ?? 0;
            $product = Product::where('product_id', $productData['id'])->first();

            $dataToInsert = [
                'url' => $productData['url'] ?? null,
                'imageUrl' => $productData['imageUrl'] ?? null,
                'brand' => $productData['brand'] ?? null,
                'name' => $productData['name'] ?? 'Без названия',
                'title' => $productData['title'] ?? 'Без названия',
                'currentPrice' => $newPrice,
                'oldPrice' => str_replace(' ', '', $productData['oldPrice']) ?? null,
                'discountPercentage' => $discount,
                'isNew' => $productData['isNew'] ?? false,
                'isGoodPrice' => $productData['isGoodPrice'] ?? false,
                'actionPromotion' => $productData['actionPromotion'] ?? null,
                'rating' => $productData['rating'] ?? null,
                'reviewCount' => $productData['reviewCount'] ?? null,
            ];

            $url = $dataToInsert['url'] ?? '#';
            $imageUrl = $dataToInsert['imageUrl'] ? "[\u{200B}]({$dataToInsert['imageUrl']})" : ""; // Zero-width space for image link

            if ($product) {
                if (str_replace(' ', '', $product->currentPrice) != $newPrice) {
                    $product->update($dataToInsert);
                    $title = $this->escapeMarkdownV2($product->title);
                    $priceChange = "{$product->currentPrice} на {$newPrice}";
                    $changedProducts[] = "✏️ *[{$title}]({$url})*{$imageUrl}\n_Цена изменилась:_ {$priceChange}";
                }
            } else {
                $newProduct = Product::create(['product_id' => $productData['id']] + $dataToInsert);
                $title = $this->escapeMarkdownV2($newProduct->title);
                $price = $this->escapeMarkdownV2($newPrice);
                $changedProducts[] = "✨ *[{$title}]({$url})*{$imageUrl}\n_Новый товар по цене:_ {$price}";
            }
        }

        if (!empty($changedProducts)) {
            $this->sendTelegramNotification($changedProducts);
        }

        return response()->json([
            'message' => "Processing complete. Changes detected: " . count($changedProducts)
        ]);
    }

    private function escapeMarkdownV2($text)
    {
        return str_replace(
            ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'],
            ['\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+', '\-', '\=', '\|', '\{', '\}', '\.', '\!'],
            $text
        );
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
            'parse_mode' => 'MarkdownV2'
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
            $query->where('updated_at', '>', $lastTimestamp);
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
