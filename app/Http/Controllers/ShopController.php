<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\TradingMasterToGroup;
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

        if (!$product->is_auth_visible && !auth()->check()) {
            return to_route('login');
        }

        $user = Auth::user();

        $masterIds = TradingMasterToGroup::where('group_id', $user?->group->group_id)
            ->pluck('trading_master_id');

        $masters = $product->masters->whereIn('id', $masterIds)->toArray();

        return Inertia::render('Product/ProductDetail', [
            'product' => $product,
            'masters' => $masters
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

        $cart = Cart::firstWhere('user_id', Auth::id());
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
            ]);
        }

        CartItem::where('cart_id', $cart->id)
            ->where('type', 'buy_now')
            ->delete();

        $action = $request->action;

        $exist_item = CartItem::where([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
        ])
            ->first();

        if ($action == 'add_to_cart' && $exist_item) {
            $exist_item->increment('quantity', $request->quantity);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'type' => $action,
                'product_id' => $request->product_id,
                'trading_master_id' => $request->master_id,
                'quantity' => $request->quantity,
                'price_per_unit' => $request->price_per_unit,
                'total_price' => $request->total_price,
            ]);
        }

        if ($action == 'add_to_cart') {
            return to_route('shop')->with('toast', [
                'title' => 'success',
                'message' => trans('public.toast_added_item_success'),
                'type' => 'success',
            ]);
        } else {
            return Redirect::route('cart.checkout', [
                'action' => $action,
            ]);
        }
    }
}
