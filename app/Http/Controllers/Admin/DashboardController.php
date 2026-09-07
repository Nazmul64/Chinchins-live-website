<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CallSession;
use App\Models\CoinTransaction;
use App\Models\DepositRequest;
use App\Models\PaymentMethod;
use App\Models\Role;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Main / Super Admin Dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // If non-super admin lands on index, route them to their specialized dashboard
        if ($user && !$user->isSuperAdmin()) {
            if ($user->isSubAdmin()) {
                return $this->subAdmin();
            } elseif ($user->isManager()) {
                return $this->manager();
            } elseif ($user->isEmployee()) {
                return $this->employee();
            }
        }

        $totalUsers = 0;
        $totalStaff = 0;
        $totalCoins = 0;
        $pendingDeposits = 0;
        $pendingWithdrawals = 0;
        $approvedDepositsSum = 0;
        $approvedWithdrawalsSum = 0;
        $activeMethodsCount = 0;
        $recentDeposits = collect();
        $recentWithdrawals = collect();
        $recentUsers = collect();
        $recentTransactions = collect();
        $recentActivities = collect();

        try {
            $totalUsers = User::count();
            $totalCoins = User::sum('coins') ?? 0;
        } catch (\Throwable $e) {}

        try {
            $totalStaff = User::whereNotNull('role_id')->count();
        } catch (\Throwable $e) {}

        try {
            $pendingDeposits = DepositRequest::where('status', 'pending')->count();
            $approvedDepositsSum = DepositRequest::where('status', 'approved')->sum('amount') ?? 0;
            $recentDeposits = DepositRequest::with(['user', 'paymentMethod'])->latest()->limit(5)->get();
        } catch (\Throwable $e) {}

        try {
            $pendingWithdrawals = WithdrawRequest::where('status', 'pending')->count();
            $approvedWithdrawalsSum = WithdrawRequest::where('status', 'approved')->sum('net_payable_amount') ?? 0;
            $recentWithdrawals = WithdrawRequest::with(['user', 'paymentMethod'])->latest()->limit(5)->get();
        } catch (\Throwable $e) {}

        try {
            $activeMethodsCount = PaymentMethod::where('is_active', true)->count();
        } catch (\Throwable $e) {}

        try {
            $recentUsers = User::latest()->limit(5)->get();
        } catch (\Throwable $e) {}

        try {
            $recentTransactions = CoinTransaction::with('user')->latest()->limit(6)->get();
        } catch (\Throwable $e) {}

        try {
            $recentActivities = ActivityLog::with('user')->latest()->limit(8)->get();
        } catch (\Throwable $e) {}

        $messagesCount = '0';
        try {
            $messagesCount = (string) CallSession::count();
        } catch (\Throwable $e) {}

        $postsCount = '0';
        try {
            $postsCount = (string) DepositRequest::count();
        } catch (\Throwable $e) {}

        $metrics = [
            'total_orders' => [
                'value' => number_format($totalUsers),
                'change' => '+ Active',
                'trend' => 'up',
                'chart_type' => 'sparkline-bar',
                'color' => '#3b82f6'
            ],
            'total_views' => [
                'value' => number_format($totalCoins),
                'change' => 'Circulating',
                'trend' => 'up',
                'chart_type' => 'sparkline-line',
                'color' => '#f59e0b'
            ],
            'revenue' => [
                'value' => '৳ ' . number_format($approvedDepositsSum, 2),
                'change' => '+ Deposited',
                'trend' => 'up',
                'chart_type' => 'sparkline-line',
                'color' => '#10b981'
            ],
            'customers' => [
                'value' => number_format($pendingDeposits + $pendingWithdrawals),
                'change' => ($pendingDeposits + $pendingWithdrawals) > 0 ? 'Action Needed' : 'All Clear',
                'trend' => ($pendingDeposits + $pendingWithdrawals) > 0 ? 'down' : 'up',
                'chart_type' => 'sparkline-bar',
                'color' => '#f43f5e'
            ],
            'messages_count' => $messagesCount,
            'posts_count' => $postsCount,
            'traffic_percentage' => 88,
            'device_stats' => [
                'desktop' => 15.2,
                'mobile' => 65.5,
                'tablet' => 19.3,
                'total_visitors_percentage' => 85,
            ]
        ];

        return view('admin.dashboard', compact(
            'metrics',
            'recentDeposits',
            'recentWithdrawals',
            'recentUsers',
            'recentTransactions',
            'recentActivities',
            'totalUsers',
            'totalStaff',
            'totalCoins',
            'pendingDeposits',
            'pendingWithdrawals',
            'approvedDepositsSum',
            'approvedWithdrawalsSum',
            'activeMethodsCount'
        ));
    }

    /**
     * Display the Sub Admin Dashboard.
     */
    public function subAdmin()
    {
        $totalUsers = 0;
        $pendingDeposits = 0;
        $pendingWithdrawals = 0;
        $todayDeposits = 0;
        $todayWithdrawals = 0;
        $totalCallsToday = 0;
        $recentDeposits = collect();
        $recentWithdrawals = collect();
        $recentActivities = collect();

        try {
            $totalUsers = User::count();
            $pendingDeposits = DepositRequest::where('status', 'pending')->count();
            $pendingWithdrawals = WithdrawRequest::where('status', 'pending')->count();
            $todayDeposits = DepositRequest::where('status', 'approved')->whereDate('created_at', today())->sum('amount') ?? 0;
            $todayWithdrawals = WithdrawRequest::where('status', 'approved')->whereDate('created_at', today())->sum('net_payable_amount') ?? 0;
            $totalCallsToday = CallSession::whereDate('created_at', today())->count();
            $recentDeposits = DepositRequest::with(['user', 'paymentMethod'])->latest()->limit(5)->get();
            $recentWithdrawals = WithdrawRequest::with(['user', 'paymentMethod'])->latest()->limit(5)->get();
            $recentActivities = ActivityLog::with('user')->latest()->limit(6)->get();
        } catch (\Throwable $e) {}

        return view('admin.dashboards.sub_admin', compact(
            'totalUsers',
            'pendingDeposits',
            'pendingWithdrawals',
            'todayDeposits',
            'todayWithdrawals',
            'totalCallsToday',
            'recentDeposits',
            'recentWithdrawals',
            'recentActivities'
        ));
    }

    /**
     * Display the Manager Dashboard.
     */
    public function manager()
    {
        $pendingDeposits = 0;
        $pendingWithdrawals = 0;
        $totalCalls = 0;
        $activeUsers = 0;
        $recentDeposits = collect();
        $recentWithdrawals = collect();
        $recentActivities = collect();

        try {
            $pendingDeposits = DepositRequest::where('status', 'pending')->count();
            $pendingWithdrawals = WithdrawRequest::where('status', 'pending')->count();
            $totalCalls = CallSession::count();
            $activeUsers = User::where('is_active', true)->count();
            $recentDeposits = DepositRequest::with(['user', 'paymentMethod'])->where('status', 'pending')->latest()->limit(6)->get();
            $recentWithdrawals = WithdrawRequest::with(['user', 'paymentMethod'])->where('status', 'pending')->latest()->limit(6)->get();
            $recentActivities = ActivityLog::where('user_id', Auth::id())->latest()->limit(6)->get();
        } catch (\Throwable $e) {}

        return view('admin.dashboards.manager', compact(
            'pendingDeposits',
            'pendingWithdrawals',
            'totalCalls',
            'activeUsers',
            'recentDeposits',
            'recentWithdrawals',
            'recentActivities'
        ));
    }

    /**
     * Display the Employee Dashboard.
     */
    public function employee()
    {
        $user = Auth::user();
        $pendingDeposits = 0;
        $pendingWithdrawals = 0;
        $myRecentActivities = collect();

        try {
            $pendingDeposits = DepositRequest::where('status', 'pending')->count();
            $pendingWithdrawals = WithdrawRequest::where('status', 'pending')->count();
            $myRecentActivities = ActivityLog::where('user_id', $user->id)->latest()->limit(8)->get();
        } catch (\Throwable $e) {}

        return view('admin.dashboards.employee', compact(
            'user',
            'pendingDeposits',
            'pendingWithdrawals',
            'myRecentActivities'
        ));
    }
}
