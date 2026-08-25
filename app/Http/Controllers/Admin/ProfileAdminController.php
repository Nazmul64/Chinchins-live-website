<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileAdminController extends Controller
{
    /**
     * Display the User Profile Overview with live preview.
     */
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $user = $userId ? User::find($userId) : auth()->user();

        if (!$user) {
            $user = User::first();
        }

        $allUsers = User::latest()->take(20)->get();

        return view('admin.profile', compact('user', 'allUsers'));
    }
}
