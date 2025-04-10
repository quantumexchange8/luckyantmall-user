<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Models\TradeHistory;
use Auth;

class SelectOptionController extends Controller
{
    public function getCountries()
    {
        $countries = Country::select('id', 'name', 'phone_code', 'iso2', 'emoji', 'translations')
            ->get();

        return response()->json($countries);
    }

    public function getCategories()
    {
        $query = Category::query();

        if (!auth()->check()) {
            $query->where('is_auth_visible', 1);
        }

        $categories = $query->get();

        return response()->json($categories);
    }

    public function getTradeSymbols()
    {
        $symbols = TradeHistory::query()
            ->where('user_id', Auth::id())
            ->select('symbol')
            ->distinct()
            ->get()
            ->pluck('symbol')
            ->toArray();

        return response()->json($symbols);
    }
}
