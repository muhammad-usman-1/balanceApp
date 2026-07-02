<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubcrption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->get('year', now()->year);

        // Monthly breakdown: total, cash, online
        $monthly = UserSubcrption::selectRaw("
                MONTH(created_at)                                              AS month,
                COUNT(*)                                                       AS total_count,
                SUM(price)                                                     AS total_income,
                SUM(CASE WHEN payment_gateway = 'cash' THEN price ELSE 0 END) AS cash_income,
                SUM(CASE WHEN payment_gateway != 'cash' AND payment_gateway IS NOT NULL AND payment_gateway != '' THEN price ELSE 0 END) AS card_income,
                SUM(CASE WHEN payment = 'paid'    THEN 1 ELSE 0 END)          AS paid_count,
                SUM(CASE WHEN payment = 'pending' THEN 1 ELSE 0 END)          AS pending_count
            ")
            ->where('payment', 'paid')
            ->whereYear('created_at', $year)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->keyBy('month');

        // Plan breakdown for selected year
        $byPlan = UserSubcrption::selectRaw("
                subcrption_plans_id,
                COUNT(*)    AS total_count,
                SUM(price)  AS total_income,
                SUM(CASE WHEN payment_gateway = 'cash' THEN price ELSE 0 END) AS cash_income,
                SUM(CASE WHEN payment_gateway != 'cash' AND payment_gateway IS NOT NULL AND payment_gateway != '' THEN price ELSE 0 END) AS card_income
            ")
            ->with('subcrption_plans:id,title')
            ->where('payment', 'paid')
            ->whereYear('created_at', $year)
            ->groupBy('subcrption_plans_id')
            ->orderByRaw('total_income DESC')
            ->get();

        // Totals for the year
        $totals = [
            'income' => $monthly->sum('total_income'),
            'cash'   => $monthly->sum('cash_income'),
            'card'   => $monthly->sum('card_income'),
            'count'  => $monthly->sum('total_count'),
        ];

        // Available years for filter
        $years = UserSubcrption::selectRaw('YEAR(created_at) AS yr')
            ->distinct()
            ->orderBy('yr', 'desc')
            ->pluck('yr');

        return view('admin.reports.index', compact('monthly', 'byPlan', 'totals', 'year', 'years'));
    }

    public function sales(Request $request)
    {
        $search    = trim($request->get('search', ''));
        $gateway   = $request->get('gateway', '');
        $dateFrom  = $request->get('date_from', '');
        $dateTo    = $request->get('date_to', '');

        $query = UserSubcrption::with([
                'user:id,name,mobile',
                'subcrption_plans:id,title,meal_count,snack_count',
            ])
            ->where('payment', 'paid')
            ->when($gateway, fn($q) => $q->where('payment_gateway', $gateway))
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('payment_reference', 'like', '%'.$search.'%')
                       ->orWhereHas('user', fn($q3) => $q3->where('name', 'like', '%'.$search.'%')
                           ->orWhere('mobile', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('created_at', 'desc');

        $sales = $query->paginate(25)->withQueryString();

        $gateways = UserSubcrption::where('payment', 'paid')
            ->whereNotNull('payment_gateway')
            ->where('payment_gateway', '!=', '')
            ->distinct()
            ->pluck('payment_gateway');

        $totalAmount = $query->sum('price');

        return view('admin.reports.sales', compact('sales', 'search', 'gateway', 'dateFrom', 'dateTo', 'gateways', 'totalAmount'));
    }
}
