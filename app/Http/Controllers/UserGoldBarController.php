<?php

namespace App\Http\Controllers;

use App\Models\UserGoldBar;
use App\Models\GoldPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserGoldBarController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userBars = UserGoldBar::where('user_id', $user->id)->orderBy('purchase_date', 'desc')->get();

        // Получаем последнюю актуальную цену на золото
        $currentGoldPrice = GoldPrice::latest()->first();

        return view('client.my_gold', [
            'userBars' => $userBars,
            'currentGoldPrice' => $currentGoldPrice->price ?? 0, // Передаем только цену
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_date' => 'required|date|before_or_equal:today',
            'quantity' => 'required|integer|min:1',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Находим цену на золото на указанную дату
        $priceOnDate = GoldPrice::where('date', $request->purchase_date)->value('price');

        if (!$priceOnDate) {
            return back()->withErrors(['purchase_date' => 'К сожалению, у нас нет данных о цене на указанную дату.']);
        }

        UserGoldBar::create([
            'user_id' => Auth::id(),
            'purchase_date' => $request->purchase_date,
            'quantity' => $request->quantity,
            'purchase_price_per_bar' => $priceOnDate,
            'comment' => $request->comment,
        ]);

        return redirect()->route('my-gold.index')->with('success', 'Слиток успешно добавлен!');
    }
}
