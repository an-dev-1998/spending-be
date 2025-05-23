<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Income;

class IncomeController extends Controller
{
    /**
     * Display a listing of the incomes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->role === 1) {
            // Admin can view all incomes
            $incomes = Income::with('category', 'user')->get();
        } else {
            // Regular users can only view their own incomes
            $incomes = Income::where('user_id', auth()->id())->with('category', 'user')->get();
        }
        
        return response()->json([
            'data' => $incomes,
        ], 200);
    }

    /**
     * Store a newly created income in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'description' => 'required|string',
            'date' => 'required|date',
            'source' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $validated['user_id'] = auth()->id();
        $income = Income::create($validated);
        return response()->json($income, 201);
    }

    /**
     * Display the specified income.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $income = Income::where('user_id', auth()->id())
            ->with('user', 'category')
            ->findOrFail($id);
        return response()->json($income);
    }

    /**
     * Update the specified income in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $income = Income::where('user_id', auth()->id())
            ->findOrFail($id);
        
        $validated = $request->validate([
            'amount' => 'sometimes|required|numeric',
            'description' => 'sometimes|required|string',
            'date' => 'sometimes|required|date',
            'source' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        $income->update($validated);
        return response()->json($income);
    }

    /**
     * Remove the specified income from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $income = Income::where('user_id', auth()->id())
            ->findOrFail($id);
        $income->delete();
        return response()->json(null, 204);
    }
}
