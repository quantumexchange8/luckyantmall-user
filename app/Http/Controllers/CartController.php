<?php

namespace App\Http\Controllers;

use App\Enums\MetaService;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CurrencyConversionRate;
use App\Models\DeliveryAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TradingAccount;
use App\Models\TradingMaster;
use App\Models\TradingSubscription;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\MetaFiveService;
use App\Services\RunningNumberService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = Cart::withCount('cart_items')
            ->where('user_id', Auth::id())
            ->first();

        CartItem::where('cart_id', $cart->id)
            ->where('type', 'buy_now')
            ->delete();

        return Inertia::render('Cart/ShoppingCart', [
            'cart' => $cart,
        ]);
    }

    public function getCartItems(Request $request)
    {
        $cartItems = CartItem::where('cart_id', $request->cartId)
            ->where('type', 'add_to_cart')
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

    public function checkout(Request $request)
    {
        $action = $request->action;
        $cart = Cart::firstWhere('user_id', Auth::id());
        $query = CartItem::with([
            'product:id,name,base_price',
            'product.media',
            'product.price_bundles'
        ])
            ->where('cart_id', $cart->id)
            ->orderByDesc('created_at');

        if ($action == 'buy_now') {
            $cartItems = $query->where('type', 'buy_now')
                ->limit(1)
                ->get();
        } else {
            $cartItems = $query->get();
        }

        if ($cartItems->isEmpty()) {
            return to_route('home');
        }

        $default_address = DeliveryAddress::where('user_id', Auth::id())
            ->where('default_address', 1)
            ->first();

        $wallet = Wallet::where('user_id', Auth::id())
            ->where('type', 'e_wallet')
            ->first();

        return Inertia::render('Cart/Checkout/Checkout', [
            'cartItems' => $cartItems,
            'default_address' => $default_address,
            'wallet' => $wallet,
            'backRoute' => $cartItems[0]->type == 'add_to_cart' ? 'cart' : 'home',
        ]);
    }

    public function proceedCheckout(Request $request)
    {
        $cart_items = $request->cart_items;
        if (empty($cart_items)) {
            throw ValidationException::withMessages(['cart_items' => trans('public.please_select_one_item')]);
        }

        foreach ($cart_items as $cart_item) {
            $item = CartItem::find($cart_item['id']);
            $item->update([
                'quantity' => $cart_item['quantity'],
                'price_per_unit' => $cart_item['price_per_unit'],
                'total_price' => $cart_item['total_price'],
            ]);
        }

        return Redirect::route('cart.checkout');
    }

    public function confirmPayment(Request $request)
    {
        Validator::make($request->all(), [
            'delivery_address_id' => ['nullable'],
            'wallet_id' => ['required'],
            'security_pin' => ['required'],
            'cart_items' => ['required'],
        ])->setAttributeNames([
            'security_pin' => trans('public.security_pin'
        )])->validate();

        $cart_items = collect($request->cart_items);
        $card_ids = $cart_items->pluck('id')->toArray();

        $total_price = CartItem::whereIn('id', $card_ids)->sum('total_price');
        $wallet = Wallet::find($request->wallet_id);

        if ($total_price > $wallet->balance) {
            throw ValidationException::withMessages(['wallet_id' => trans('public.insufficient_balance')]);
        }

        $delivery_address = DeliveryAddress::find($request->delivery_address_id);

        // Create order
        $order = Order::create([
            'user_id' => Auth::id(),
            'order_number' => RunningNumberService::getID('order'),
            'sub_total' => $request->sub_total,
            'delivery_fee' => 0,
            'discount_price' => 0,
            'total_price' => $request->total_price,
            'receiver_name' => optional($delivery_address)->receiver_name,
            'receiver_phone' => optional($delivery_address)->receiver_phone,
            'receiver_address' => optional($delivery_address)->address,
            'postal_code' => optional($delivery_address)->postal_code,
            'country_id' => optional($delivery_address)->country_id,
        ]);

        $all_items_no_delivery = true;

        $cart_items->each(function ($cart_item) use ($order, &$all_items_no_delivery) {
            $product_item = CartItem::with('product:id,required_delivery')
                ->find($cart_item['id']);

            $order_item = OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product_item->product_id,
                'price_per_unit' => $product_item->price_per_unit,
                'quantity' => $product_item->quantity,
                'status' => $product_item->product->required_delivery ? 'processing' : 'completed',
                'delivered_at' => $product_item->product->required_delivery ? null : now(),
            ]);

            if ($order_item->status == 'completed') {
                $product = Product::find($product_item->product_id);
                $product->decrement('quantity', $order_item->quantity);
            }

            $all_items_no_delivery = $all_items_no_delivery && !$product_item->product->required_delivery;

            if ($product_item->trading_master_id) {
                $order_item->update(['trading_master_id' => $product_item->trading_master_id]);
                $this->checkTradingAccount($product_item);
            }

            // Delete cart item
            $product_item->delete();
        });

        if ($all_items_no_delivery) {
            $order->update(['completed_at' => now(), 'status' => 'completed']);
        }

        $transaction = $this->recordTransaction(Auth::user(), $wallet, $total_price);
        $order->update(['transaction_id' => $transaction->id]);

        return Redirect::route('profile')->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_payment_success'),
            'type' => 'success',
        ]);
    }

    private function checkTradingAccount($item): void
    {
        $user = Auth::user();
        $metaService = new MetaFiveService();
        $e_wallet = Wallet::firstWhere(['user_id' => $user->id, 'type' => 'e_wallet']);
        $master = TradingMaster::find($item->trading_master_id);
        $subscription = TradingSubscription::where([
            'user_id' => $user->id,
            'master_meta_login' => $master->meta_login,
            'status' => 'active'
        ])->first();

        $trading_account = $subscription
            ? TradingAccount::firstWhere('meta_login', $subscription->meta_login)
            : $this->createNewTradingAccount($metaService, $user);;

        // Convert CNY to USD
        $original_price = $item->total_price / 2;
        $currency_rate = CurrencyConversionRate::firstWhere('base_currency', $e_wallet->currency);
        $subscription_amount = $original_price / $currency_rate->deposit_rate;

        $this->createTradingDeal($metaService, $trading_account, $subscription_amount, $original_price, $currency_rate, $item->product->id);

        $this->createTradingSubscription($metaService, $user, $trading_account, $master->meta_login, $subscription_amount);
    }

    private function recordTransaction($user, $wallet, $amount)
    {
        $new_wallet_balance = $wallet->balance - $amount;

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'category' => 'e_wallet',
            'transaction_type' => 'payment',
            'from_wallet_id' => $wallet->id,
            'transaction_number' => RunningNumberService::getID('transaction'),
            'amount' => $amount,
            'from_currency' => $wallet->currency,
            'to_currency' => $wallet->currency,
            'transaction_amount' => $amount,
            'fund_type' => $user->role === 'user' ? MetaService::REAL_FUND : MetaService::DEMO_FUND,
            'old_wallet_amount' => $wallet->balance,
            'new_wallet_amount' => $new_wallet_balance,
            'status' => 'success',
            'approval_at' => now(),
        ]);

        $wallet->update(['balance' => $new_wallet_balance]);

        return $transaction;
    }

    private function createTradingSubscription($metaService, $user, $trading_account, $master_meta_login, $subscription_amount)
    {
        $master = TradingMaster::firstWhere('meta_login', $master_meta_login);

        TradingSubscription::create([
            'user_id' => $user->id,
            'meta_login' => $trading_account->meta_login,
            'master_meta_login' => $master_meta_login,
            'type' => $master->type,
            'subscription_amount' => $subscription_amount,
            'subscription_number' => RunningNumberService::getID('subscription'),
            'subscription_period_type' => $master->join_period_type,
            'subscription_period' => $master->join_period,
            'settlement_period_type' => $master->settlement_period_type,
            'settlement_period' => $master->settlement_period,
            'approval_at' => now(),
            'status' => 'active',
            'remarks' => 'China PAMM',
            'extra_conditions' => 'NOLOT',
            'expired_at' => $this->calculatePeriod($master->join_period_type, $master->join_period),
            'settlement_at' => $this->calculatePeriod($master->settlement_period_type, $master->settlement_period),
            'real_fund' => $user->role == 'user' ? $subscription_amount : null,
            'demo_fund' => $user->role != 'user' ? $subscription_amount : null,
        ]);

        $metaService->disableTrade($trading_account->meta_login);

        $metaService->createDeal($master_meta_login, $subscription_amount, 'Deposit #' . $trading_account->meta_login, MetaService::DEPOSIT);

        $master->increment('total_fund', $subscription_amount);
    }

    private function createTradingDeal($metaService, $trading_account, $amount, $original_price, $currency_rate, $product_id)
    {
        $deal = $metaService->createDeal($trading_account->meta_login, $amount, 'Deposit', MetaService::DEPOSIT);

        Transaction::create([
            'user_id' => Auth::id(),
            'category' => 'trading_account',
            'transaction_type' => 'balance_in',
            'to_meta_login' => $trading_account->meta_login,
            'transaction_number' => RunningNumberService::getID('transaction'),
            'ticket' => $deal['deal_Id'] ?? null,
            'amount' => $original_price,
            'from_currency' => 'CNY',
            'to_currency' => $currency_rate->target_currency,
            'conversion_rate' => $currency_rate->deposit_rate,
            'conversion_amount' => $amount,
            'transaction_amount' => $amount,
            'fund_type' => Auth::user()->role == 'user' ? MetaService::REAL_FUND : MetaService::DEMO_FUND,
            'comment' => 'Payment Product ID - ' . $product_id,
            'status' => 'success',
            'approval_at' => now(),
        ]);
    }

    private function createNewTradingAccount($metaService, $user)
    {
        $meta_account = $metaService->createUser($user, 'JS', 500, $user->email);
        return TradingAccount::where('meta_login', $meta_account['login'])->first();
    }

    private function calculatePeriod($type, $period)
    {
        return match ($type) {
            'day' => Carbon::now()->addDays($period),
            'month' => Carbon::now()->addMonths($period),
            'year' => Carbon::now()->addYears($period),
            default => null,
        };
    }

    public function deleteItem(Request $request)
    {
        CartItem::find($request->item_id)->delete();
    }
}
