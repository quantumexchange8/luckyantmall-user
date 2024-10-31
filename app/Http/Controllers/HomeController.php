<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Home/HomeScreen');
    }

    public function getPopularProducts()
    {
        $products = Product::with(['media'])->get();

        return response()->json([
            'products' => $products,
        ]);
    }
}
