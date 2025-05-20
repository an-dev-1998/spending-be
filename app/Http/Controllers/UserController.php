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
        // Only admin can get all users
        if (auth()->user()->role !== 1) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }
        
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
            'role' => 'required|integer|in:1,2',
            'image_url' => 'nullable|string',
        ]);

        // Only admin can create admin users
        if ($validated['role'] === 1 && auth()->user()->role !== 1) {
            return response()->json([
                'message' => 'Unauthorized to create admin user'
            ], 403);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'image_url' => $validated['image_url'],
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], 200);
    }

    public function updateUser(Request $request)
    {
        $user = User::where('id', $request->id)->first();
        
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Only admin can update other users
        if (auth()->user()->role !== 1 && auth()->user()->id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized to update this user'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|integer|in:1,2',
            'image_url' => 'nullable|string',
        ]);

        // Only admin can change roles
        // if (isset($request->role) && auth()->user()->role !== 1) {
        //     return response()->json([
        //         'message' => 'Unauthorized to change user role'
        //     ], 403);
        // }

        $user->update($validated);
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

        // Only admin can delete users
        if (auth()->user()->role !== 1) {
            return response()->json([
                'message' => 'Unauthorized to delete users'
            ], 403);
        }

        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully',
        ], 200);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Store the file in the public disk
            $path = $file->storeAs('uploads', $filename, 'public');
            
            // Generate the public URL for the file
            $url = asset('storage/' . $path);

            return response()->json([
                'message' => 'File uploaded successfully',
                'url' => $url,
                'path' => $path
            ], 200);
        }

        return response()->json([
            'message' => 'No file uploaded'
        ], 400);
    }
}
