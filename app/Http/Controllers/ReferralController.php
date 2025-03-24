<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReferralController extends Controller
{
    public function index()
    {
        $children_ids = User::find(Auth::id())->getChildrenIds();
        $hierarchies = User::whereIn('id', $children_ids)->get()->pluck('hierarchyList');

        $levels = [];

        foreach ($hierarchies as $hierarchy) {
            $parts = array_filter(explode('-', trim($hierarchy, '-'))); // Remove empty parts
            $level = count($parts) - 1; // Convert count to level (Level 0 is the first segment)
            $levels[$level] = $level; // Store unique levels
        }

        $levels = array_values($levels);

        return Inertia::render('Referral/Referral', [
            'levels' => $levels
        ]);
    }

    public function getStructureData(Request $request)
    {
        $upline_id = $request->upline_id;
        $parent_id = $request->parent_id ?: Auth::id();

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $parent = User::query()
                ->where(function ($query) use ($search) {
                    $query->where('id_number', 'LIKE', $search)
                        ->orWhere('username', 'LIKE', $search)
                        ->orWhere('email', 'LIKE', $search);
                })
                ->first();

            if (empty($parent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'user not found'
                ]);
            }

            $parent_id = $parent->id;
            $upline_id = $parent->upline_id;
        }

        $parent = User::with([
            'directs' => function ($query) {
                $query->select([
                    'users.*',

                    // Count total downlines
                    DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),

                    // Sum subscription_amount from trading_subscriptions where status is success
                    DB::raw("COALESCE((SELECT SUM(subscription_amount) FROM trading_subscriptions WHERE trading_subscriptions.user_id = users.id AND trading_subscriptions.deleted_at is null AND trading_subscriptions.status = 'active'), 0) as capital_fund_sum"),

                    // Sum total subscription_amount of all downlines
                    DB::raw("COALESCE((SELECT SUM(ts.subscription_amount)
                        FROM trading_subscriptions AS ts
                        JOIN users AS u ON ts.user_id = u.id
                        WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                        AND u.id != users.id
                        AND ts.deleted_at is null
                        AND ts.status = 'active'), 0) as total_downline_capital_fund")
                ]);
            }
        ])
            ->select([
                'users.*',

                // Count total downlines
                DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),

                // Sum subscription_amount from trading_subscriptions where status is active
                DB::raw("COALESCE((SELECT SUM(subscription_amount) FROM trading_subscriptions WHERE trading_subscriptions.user_id = users.id AND trading_subscriptions.deleted_at is null AND trading_subscriptions.status = 'active'), 0) as capital_fund_sum"),

                // Sum total subscription_amount of all downlines
                DB::raw("COALESCE((SELECT SUM(ts.subscription_amount)
                FROM trading_subscriptions AS ts
                JOIN users AS u ON ts.user_id = u.id
                WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                AND u.id != users.id
                AND ts.deleted_at is null
                AND ts.status = 'active'), 0) as total_downline_capital_fund")
            ])
            ->where('id', $parent_id)
            ->first();

        $upline = User::select([
            'users.*',

            // Count total downlines
            DB::raw("(SELECT COUNT(*) FROM users AS u WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')) as total_downlines"),

            // Sum subscription_amount from trading_subscriptions where status is active
            DB::raw("COALESCE((SELECT SUM(subscription_amount) FROM trading_subscriptions WHERE trading_subscriptions.user_id = users.id AND trading_subscriptions.deleted_at is null AND trading_subscriptions.status = 'active'), 0) as capital_fund_sum"),

            // Sum total subscription_amount of all downlines
            DB::raw("COALESCE((SELECT SUM(ts.subscription_amount)
                FROM trading_subscriptions AS ts
                JOIN users AS u ON ts.user_id = u.id
                WHERE u.hierarchyList LIKE CONCAT('%-', users.id, '-%')
                AND u.id != users.id
                AND ts.deleted_at is null
                AND ts.status = 'active'), 0) as total_downline_capital_fund")
        ])
            ->where('id', $upline_id)
            ->first();

        $parent_data = $this->formatUserData($parent);
        $upline_data = $upline ? $this->formatUserData($upline) : null;

        $direct_children = $parent->directs->map(function ($child) {
            return $this->formatUserData($child);
        });

        return response()->json([
            'success' => true,
            'upline' => $upline_data,
            'parent' => $parent_data,
            'direct_children' => $direct_children,
        ]);
    }

    private function formatUserData($user)
    {
        if (!$user) return null;

        if ($user->upline) {
            $upper_upline = $user->upline->upline;
        }

        return array_merge(
            $user->only(['id', 'username', 'id_number', 'upline_id', 'role']),
            [
                'upper_upline_id' => $upper_upline->id ?? null,
                'level' => $user->id === Auth::id() ? 0 : $this->calculateLevel($user->hierarchyList),
                'total_directs' => count($user->directs) ?? 0,
                'total_downlines' => $user->total_downlines ?? 0,
                'capital_fund_sum' => $user->capital_fund_sum ?? 0,
                'total_downline_capital_fund' => $user->total_downline_capital_fund ?? 0
            ]
        );
    }

    private function calculateLevel($hierarchyList)
    {
        if (is_null($hierarchyList) || $hierarchyList === '') {
            return 0;
        }

        $split = explode('-'.Auth::id().'-', $hierarchyList);
        return substr_count($split[1], '-') + 1;
    }

    public function getReferralListingData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);
            $childrenIds = Auth::user()->getChildrenIds();

            $query = User::with([
                'upline',
                'active_trading_subscriptions',
            ])
                ->whereIn('id', $childrenIds)
                ->withSum('active_trading_subscriptions', 'subscription_amount');

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('email', 'like', '%' . $keyword . '%')
                        ->orWhere('username', 'like', '%' . $keyword . '%')
                        ->orWhereHas('upline', function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('username', 'like', '%' . $keyword . '%')
                                ->orWhere('email', 'like', '%' . $keyword . '%');
                        });
                });
            }

            if (!empty($data['filters']['start_join_date']['value']) && !empty($data['filters']['end_join_date']['value'])) {
                $start_join_date = Carbon::parse($data['filters']['start_join_date']['value'])->addDay()->startOfDay();
                $end_join_date = Carbon::parse($data['filters']['end_join_date']['value'])->addDay()->endOfDay();

                $query->whereBetween('created_at', [$start_join_date, $end_join_date]);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $connections = $query->paginate($data['rows']);

            $connections->each(function ($user) {
                $user->level = $this->calculateLevel($user->hierarchyList);
            });

            if (!empty($data['filters']['levels']['value'])) {
                $filterLevels = $data['filters']['levels']['value'];

                $connections->setCollection(
                    $connections->getCollection()->filter(function ($user) use ($filterLevels) {
                        return in_array($user->level, $filterLevels);
                    })
                );
            }

            return response()->json([
                'success' => true,
                'data' => $connections,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }
}
