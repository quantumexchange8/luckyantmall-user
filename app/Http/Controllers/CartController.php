<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::withCount('cart_items')
            ->where('user_id', Auth::id())
            ->first();

        return Inertia::render('Cart/ShoppingCart', [
            'cart' => $cart,
        ]);
    }

    public function getCartItems(Request $request)
    {
        $cartItems = CartItem::where('cart_id', $request->cartId)
            ->with([
                'product:id,name,base_price',
                'product.media',
                'product.price_bundles'
            ])
            ->latest()
            ->get();

        return response()->json([
            'cart_items' => $cartItems,
        ]);
    }
}
