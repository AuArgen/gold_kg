<?php

namespace App\Http\Controllers;

use App\Models\UserGoldBar;
use App\Models\GoldPrice;
use App\Models\Gold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserGoldBarController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userBars = UserGoldBar::with('goldBar')->where('user_id', $user->id)->orderBy('purchase_date', 'desc')->get();

        $goldBars = Gold::all();

        // Получаем ID последней записи для каждого gold_id
        $latestPriceIds = GoldPrice::select('gold_id', DB::raw('MAX(id) as last_id'))
            ->groupBy('gold_id');

        // Получаем полные записи для этих ID
        $currentGoldPrices = GoldPrice::whereIn('id', $latestPriceIds->pluck('last_id'))
            ->get()
            ->keyBy('gold_id');

        return view('client.my_gold', [
            'userBars' => $userBars,
            'goldBars' => $goldBars,
            'currentGoldPrices' => $currentGoldPrices,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'gold_bar_id' => 'required|exists:gold,id',
            'purchase_date' => 'required|date|before_or_equal:today',
            'quantity' => 'required|integer|min:1',
            'comment' => 'nullable|string|max:1000',
        ]);

        $priceRecord = GoldPrice::where('gold_id', $request->gold_bar_id)
            ->whereDate('public_date', '<=', $request->purchase_date)
            ->orderBy('public_date', 'desc')
            ->first();

        if (!$priceRecord) {
            return back()->withErrors(['purchase_date' => 'К сожалению, у нас нет данных о ценах до указанной даты.']);
        }

        $purchasePrice = $priceRecord->buy_in_kopecks / 100;

        UserGoldBar::create([
            'user_id' => Auth::id(),
            'gold_bar_id' => $request->gold_bar_id,
            'purchase_date' => $request->purchase_date,
            'quantity' => $request->quantity,
            'purchase_price_per_bar' => $purchasePrice,
            'price_date' => $priceRecord->public_date,
            'comment' => $request->comment,
        ]);

        return redirect()->route('my-gold.index')->with('success', 'Слиток успешно добавлен!');
    }

    public function edit(UserGoldBar $userGoldBar)
    {
        if ($userGoldBar->user_id !== Auth::id()) {
            abort(403);
        }

        $goldBars = Gold::all();

        return view('client.edit_my_gold', [
            'bar' => $userGoldBar,
            'goldBars' => $goldBars,
        ]);
    }

    public function update(Request $request, UserGoldBar $userGoldBar)
    {
        if ($userGoldBar->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'gold_bar_id' => 'required|exists:gold,id',
            'purchase_date' => 'required|date|before_or_equal:today',
            'quantity' => 'required|integer|min:1',
            'comment' => 'nullable|string|max:1000',
        ]);

        $priceRecord = GoldPrice::where('gold_id', $request->gold_bar_id)
            ->whereDate('public_date', '<=', $request->purchase_date)
            ->orderBy('public_date', 'desc')
            ->first();

        if (!$priceRecord) {
            return back()->withErrors(['purchase_date' => 'К сожалению, у нас нет данных о ценах до указанной даты.']);
        }

        $purchasePrice = $priceRecord->buy_in_kopecks / 100;

        $userGoldBar->update([
            'gold_bar_id' => $request->gold_bar_id,
            'purchase_date' => $request->purchase_date,
            'quantity' => $request->quantity,
            'purchase_price_per_bar' => $purchasePrice,
            'price_date' => $priceRecord->public_date,
            'comment' => $request->comment,
        ]);

        return redirect()->route('my-gold.index')->with('success', 'Запись успешно обновлена!');
    }

    public function destroy(UserGoldBar $userGoldBar)
    {
        if ($userGoldBar->user_id !== Auth::id()) {
            abort(403);
        }

        $userGoldBar->delete();

        return redirect()->route('my-gold.index')->with('success', 'Запись успешно удалена!');
    }
}
