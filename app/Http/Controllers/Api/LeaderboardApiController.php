<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GiftTransaction;
use App\Models\PeriodRankBadge;
use App\Models\User;
use App\Models\UserVipCardSubscription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardApiController extends Controller
{
    /**
     * Get Real-time Leaderboard Rankings for SVIP, Rich, and Charm.
     * 
     * Endpoint: GET /api/ranks/leaderboard
     * Query params:
     *   - category: 'rich' | 'charm' | 'svip' (default: 'rich')
     *   - period: 'daily' | 'weekly' | 'monthly' (default: 'daily')
     *   - limit: integer (default: 50)
     */
    public function getLeaderboard(Request $request)
    {
        $category = strtolower($request->query('category', 'rich'));
        if (!in_array($category, ['rich', 'charm', 'svip'])) {
            $category = 'rich';
        }

        $period = strtolower($request->query('period', 'daily'));
        if (!in_array($period, ['daily', 'weekly', 'monthly'])) {
            $period = 'daily';
        }

        $limit = min(max((int) $request->query('limit', 50), 10), 100);

        // Calculate time range & remaining countdown seconds
        $now = Carbon::now();
        if ($period === 'daily') {
            $start = $now->copy()->startOfDay();
            $end = $now->copy()->endOfDay();
            $periodLabel = 'Today';
        } elseif ($period === 'weekly') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $periodLabel = 'Current Week';
        } else {
            $start = $now->copy()->startOfMonth();
            $end = $now->copy()->endOfMonth();
            $periodLabel = 'Current Month';
        }

        $countdownSeconds = max(0, $now->diffInSeconds($end, false));

        // Fetch configured rank badges & frames for this period & category from DB
        $badgeQueryCategory = ($category === 'charm') ? 'charm' : 'rich';
        $configuredBadges = PeriodRankBadge::where('is_active', true)
            ->where('period_type', $period)
            ->where('category', $badgeQueryCategory)
            ->get()
            ->keyBy('rank_position');

        // Fetch leaderboard records from GiftTransaction
        $records = [];
        if ($category === 'rich') {
            $query = GiftTransaction::whereBetween('created_at', [$start, $end])
                ->select('sender_id as user_id', DB::raw('SUM(coins_spent) as total_consume'))
                ->whereNotNull('sender_id')
                ->groupBy('sender_id')
                ->orderByDesc('total_consume')
                ->with(['sender'])
                ->take($limit);
            $records = $query->get();
        } elseif ($category === 'charm') {
            $query = GiftTransaction::whereBetween('created_at', [$start, $end])
                ->select('receiver_id as user_id', DB::raw('SUM(coins_spent) as total_consume'))
                ->whereNotNull('receiver_id')
                ->groupBy('receiver_id')
                ->orderByDesc('total_consume')
                ->with(['receiver'])
                ->take($limit);
            $records = $query->get();
        } else {
            // SVIP: Active VIP Subscribers or top spenders with VIP tier
            $vipUserIds = UserVipCardSubscription::where('status', 'active')
                ->where('expires_at', '>', $now)
                ->pluck('user_id')
                ->unique();

            if ($vipUserIds->isNotEmpty()) {
                $query = GiftTransaction::whereIn('sender_id', $vipUserIds)
                    ->whereBetween('created_at', [$start, $end])
                    ->select('sender_id as user_id', DB::raw('SUM(coins_spent) as total_consume'))
                    ->groupBy('sender_id')
                    ->orderByDesc('total_consume')
                    ->with(['sender'])
                    ->take($limit);
                $records = $query->get();
            }
        }

        // Format rankings list
        $rankings = [];
        $rank = 1;

        if ($records && $records->isNotEmpty()) {
            foreach ($records as $item) {
                $user = ($category === 'charm') ? $item->receiver : $item->sender;
                if (!$user) {
                    continue;
                }

                $badgeConfig = $configuredBadges->get($rank);

                $rankings[] = [
                    'rank'               => $rank,
                    'user_id'            => $user->id,
                    'account_id'         => $user->account_id ?? (string) (100000 + $user->id),
                    'name'               => $user->display_name ?? $user->name ?? 'User_' . $user->id,
                    'avatar'             => $user->avatar ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset($user->avatar)) : asset('assets/images/defaults/avatar-male.png'),
                    'level'              => (int) ($user->level ?? 1),
                    'country'            => $user->country ?? 'BD',
                    'country_flag'       => $user->country_flag ?? '🇧🇩',
                    'consume'            => (int) $item->total_consume,
                    'consume_formatted'  => self::formatNumber((int) $item->total_consume),
                    'badge_icon_url'     => $badgeConfig ? $badgeConfig->badge_icon_url : null,
                    'avatar_frame_url'   => $badgeConfig ? $badgeConfig->avatar_frame_url : null,
                ];
                $rank++;
            }
        }

        // If no real transactions yet in dev/staging, provide smart simulated preview records
        if (empty($rankings)) {
            $rankings = $this->getPreviewMockRankings($period, $category, $configuredBadges);
        }

        // Current logged-in user ranking information
        $currentUserId = auth('sanctum')->id() ?? $request->query('user_id');
        $myRankInfo = $this->calculateMyRank($currentUserId, $rankings);

        return response()->json([
            'success' => true,
            'status'  => true,
            'meta'    => [
                'category'          => $category,
                'period'            => $period,
                'period_label'      => $periodLabel,
                'countdown_seconds' => $countdownSeconds,
                'countdown_human'   => $this->formatCountdown($countdownSeconds),
                'total_ranked'      => count($rankings),
            ],
            'my_rank'   => $myRankInfo,
            'rankings'  => $rankings,
        ]);
    }

    /**
     * Calculate current user's distance to next rank
     */
    private function calculateMyRank($userId, array $rankings)
    {
        if (empty($rankings)) {
            return [
                'is_ranked'             => false,
                'rank'                  => null,
                'consume'               => 0,
                'consume_formatted'     => '0',
                'distance_from_rank'    => 10000,
                'distance_formatted'    => '10k',
                'label'                 => 'Distance from rank is: 10000 💎',
            ];
        }

        $myPos = null;
        $myScore = 0;

        if ($userId) {
            foreach ($rankings as $idx => $r) {
                if ($r['user_id'] == $userId) {
                    $myPos = $r['rank'];
                    $myScore = $r['consume'];
                    break;
                }
            }
        }

        if ($myPos && $myPos > 1) {
            $aboveScore = $rankings[$myPos - 2]['consume'];
            $distance = max(1, $aboveScore - $myScore + 1);
        } elseif ($myPos === 1) {
            $distance = 0;
        } else {
            // Unranked - distance to last ranked user in list
            $lastRankScore = end($rankings)['consume'] ?? 50000;
            $distance = max(1, $lastRankScore - $myScore + 1);
        }

        return [
            'is_ranked'          => $myPos !== null,
            'rank'               => $myPos,
            'consume'            => $myScore,
            'consume_formatted'  => self::formatNumber($myScore),
            'distance_from_rank' => $distance,
            'distance_formatted' => self::formatNumber($distance),
            'label'              => 'Distance from rank is: ' . number_format($distance) . ' 💎',
        ];
    }

    /**
     * Format numbers into 31.8m, 1210m, 500k, etc.
     */
    public static function formatNumber($num)
    {
        if ($num >= 1000000000) {
            return round($num / 1000000000, 1) . 'b';
        }
        if ($num >= 1000000) {
            return round($num / 1000000, 1) . 'm';
        }
        if ($num >= 1000) {
            return round($num / 1000, 1) . 'k';
        }
        return (string) $num;
    }

    /**
     * Format countdown seconds into "0d 19:07:06"
     */
    private function formatCountdown($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%dd %02d:%02d:%02d', $days, $hours, $minutes, $secs);
    }

    /**
     * Preview mock rankings matching user's screenshots
     */
    private function getPreviewMockRankings($period, $category, $configuredBadges)
    {
        $multiplier = ($period === 'monthly') ? 40 : (($period === 'weekly') ? 10 : 1);
        $baseScores = [
            ['name' => '💕 🇮🇳 ABHI 🇮🇳 ...', 'consume' => 31800000 * $multiplier, 'level' => 12, 'country' => 'IN', 'flag' => '🇮🇳'],
            ['name' => 'X-Factor',            'consume' => 21200000 * $multiplier, 'level' => 9,  'country' => 'IN', 'flag' => '🇮🇳'],
            ['name' => 'Guest_COc4hV',        'consume' => 18300000 * $multiplier, 'level' => 8,  'country' => 'SA', 'flag' => '🇸🇦'],
            ['name' => 'SAM',                 'consume' => 12800000 * $multiplier, 'level' => 7,  'country' => 'AE', 'flag' => '🇦🇪'],
            ['name' => 'Mehran',              'consume' => 11700000 * $multiplier, 'level' => 9,  'country' => 'PK', 'flag' => '🇵🇰'],
            ['name' => '❤️ 🇳🇬 Sahil 🇳🇬 ❤️',    'consume' => 9690000 * $multiplier,  'level' => 7,  'country' => 'NG', 'flag' => '🇳🇬'],
            ['name' => 'MD Fayaz',            'consume' => 9400000 * $multiplier,  'level' => 5,  'country' => 'BD', 'flag' => '🇧🇩'],
            ['name' => 'Azzad 🤴',            'consume' => 9040000 * $multiplier,  'level' => 8,  'country' => 'BD', 'flag' => '🇧🇩'],
        ];

        $rankings = [];
        foreach ($baseScores as $i => $item) {
            $rank = $i + 1;
            $badgeConfig = $configuredBadges->get($rank);

            $rankings[] = [
                'rank'               => $rank,
                'user_id'            => 100 + $rank,
                'account_id'         => (string) (743264900 + $rank),
                'name'               => $item['name'],
                'avatar'             => asset('assets/images/defaults/avatar-male.png'),
                'level'              => $item['level'],
                'country'            => $item['country'],
                'country_flag'       => $item['flag'],
                'consume'            => $item['consume'],
                'consume_formatted'  => self::formatNumber($item['consume']),
                'badge_icon_url'     => $badgeConfig ? $badgeConfig->badge_icon_url : null,
                'avatar_frame_url'   => $badgeConfig ? $badgeConfig->avatar_frame_url : null,
            ];
        }

        return $rankings;
    }
}
