<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Spending;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticController extends Controller
{
    /**
     * Get spending statistics by category
     *
     * @return \Illuminate\Http\Response
     */
    public function spendingByCategory()
    {
        $spendings = Spending::select('category_id', DB::raw('SUM(amount) as total_amount'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        return response()->json([
            'data' => $spendings
        ]);
    }

    /**
     * Get spending trends over time
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function spendingTrends(Request $request)
    {
        $period = $request->get('period', 'month'); // month, week, year
        $startDate = $request->get('start_date', Carbon::now()->subMonths(6));
        $endDate = $request->get('end_date', Carbon::now());

        $spendings = Spending::select(
            DB::raw('DATE_FORMAT(date, "%Y-%m") as period'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json([
            'data' => $spendings
        ]);
    }

    /**
     * Get top spending categories
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function topCategories(Request $request)
    {
        $limit = $request->get('limit', 5);
        $startDate = $request->get('start_date', Carbon::now()->subMonths(1));
        $endDate = $request->get('end_date', Carbon::now());

        $topCategories = Spending::select('category_id', DB::raw('SUM(amount) as total_amount'))
            ->with('category')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('category_id')
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $topCategories
        ]);
    }

    /**
     * Get spending summary
     *
     * @return \Illuminate\Http\Response
     */
    public function summary()
    {
        $totalSpent = Spending::sum('amount');
        $averageSpent = Spending::avg('amount');
        $totalTransactions = Spending::count();
        $categoriesCount = Category::count();

        return response()->json([
            'data' => [
                'total_spent' => $totalSpent,
                'average_spent' => $averageSpent,
                'total_transactions' => $totalTransactions,
                'categories_count' => $categoriesCount
            ]
        ]);
    }
} 