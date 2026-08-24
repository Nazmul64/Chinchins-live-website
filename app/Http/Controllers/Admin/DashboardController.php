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
        $metrics = [
            'total_orders' => [
                'value' => '8,542',
                'change' => '+ 16%',
                'trend' => 'up',
                'chart_type' => 'sparkline-line',
                'color' => '#f43f5e'
            ],
            'total_views' => [
                'value' => '12.5M',
                'change' => '- 3.4%',
                'trend' => 'down',
                'chart_type' => 'sparkline-bar',
                'color' => '#3b82f6'
            ],
            'revenue' => [
                'value' => '$64.5K',
                'change' => '+ 24%',
                'trend' => 'up',
                'chart_type' => 'sparkline-line',
                'color' => '#10b981'
            ],
            'customers' => [
                'value' => '25.8K',
                'change' => '+ 8.2%',
                'trend' => 'up',
                'chart_type' => 'sparkline-bar',
                'color' => '#f97316'
            ],
            'messages_count' => '289',
            'posts_count' => '489',
            'traffic_percentage' => 78,
            'device_stats' => [
                'desktop' => 15.2,
                'mobile' => 62.3,
                'tablet' => 22.5,
                'total_visitors_percentage' => 85,
            ]
        ];

        return view('admin.dashboard', compact('metrics'));
    }
}
