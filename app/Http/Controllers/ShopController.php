<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class ShopController extends Controller
{
    public function index()
    {
        $categoriesData = app(SelectOptionController::class)
            ->getCategories()
            ->getData();

        return Inertia::render('Product/ProductListing', [
            'productsCount' => Product::count(),
            'categories' => $categoriesData
        ]);
    }

    public function getProducts(Request $request)
    {
        $limit = $request->input('limit', 12);

        $search = $request->input('search', '');
        $sortType = $request->input('sortType');
        $categories = $request->input('categories');

        $query = Product::query()
            ->with([
                'category',
                'media'
            ]);

        if (!empty($search)) {
            $keyword = '%' . $search . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', $keyword)
                    ->orWhere('descriptions', 'like', $keyword);
            });
        }

        if ($categories) {
            $slugs = explode(',', $categories);
            $query->whereHas('category', function ($q) use ($slugs) {
                $q->whereIn('slug', $slugs);
            });
        }

        if (!auth()->check()) {
            $query->where('is_auth_visible', 1);
        }

        $sortType = json_decode($request->get('sortType'), true);
        if (!empty($sortType)) {
            $field = $sortType['field'] ?? 'created_at';
            $direction = $sortType['direction'] ?? 'desc';

            $query->orderBy($field, $direction);
        }

        $products = $query->paginate($limit);

        return response()->json([
            'products' => $products,
        ]);
    }

    public function product_detail($slug, $id)
    {
        $product = Product::with([
            'media',
            'price_bundles',
            'masters',
            'category'
        ])->find($id);

        return Inertia::render('Product/ProductDetail', [
            'product' => $product
        ]);
    }

    public function addToCart(Request $request)
    {
        Validator::make($request->all(), [
            'quantity' => ['required'],
        ])->setAttributeNames([
            'quantity' => trans('public.quantity'),
        ])->validate();

        $product = Product::find($request->product_id);
        if (($product->masters)->isNotEmpty() && !$request->master_id) {
            throw ValidationException::withMessages(['master_id' => trans('validation.required', ['attribute' => trans('public.investment_plan')])]);
        }

        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
            ]);
        }

        $action = $request->action;

        CartItem::create([
            'cart_id' => $cart->id,
            'type' => $action,
            'product_id' => $request->product_id,
            'trading_master_id' => $request->master_id,
            'quantity' => $request->quantity,
            'price_per_unit' => $request->price_per_unit,
            'total_price' => $request->total_price,
        ]);

        if ($action == 'add_to_cart') {
            return Redirect::route('cart');
        } else {
            return Redirect::route('cart.checkout');
        }
    }
}
