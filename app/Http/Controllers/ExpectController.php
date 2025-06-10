<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Spending;
use App\Models\Income;
use App\Models\Expect;
use App\Models\Category;
use Carbon\Carbon;

class ExpectController extends Controller
{
    //
    private function calculateTotal() {
        $totalSpending = Spending::sum('amount');
        $totalIncome = Income::sum('amount');
        return ($totalIncome - $totalSpending);
    }

    public function amoutTotal() {
        $total = $this->calculateTotal();

        return response()->json([
            'data' => [
                'total' => $total,
            ]
        ]);
    }

    public function actionExpect() {
        $total = $this->calculateTotal();

        return response()->json([
            'data' => [
                'total' => $total,
            ]
        ]);
    }

    public function actionSpending(Request $request) {
        $monthlyData = $request->all();
        $processedExpectations = [];

        foreach ($monthlyData as $monthData) {
            $month = $monthData['month'];
            $expectations = $monthData['expectations'];

            $date = Carbon::createFromFormat('m/Y', $month)->startOfMonth();

            $existingExpectations = Expect::where('user_id', auth()->user()->id)
                ->where('date', $date)
                ->where('type', 'spending')
                ->get()
                ->keyBy('id');

            $sentIds = collect($expectations)
                ->filter(function($exp) { return isset($exp['id']) && !empty($exp['id']); })
                ->pluck('id')
                ->toArray();

            foreach ($expectations as $expectation) {
                // Validate required fields and safely access category
                if (!isset($expectation['amount']) || !isset($expectation['description'])) {
                    continue; // Skip incomplete data
                }
                
                $category = null;
                if (isset($expectation['category']) && !empty($expectation['category'])) {
                    $category = Category::where('id', $expectation['category'])->first();
                }
                
                if (isset($expectation['id']) && !empty($expectation['id'])) {
                    $expect = $existingExpectations->get($expectation['id']);
                    
                    if ($expect) {
                        $newData = [
                            'amount' => $expectation['amount'],
                            'description' => $expectation['description'],
                            'date' => $date,
                            'category_id' => $category ? $category->id : null,
                        ];
                        
                        $hasChanges = false;
                        foreach ($newData as $key => $value) {
                            if ($expect->$key != $value) {
                                $hasChanges = true;
                                break;
                            }
                        }
                        
                        if ($hasChanges) {
                            $expect->update($newData);
                        }
                    }
                } else {
                    $expect = Expect::create([
                        'user_id' => auth()->user()->id,
                        'amount' => $expectation['amount'],
                        'description' => $expectation['description'],
                        'date' => $date,
                        'category_id' => $category ? $category->id : null,
                        'type' => 'spending',
                    ]);
                }

                if ($expect) {
                    $processedExpectations[] = $expect;
                }
            }

            $expectationsToDelete = $existingExpectations->whereNotIn('id', $sentIds);
            foreach ($expectationsToDelete as $expectationToDelete) {
                $expectationToDelete->delete();
            }
        }

        return response()->json([
            'data' => [
                'expectations' => $processedExpectations,
                'total_processed' => count($processedExpectations),
            ]
        ]);
    }

    public function getActionSpending() {
        $expectations = Expect::where('type', 'spending')
            ->with('category')
            ->get();

        $groupedExpectations = $expectations->groupBy(function ($expectation) {
            return $expectation->date->format('m/Y');
        })->map(function ($group, $month) {
            return [
                'month' => $month,
                'expectations' => $group->map(function ($expectation) {
                    return [
                        'id' => $expectation->id,
                        'category' => $expectation->category ? $expectation->category->name : null,
                        'description' => $expectation->description,
                        'amount' => $expectation->amount,
                        'date' => $expectation->date->format('Y-m-d'),
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'data' => [
                'result' => $groupedExpectations,
            ]
        ]);
    }
}
