<?php

namespace App\Http\Controllers;

use App\Models\TradeHistory;
use App\Models\TradeRebateSummary;
use App\Models\TradingSubscription;
use App\Models\Transaction;
use Auth;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Str;

class DashboardController extends Controller
{
    public function index()
    {
        $transactionQuery = Transaction::query()
            ->where([
                'user_id' => Auth::id(),
                'status' => 'success'
            ]);

        $totalDeposit = (clone $transactionQuery)
            ->where('transaction_type', 'deposit')
            ->sum('transaction_amount');

        $totalWithdrawal = (clone $transactionQuery)
            ->where('transaction_type', 'withdrawal')
            ->sum('transaction_amount');

        $capitalFund = TradingSubscription::where([
            'user_id' => Auth::id(),
            'status' => 'active'
        ])->sum('subscription_amount');

        $tradeProfit = TradeHistory::where('user_id', Auth::id())
            ->sum('trade_profit');

        $totalRebate = TradeRebateSummary::where('upline_user_id', Auth::id())
            ->sum('rebate');

        return Inertia::render('Dashboard/Dashboard', [
            'totalDeposit' => (float) $totalDeposit,
            'totalWithdrawal' => (float) $totalWithdrawal,
            'capitalFund' => (float) $capitalFund,
            'tradeProfit' => (float) $tradeProfit,
            'totalRebate' => (float) $totalRebate,
        ]);
    }

    public function getTotalProfitByDays(Request $request)
    {
        $query = TradeHistory::query()
            ->where('user_id', Auth::id());

        $month = (int) $request->input('month', date('m'));
        $year = (int) $request->input('year', date('Y'));
        $days = (int) $request->input('days', cal_days_in_month(CAL_GREGORIAN, $month, $year));

        if ($days <= 14) {
            $endDate = Carbon::now()->endOfDay();
            $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
            $labels = collect(range(0, $days - 1))->map(fn($i) => Carbon::now()->subDays($days - 1 - $i)->format('Y-m-d'))->toArray();
        } else {
            $startDate = Carbon::create($year, $month, 1)->startOfDay();
            $endDate = Carbon::create($year, $month, date('d'))->endOfDay();
            $labels = collect(range(1, date('d')))
                ->map(fn($day) => Carbon::create($year, $month, $day)->format('Y-m-d'))
                ->toArray();
        }

        $chartResults = $query
            ->whereBetween('time_close', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(time_close) as date'),
                DB::raw('SUM(trade_profit) as profit')
            )
            ->groupBy('date')
            ->pluck('profit', 'date');

        $profitData = array_map(function ($date) use ($chartResults) {
            return $chartResults[$date] ?? 0;
        }, $labels);

        $chartData = [
            'labels' => array_map(fn($date) => Carbon::parse($date)->format('j'), $labels),
            'datasets' => [
                [
                    'label' => trans('public.profit'),
                    'data' => $profitData,
                    'borderColor' => '#0EA5E9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.3)',
                    'borderWidth' => 2,
                    'pointStyle' => false,
                    'fill' => true,
                    'tension' => 0.4,
                ]
            ]
        ];

        return response()->json([
            'chartData' => $chartData,
        ]);
    }

    public function getTotalRebateByYears(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));

        $monthlyData = TradeRebateSummary::query()
            ->where('upline_user_id', Auth::id())
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(rebate) as amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $labels = collect(range(1, 12))->map(function ($month) {
            return Str::lower(Carbon::create(null, $month, 1)->format('F'));
        })->toArray();

        $translatedLabels = array_map(function ($label) {
            return trans("public.$label");
        }, $labels);

        $data = collect(range(1, 12))->map(function ($month) use ($monthlyData) {
            return $monthlyData[$month]->amount ?? 0;
        })->toArray();

        $chartData = [
            'labels' => $translatedLabels,
            'datasets' => [
                [
                    'label' => trans('public.amount'),
                    'data' => $data,
                    'backgroundColor' => 'rgb(18, 183, 106)',
                    'borderColor' => '#12B76A',
                    'borderWidth' => 2,
                    'fill' => true,
                    'borderRadius' => 8,
                ],
            ],
        ];

        return response()->json([
            'chartData' => $chartData,
        ]);
    }
}
