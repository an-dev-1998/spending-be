<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUser()
    {
        return response()->json(auth()->user());
    }

    public function getUsers()
    {
        $users = User::where('role', 2)->get();
        return response()->json([
            'data' => $users,
        ], 200);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        $user->update($request->all());
        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ], 200);
    }

    public function deleteUser(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], 404);
        }
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }
}
