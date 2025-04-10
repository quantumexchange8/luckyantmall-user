<?php

namespace App\Http\Controllers;

use App\Exports\TradeHistoryExport;
use App\Models\TradeHistory;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function trade_history()
    {
        return Inertia::render('Trade/TradeHistory');
    }

    public function getTradeHistoriesData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $trade_type = $data['filters']['type']['value'];

            $query = TradeHistory::where('user_id', Auth::id());

            if ($trade_type !== 'all_trades') {
                $query->where('trade_type', $trade_type);
            }

            $todayProfit = (clone $query)
                ->whereDate('time_close', Carbon::today())
                ->sum('trade_profit');

            if (!empty($data['filters']['start_date']['value']) && !empty($data['filters']['end_date']['value'])) {
                $start_date = Carbon::parse($data['filters']['start_date']['value'])->addDay()->startOfDay();
                $end_date = Carbon::parse($data['filters']['end_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('time_close', [$start_date, $end_date]);
            }

            if (!empty($data['filters']['symbols']['value'])) {
                $symbols = $data['filters']['symbols']['value'];
                $query->whereIn('symbol', $symbols);
            }

            //sort field/order
            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('time_close');
            }

            $totalProfit = (clone $query)->sum('trade_profit');
            $totalTradeLot = (clone $query)->sum('volume');

            // Export logic
            if ($request->has('exportStatus') && $request->exportStatus) {
                return Excel::download(new TradeHistoryExport($query), now() . '-trade-history.xlsx');
            }

            $tradeHistories = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $tradeHistories,
                'todayProfit' => $todayProfit,
                'totalProfit' => $totalProfit,
                'totalTradeLot' => $totalTradeLot,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }
}
