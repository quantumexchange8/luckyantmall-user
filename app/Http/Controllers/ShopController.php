<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class ShopController extends Controller
{
    public function index()
    {

    }

    public function product_detail($slug, $id)
    {
        $product = Product::with([
            'media',
            'price_bundles'
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

        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
            ]);
        }

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'price_per_unit' => $request->price_per_unit,
            'total_price' => $request->total_price,
        ]);

        return Redirect::route('cart');
    }
}
