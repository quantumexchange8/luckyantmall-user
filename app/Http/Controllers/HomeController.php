<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\SettingBanner;
use Auth;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $categoriesData = app(SelectOptionController::class)
            ->getCategories()
            ->getData();

        $settingBanners = SettingBanner::with('media')
            ->orderBy('position')
            ->get()
            ->map(function ($banner) {
                return [
                    'image_url' => $banner->getFirstMediaUrl('banner_image'),
                    'banner_url' => $banner->banner_url,
                ];
            })->filter(function ($item) {
                return $item['image_url'];
            })->values();


        return Inertia::render('Home/HomeScreen', [
            'categories' => $categoriesData,
            'settingBanners' => $settingBanners,
        ]);
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
