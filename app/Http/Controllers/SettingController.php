<?php

namespace App\Http\Controllers;

use App\Models\DeliveryAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function delivery_address()
    {
        return Inertia::render('Setting/DeliveryAddress/DeliveryAddress', [
            'addressCounts' => DeliveryAddress::count(),
        ]);
    }

    public function getDeliveryAddress()
    {
        $addresses = DeliveryAddress::with('country:id,name,translations')
            ->where('user_id', Auth::id())
            ->orderByDesc('default_address')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'addresses' => $addresses,
        ]);
    }

    public function addDeliveryAddress(Request $request)
    {
        Validator::make($request->all(), [
            'receiver_name' => ['required'],
            'receiver_phone' => ['required'],
            'address' => ['required'],
            'postal_code' => ['required'],
            'country_id' => ['required'],
        ])->setAttributeNames([
            'receiver_name' => trans('public.receiver_name'),
            'receiver_phone' => trans('public.phone_number'),
            'address' => trans('public.full_address'),
            'postal_code' => trans('public.postal_code'),
            'country_id' => trans('public.country'),
        ])->validate();

        $defaultAddress = (bool)$request->default_address;

        if ($defaultAddress) {
            DeliveryAddress::where('user_id', auth()->id())
                ->where('default_address', 1)
                ->update(['default_address' => 0]);
        }

        DeliveryAddress::create([
            'user_id' => auth()->id(),
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'country_id' => $request->country_id,
            'default_address' => $defaultAddress,
        ]);

        return back()->with('toast', [
            'title' => trans('public.success'),
            'message' => trans('public.toast_add_delivery_address_success'),
            'type' => 'success',
        ]);
    }
}
