<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Display a listing of users (Admin only)
     * GET /api/users
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 1000); // Support pagination

            $users = User::select('id', 'name', 'email', 'phone', 'address', 'role', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc');

            // Handle pagination
            if ($perPage === 'all' || $perPage > 1000) {
                $users = $users->get();
                return response()->json([
                    'success' => true,
                    'data' => $users,
                    'total' => $users->count()
                ], 200);
            } else {
                $users = $users->paginate($perPage);
                return response()->json([
                    'success' => true,
                    'data' => $users->items(),
                    'meta' => [
                        'current_page' => $users->currentPage(),
                        'total' => $users->total(),
                        'per_page' => $users->perPage(),
                        'last_page' => $users->lastPage(),
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user (Admin only)
     * POST /api/users
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,member',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user
     * GET /api/users/{id}
     */
    public function show($id)
    {
        try {
            $user = User::select('id', 'name', 'email', 'phone', 'address', 'role', 'created_at', 'updated_at')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    /**
     * Update the specified user
     * PUT /api/users/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8',
                'role' => 'sometimes|in:admin,member',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update fields
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('password') && !empty($request->password)) {
                $user->password = Hash::make($request->password);
            }
            if ($request->has('role')) {
                $user->role = $request->role;
            }
            if ($request->has('phone')) {
                $user->phone = $request->phone;
            }
            if ($request->has('address')) {
                $user->address = $request->address;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     * DELETE /api/users/{id}
     */
    public function destroy($id)
    {
        try {
            // Get authenticated user via JWT
            $currentUser = JWTAuth::parseToken()->authenticate();
            $user = User::findOrFail($id);

            // Prevent deleting yourself
            if ($user->id === $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            // Prevent deleting admin if only one admin exists
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete the last admin account'
                    ], 403);
                }
            }

            $userName = $user->name;
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User "' . $userName . '" deleted successfully'
            ], 200);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token expired'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token invalid'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token absent'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics for admin dashboard
     * GET /api/users/stats
     */
    public function stats()
    {
        try {
            $totalUsers = User::count();
            $totalMembers = User::where('role', 'member')->count();
            $totalAdmins = User::where('role', 'admin')->count();
            $recentUsers = User::select('id', 'name', 'email', 'role', 'created_at')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers,
                    'total_members' => $totalMembers,
                    'total_admins' => $totalAdmins,
                    'recent_users' => $recentUsers
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get only members (exclude admins)
     * GET /api/users/members
     */
    public function members()
    {
        try {
            $members = User::where('role', 'member')
                ->select('id', 'name', 'email', 'phone', 'address', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $members,
                'total' => $members->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch members',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users by name or email
     * GET /api/users/search?q=keyword
     */
    public function search(Request $request)
    {
        try {
            $keyword = $request->query('q', '');

            $users = User::where('name', 'like', '%' . $keyword . '%')
                ->orWhere('email', 'like', '%' . $keyword . '%')
                ->select('id', 'name', 'email', 'phone', 'address', 'role', 'created_at', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users,
                'total' => $users->count(),
                'keyword' => $keyword
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
