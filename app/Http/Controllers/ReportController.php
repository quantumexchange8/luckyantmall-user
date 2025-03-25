<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class ReportController extends Controller
{
    public function trade_history()
    {
        return Inertia::render('Trade/TradeHistory');
    }
}
