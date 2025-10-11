<?php

namespace App\Http\Controllers\public;

use App\Http\Controllers\Controller;
use App\Models\GoldPrice;
use App\Models\Gold;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        // 1. Получаем все типы золотых слитков
        $golds = Gold::orderBy('weight_units', 'asc')->get();

        // 2. Получаем последние цены для каждого слитка (для калькулятора и приветствия)
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

        // 3. Получаем пагинированный список всех цен (для нижней таблицы)
        $allPrices = GoldPrice::with('gold')
            ->orderBy('public_date', 'desc')
            ->orderBy('gold_id', 'asc')
            ->paginate(30); // Пагинация для удобства

        return view('public.index', compact('golds', 'latestPrices', 'allPrices', 'latestPublicDate'));
    }


    public function countUser(Request $request)
    {
        return response(User::count());
    }

    public function createAdmin(Request $request)
    {
        if (!Role::where('name', 'admin')->exists()) {
            $role = new Role();
            $role->name = 'admin';
            $role->description = 'admin';
            $role->save();
        }
        $user_role = new UserRole();
        $user_role->user_id = 1;
        $user_role->role_id = 1;
        $user_role->GET=true;
        $user_role->POST=true;
        $user_role->PUT=true;
        $user_role->DELETE=true;
        $user_role->PATCH=true;
        $user_role->save();
        return 'ok';
    }
}
