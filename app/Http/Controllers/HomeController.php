<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Home/HomeScreen');
    }

    public function getPopularProducts()
    {
        $query = Product::with(['media']);

        if (!auth()->check()) {
            $query->where('is_auth_visible', 1);
        }

        $products = $query->get();

        return response()->json([
            'products' => $products,
        ]);
    }
}
