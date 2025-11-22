<?php

namespace App\Http\Controllers\public;

use App\Http\Controllers\Controller;
use App\Models\GoldPrice;
use App\Models\Gold;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class IndexController extends Controller
{
    /**
     * Отображение главной публичной страницы с ценами, графиком и калькуляторами.
     */
    public function index(Request $request)
    {
        // 1. Получаем все типы золотых слитков
        $golds = Gold::orderBy('weight_units', 'asc')->get();

        // 2. Получаем последние цены для каждой монеты (для карточек и калькуляторов)
        $latestPrices = [];
        $latestPublicDate = null;

        foreach ($golds as $gold) {
            $latestPrice = GoldPrice::where('gold_id', $gold->id)
                ->orderBy('public_date', 'desc')
                ->first();

            if ($latestPrice) {
                $latestPrices[] = $latestPrice;
                if (!$latestPublicDate || $latestPrice->public_date > $latestPublicDate) {
                    $latestPublicDate = $latestPrice->public_date;
                }
            }
        }

        // 3. Получаем все исторические цены (для графика и модальных окон)
        // Группируем цены по дате
        $allHistoricalPrices = GoldPrice::with('gold')
            ->orderBy('public_date', 'desc')
            ->orderBy('gold_id', 'asc')
            ->get()
            ->groupBy(function($item) {
                return Carbon::parse($item->public_date)->format('Y-m-d');
            });

        // 4. Получаем пагинированный список цен для нижней таблицы
        $allPrices = GoldPrice::with('gold')
            ->orderBy('public_date', 'desc')
            ->orderBy('gold_id', 'asc')
            ->paginate(30);

        return view('public.index', compact('golds', 'latestPrices', 'allPrices', 'latestPublicDate', 'allHistoricalPrices'));
    }

    public function contact(Request $request)
    {
        return view('public.contact');
    }

    public function countUser(Request $request)
    {
        return view('iframe/iframe');
    }
}
