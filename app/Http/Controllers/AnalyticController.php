<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Spending;
use App\Models\Income;
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
    public function spendingAnalytics(Request $request)
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

    public function incomeAnalytics(Request $request)
    {
        $query = Income::select('category_id', DB::raw('SUM(amount) as total_amount'))
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

        $incomes = $query->get();

        return response()->json([
            'data' => $incomes
        ]);
    }

    public function totalAnalytics(Request $request)
    {
        $spending = Spending::select('amount');

        if ($request->has('start_date') && $request->has('end_date')) {
            $spending->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $income = Income::select('amount');

        if ($request->has('start_date') && $request->has('end_date')) {
            $income->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $totalSpending = $spending->sum('amount'); 
        $totalIncome = $income->sum('amount');
        $total = $totalIncome - $totalSpending;

        return response()->json([
            'data' => [
                'totalSpending' => $totalSpending,
                'totalIncome' => $totalIncome,
                'totalBalance' => $total
            ]
        ]);
    }
} 

