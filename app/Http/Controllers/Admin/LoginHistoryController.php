<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Http\Request;

class LoginHistoryController extends Controller
{
    /**
     * Display a listing of login attempt histories.
     */
    public function index(Request $request)
    {
        $query = LoginHistory::with('user')->latest();

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by status (success / failed)
        if ($request->filled('status')) {
            $isSuccess = $request->status === 'success';
            $query->where('is_successful', $isSuccess);
        }

        // Filter by IP or Identifier
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                  ->orWhere('email_or_phone', 'like', "%{$search}%")
                  ->orWhere('device_type', 'like', "%{$search}%");
            });
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $histories = $query->paginate(20)->withQueryString();
        $users = User::whereNotNull('role_id')->get();

        return view('admin.login_history.index', compact('histories', 'users'));
    }
}
