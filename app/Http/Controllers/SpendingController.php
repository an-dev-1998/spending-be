<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Spending;
use App\Models\Category;

class SpendingController extends Controller
{
    /**
     * Display a listing of the spendings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->user()->role === 1) {
            // Admin can view all spendings
            $spendings = Spending::with('category', 'user')->get();
        } else {
            // Regular users can only view their own spendings
            $spendings = Spending::with('category')
                ->where('user_id', auth()->id())
                ->get();
        }
        
        return response()->json([
            'data' => $spendings,
        ], 200);
    }

    /**
     * Store a newly created spending in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['description'] = $request->description ?? '-';
        $spending = Spending::create($validated);
        return response()->json($spending->load('category'), 201);
    }

    /**
     * Display the specified spending.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $spending = Spending::with('category')
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        return response()->json($spending);
    }

    /**
     * Update the specified spending in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $spending = Spending::where('user_id', auth()->id())
            ->findOrFail($id);
        
        $validated = $request->validate([
            'amount' => 'sometimes|required|numeric',
            'description' => 'nullable|string',
            'date' => 'sometimes|required|date',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        if ($request->has('description')) {
            $validated['description'] = $request->description;
        } else {
            $validated['description'] = '-';
        }

        $spending->update($validated);
        return response()->json($spending->load('category'));
    }

    /**
     * Remove the specified spending from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $spending = Spending::where('user_id', auth()->id())
            ->findOrFail($id);
        $spending->delete();
        return response()->json(null, 204);
    }
} 