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

    }

    public function product_detail($slug, $id)
    {
        $product = Product::with([
            'media',
            'price_bundles',
            'masters'
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
        if (!empty($product->masters) && !$request->master_id) {
            throw ValidationException::withMessages(['master_id' => trans('validation.required', ['attribute' => trans('public.investment_plan')])]);
        }

        $cart = Cart::where('user_id', Auth::id())->first();
        if (!$cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
            ]);
        }

        $action = $request->action;

        $cart_item  = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $request->product_id,
            'trading_master_id' => $request->master_id,
            'quantity' => $request->quantity,
            'price_per_unit' => $request->price_per_unit,
            'total_price' => $request->total_price,
        ]);
        $cart_item->update(['type' => $action]);

        if ($action == 'add_to_cart') {
            return Redirect::route('cart');
        } else {
            return Redirect::route('cart.checkout');
        }
    }
}
