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

        $now = Carbon::now();
        $nextMonth = $now->copy()->addMonth();
        $targetDate = $nextMonth->startOfMonth()->addDays(9);
        $remainDate = $targetDate->diffInDays($now);

        $totalSpending = $spending->sum('amount'); 
        $totalIncome = $income->sum('amount');
        $total = ($totalIncome - $totalSpending);
        $totalPerDay = $total / $remainDate;

        return response()->json([
            'data' => [
                'totalSpending' => number_format($totalSpending, 0),
                'totalIncome' => number_format($totalIncome, 0),
                'totalBalance' => number_format($total, 0),
                'totalPerDay' => number_format($totalPerDay, 0)
            ]
        ]);
    }

    public function spendingByDate(Request $request)
    {
        if ($request->has('date')) {
            $today = Carbon::parse($request->date);
        } else {
            $today = Carbon::today();
        }
        $yesterday = $today->copy()->subDay();

        $todaySpending = Spending::select(DB::raw('SUM(amount) as total_amount'))
            ->whereDate('date', $today)
            ->when(auth()->user()->role !== 1, function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->first();

        $yesterdaySpending = Spending::select(DB::raw('SUM(amount) as total_amount'))
            ->whereDate('date', $yesterday)
            ->when(auth()->user()->role !== 1, function ($query) {
                return $query->where('user_id', auth()->id());
            })
            ->first();

        $todayAmount = $todaySpending ? $todaySpending->total_amount : 0;
        $yesterdayAmount = $yesterdaySpending ? $yesterdaySpending->total_amount : 0;
        $difference = $todayAmount - $yesterdayAmount;
        $percentageChange = $yesterdayAmount != 0 ? (($difference / $yesterdayAmount) * 100) : 0;

        return response()->json([
            'data' => [
                'today' => [
                    'date' => $today->format('Y-m-d'),
                    'amount' => number_format($todayAmount, 0)
                ],
                'yesterday' => [
                    'date' => $yesterday->format('Y-m-d'),
                    'amount' => number_format($yesterdayAmount, 0)
                ],
                'difference' => [
                    'amount' => number_format($difference, 0),
                    'percentage' => number_format($percentageChange, 2)
                ]
            ]
        ]);
    }
} 

