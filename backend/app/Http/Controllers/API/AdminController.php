<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Book;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // ===================================
    // DASHBOARD STATS
    // ===================================
    public function getDashboardStats()
    {
        try {
            $totalUsers = User::count();
            $totalBooks = Book::count();
            $availableBooks = Book::where('available_stock', '>', 0)->count();
            $totalTransactions = Transaction::count();
            $activeBorrows = Transaction::where('status', 'borrowed')->count();
            $overdueBooks = Transaction::where('status', 'overdue')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers,
                    'total_books' => $totalBooks,
                    'available_books' => $availableBooks,
                    'total_transactions' => $totalTransactions,
                    'active_borrows' => $activeBorrows,
                    'overdue_books' => $overdueBooks
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ===================================
    // USER MANAGEMENT
    // ===================================

    public function getUsers(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 1000);

            $users = User::orderBy('created_at', 'desc');

            if ($perPage == 'all') {
                $users = $users->get();
                return response()->json([
                    'success' => true,
                    'data' => $users
                ], 200);
            }

            $users = $users->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users->items(),
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUser($id)
    {
        try {
            $user = User::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:admin,user,member',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role ?? 'member',
                'phone' => $request->phone,
                'address' => $request->address
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateUser(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'role' => 'nullable|in:admin,user,member',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);

            if ($request->has('name')) {
                $user->name = $request->name;
            }

            if ($request->has('email')) {
                $user->email = $request->email;
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

            if ($request->password) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting admin
            if ($user->role === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete admin user'
                ], 403);
            }

            // Check if user has active transactions
            $activeTransactions = Transaction::where('user_id', $id)
                                            ->where('status', 'borrowed')
                                            ->count();

            if ($activeTransactions > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user with active transactions. Please return all books first.'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
