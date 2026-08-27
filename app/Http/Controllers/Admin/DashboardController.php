<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the Onedash Admin Dashboard.
     */
    public function index()
    {
        $totalUsers = \App\Models\User::count();
        $totalCoins = \App\Models\User::sum('coins');
        $pendingDeposits = \App\Models\DepositRequest::where('status', 'pending')->count();
        $pendingWithdrawals = \App\Models\WithdrawRequest::where('status', 'pending')->count();
        $approvedDepositsSum = \App\Models\DepositRequest::where('status', 'approved')->sum('amount');
        $approvedWithdrawalsSum = \App\Models\WithdrawRequest::where('status', 'approved')->sum('net_payable_amount');
        $activeMethodsCount = \App\Models\PaymentMethod::where('is_active', true)->count();

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
            'messages_count' => (string) \App\Models\CallSession::count(),
            'posts_count' => (string) \App\Models\DepositRequest::count(),
            'traffic_percentage' => 88,
            'device_stats' => [
                'desktop' => 15.2,
                'mobile' => 65.5,
                'tablet' => 19.3,
                'total_visitors_percentage' => 85,
            ]
        ];

        $recentDeposits = \App\Models\DepositRequest::with(['user', 'paymentMethod'])->latest()->limit(5)->get();
        $recentWithdrawals = \App\Models\WithdrawRequest::with(['user', 'paymentMethod'])->latest()->limit(5)->get();
        $recentUsers = \App\Models\User::latest()->limit(5)->get();
        $recentTransactions = \App\Models\CoinTransaction::with('user')->latest()->limit(6)->get();

        return view('admin.dashboard', compact(
            'metrics',
            'recentDeposits',
            'recentWithdrawals',
            'recentUsers',
            'recentTransactions',
            'totalUsers',
            'totalCoins',
            'pendingDeposits',
            'pendingWithdrawals',
            'approvedDepositsSum',
            'approvedWithdrawalsSum',
            'activeMethodsCount'
        ));
    }
}
