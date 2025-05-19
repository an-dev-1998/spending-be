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
    public function spendingByCategory(Request $request)
    {
        $query = Spending::select('category_id', DB::raw('SUM(amount) as total_amount'))
            ->with('category');

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $query->groupBy('category_id');

        if (auth()->user()->role === 1) {
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', auth()->id());
        }

        $spendings = $query->get();

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

        $query = Spending::select(
            DB::raw('DATE_FORMAT(date, "%Y-%m") as period'),
            DB::raw('SUM(amount) as total_amount')
        )
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period');

        if (auth()->user()->role === 1) {
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', auth()->id());
        }

        $spendings = $query->get();

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

        $query = Spending::select('category_id', DB::raw('SUM(amount) as total_amount'))
            ->with('category')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('category_id')
            ->orderByDesc('total_amount')
            ->limit($limit);

        if (auth()->user()->role === 1) {
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', auth()->id());
        }

        $topCategories = $query->get();

        return response()->json([
            'data' => $topCategories
        ]);
    }

    /**
     * Get spending summary
     *
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        $query = Spending::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if (auth()->user()->role === 1) {
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', auth()->id());
        }

        $totalSpent = $query->sum('amount');
        $averageSpent = $query->avg('amount');
        $totalTransactions = $query->count();
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