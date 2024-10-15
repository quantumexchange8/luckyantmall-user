<?php

namespace App\Http\Controllers;

use App\Models\Country;

class SelectOptionController extends Controller
{
    public function getCountries()
    {
        $countries = Country::select('id', 'name', 'phone_code', 'iso2', 'emoji', 'translations')
            ->get();

        return response()->json($countries);
    }
}
