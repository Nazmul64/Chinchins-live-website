<?php

namespace Database\Seeders;

use App\Models\Gift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StreamingAssetsSeeder extends Seeder
{
    public function run(): void
    {
        $targetDir = public_path('uploads/all_image');
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0777, true, true);
        }

        $assets = [
            // ==========================================
            // 1. Mythical & Dragons
            // ==========================================
            [
                'name'        => 'Golden Fire Dragon',
                'category'    => 'Luxury',
                'coins'       => 20000,
                'filename'    => 'dragon_gift',
                'badge'       => 'TOP VIP',
                'color1'      => '#FF8C00',
                'color2'      => '#FFD700',
                'color3'      => '#FF4500',
                'icon'        => '🐲',
                'description' => 'Summon the Legendary Golden Fire Dragon across the whole screen!',
            ],
            [
                'name'        => 'Blazing Phoenix',
                'category'    => 'Luxury',
                'coins'       => 15000,
                'filename'    => 'phoenix_gift',
                'badge'       => 'HOT',
                'color1'      => '#FF1493',
                'color2'      => '#FF4500',
                'color3'      => '#FFD700',
                'icon'        => '🦅',
                'description' => 'Glorious Phoenix rising with radiant solar wings!',
            ],

            // ==========================================
            // 2. Supercars, Bikes & Luxury Transport
            // ==========================================
            [
                'name'        => 'Luxury Supercar',
                'category'    => 'Vehicles',
                'coins'       => 12000,
                'filename'    => 'sports_car_gift',
                'badge'       => 'SUPER',
                'color1'      => '#E11D48',
                'color2'      => '#F59E0B',
                'color3'      => '#9333EA',
                'icon'        => '🏎️',
                'description' => 'Exotic gold & ruby supercar with roaring neon sound!',
            ],
            [
                'name'        => 'Cyber Sports Bike',
                'category'    => 'Vehicles',
                'coins'       => 8500,
                'filename'    => 'sports_bike_gift',
                'badge'       => 'NEON',
                'color1'      => '#06B6D4',
                'color2'      => '#3B82F6',
                'color3'      => '#10B981',
                'icon'        => '🏍️',
                'description' => 'Futuristic cyber superbike with glowing light trails!',
            ],
            [
                'name'        => 'Mega Luxury Yacht',
                'category'    => 'Vehicles',
                'coins'       => 18000,
                'filename'    => 'luxury_yacht_gift',
                'badge'       => 'VIP RIDE',
                'color1'      => '#3B82F6',
                'color2'      => '#60A5FA',
                'color3'      => '#F59E0B',
                'icon'        => '🛥️',
                'description' => 'Luxury cruise yacht sailing with ocean waves & fireworks!',
            ],
            [
                'name'        => 'Private Gold Jet',
                'category'    => 'Vehicles',
                'coins'       => 16000,
                'filename'    => 'private_jet_gift',
                'badge'       => 'AIR',
                'color1'      => '#F59E0B',
                'color2'      => '#FDE047',
                'color3'      => '#1E293B',
                'icon'        => '✈️',
                'description' => 'Golden private jet flying across the live stream!',
            ],
            [
                'name'        => 'Space Rocket Launch',
                'category'    => 'Vehicles',
                'coins'       => 25000,
                'filename'    => 'space_rocket_gift',
                'badge'       => 'GALACTIC',
                'color1'      => '#8B5CF6',
                'color2'      => '#EC4899',
                'color3'      => '#38BDF8',
                'icon'        => '🚀',
                'description' => 'Blast off to the cosmos with a massive rocket boost!',
            ],

            // ==========================================
            // 3. Royal & Diamond Castles
            // ==========================================
            [
                'name'        => 'Royal Diamond Castle',
                'category'    => 'Luxury',
                'coins'       => 30000,
                'filename'    => 'diamond_castle_gift',
                'badge'       => 'LEGENDARY',
                'color1'      => '#EC4899',
                'color2'      => '#8B5CF6',
                'color3'      => '#F59E0B',
                'icon'        => '🏰',
                'description' => 'A magical fairytale diamond palace with glowing towers!',
            ],
            [
                'name'        => 'Royal SVIP Crown',
                'category'    => 'Luxury',
                'coins'       => 10000,
                'filename'    => 'royal_crown_gift',
                'badge'       => 'SVIP',
                'color1'      => '#F59E0B',
                'color2'      => '#FDE047',
                'color3'      => '#EF4444',
                'icon'        => '👑',
                'description' => 'Crown your favorite host with the imperial golden tiara!',
            ],
            [
                'name'        => 'Diamond Solitaire Ring',
                'category'    => 'Luxury',
                'coins'       => 7000,
                'filename'    => 'diamond_ring_gift',
                'badge'       => 'PROPOSAL',
                'color1'      => '#38BDF8',
                'color2'      => '#E0F2FE',
                'color3'      => '#F59E0B',
                'icon'        => '💍',
                'description' => 'Sparkling proposal diamond ring in luxury velvet box!',
            ],

            // ==========================================
            // 4. Romantic & Popular Live Gifts (Bigo/TikTok style)
            // ==========================================
            [
                'name'        => '999 Luxury Roses',
                'category'    => 'Popular',
                'coins'       => 1999,
                'filename'    => 'rose_bouquet_gift',
                'badge'       => 'ROMANTIC',
                'color1'      => '#E11D48',
                'color2'      => '#FB7185',
                'color3'      => '#F43F5E',
                'icon'        => '🌹',
                'description' => 'A giant bouquet of 999 blossoming red velvet roses!',
            ],
            [
                'name'        => 'Passionate Kiss',
                'category'    => 'Popular',
                'coins'       => 520,
                'filename'    => 'romantic_kiss_gift',
                'badge'       => '520 LOVE',
                'color1'      => '#F43F5E',
                'color2'      => '#FDA4AF',
                'color3'      => '#FB7185',
                'icon'        => '💋',
                'description' => 'Glowing ruby lips sending flying kiss hearts!',
            ],
            [
                'name'        => 'Love Heart Fireworks',
                'category'    => 'Popular',
                'coins'       => 3500,
                'filename'    => 'heart_fireworks_gift',
                'badge'       => 'FIREWORKS',
                'color1'      => '#EC4899',
                'color2'      => '#F43F5E',
                'color3'      => '#FBBF24',
                'icon'        => '🎆',
                'description' => 'Dazzling heart fireworks lighting up the live room!',
            ],
            [
                'name'        => 'Luxury Champagne Pop',
                'category'    => 'Popular',
                'coins'       => 2800,
                'filename'    => 'champagne_gift',
                'badge'       => 'PARTY',
                'color1'      => '#F59E0B',
                'color2'      => '#FEF08A',
                'color3'      => '#10B981',
                'icon'        => '🍾',
                'description' => 'Pop sparkling champagne with glowing crystal glasses!',
            ],
            [
                'name'        => 'Love Teddy Bear',
                'category'    => 'Popular',
                'coins'       => 999,
                'filename'    => 'teddy_bear_gift',
                'badge'       => 'CUTE',
                'color1'      => '#D97706',
                'color2'      => '#FBBF24',
                'color3'      => '#F43F5E',
                'icon'        => '🧸',
                'description' => 'Adorable giant fluffy teddy bear hugging a heart!',
            ],

            // ==========================================
            // 5. Profile Bases & Frames (প্রোফাইল বেস ও ফ্রেম)
            // ==========================================
            [
                'name'        => 'Royal Gold Profile Base',
                'category'    => 'Profile Base',
                'coins'       => 5000,
                'filename'    => 'profile_base_royal_gold',
                'badge'       => 'FRAME',
                'color1'      => '#F59E0B',
                'color2'      => '#FDE047',
                'color3'      => '#D97706',
                'icon'        => '👑',
                'description' => 'Imperial 24K gold glowing profile avatar base with ruby accents.',
            ],
            [
                'name'        => 'Diamond Angel Wings Base',
                'category'    => 'Profile Base',
                'coins'       => 8000,
                'filename'    => 'profile_base_diamond_wings',
                'badge'       => 'FRAME',
                'color1'      => '#38BDF8',
                'color2'      => '#EC4899',
                'color3'      => '#FFFFFF',
                'icon'        => '🪽',
                'description' => 'Shining diamond crystal wings wrapping around profile avatar.',
            ],
            [
                'name'        => 'Cyberpunk Neon Profile Base',
                'category'    => 'Profile Base',
                'coins'       => 6000,
                'filename'    => 'profile_base_cyber_neon',
                'badge'       => 'FRAME',
                'color1'      => '#06B6D4',
                'color2'      => '#A855F7',
                'color3'      => '#10B981',
                'icon'        => '⚡',
                'description' => 'Futuristic neon holographic ring with pulsing energy lights.',
            ],
            [
                'name'        => 'Fire Dragon Avatar Base',
                'category'    => 'Profile Base',
                'coins'       => 12000,
                'filename'    => 'profile_base_fire_dragon',
                'badge'       => 'FRAME',
                'color1'      => '#FF4500',
                'color2'      => '#FFD700',
                'color3'      => '#B91C1C',
                'icon'        => '🔥',
                'description' => 'Ferocious fire dragon coiled around the user avatar profile.',
            ],
            [
                'name'        => 'SVIP Emperor Tiara Base',
                'category'    => 'Profile Base',
                'coins'       => 15000,
                'filename'    => 'profile_base_svip_crown',
                'badge'       => 'FRAME',
                'color1'      => '#8B5CF6',
                'color2'      => '#F59E0B',
                'color3'      => '#EC4899',
                'icon'        => '✨',
                'description' => 'Top tier SVIP Emperor Tiara with shimmering particle aura.',
            ],
        ];

        foreach ($assets as $idx => $item) {
            $svgFilename = $item['filename'] . '.svg';
            $pngFilename = $item['filename'] . '.png';
            $svgPath = $targetDir . '/' . $svgFilename;
            $pngPath = $targetDir . '/' . $pngFilename;

            $svgContent = $this->generateVectorSvg($item);
            File::put($svgPath, $svgContent);

            // Also ensure .png exists for fallback
            if (!File::exists($pngPath)) {
                File::put($pngPath, $svgContent);
            }

            // Register / Update in database
            Gift::updateOrCreate(
                ['name' => $item['name']],
                [
                    'coins'          => $item['coins'],
                    'coin_price'     => $item['coins'],
                    'category'       => $item['category'],
                    'image'          => 'uploads/all_image/' . $pngFilename,
                    'icon_url'       => 'uploads/all_image/' . $pngFilename,
                    'file_url'       => 'uploads/all_image/' . $svgFilename,
                    'animation_url'  => 'uploads/all_image/' . $svgFilename,
                    'animation_type' => 'svg',
                    'format'         => 'svg',
                    'badge'          => $item['badge'],
                    'description'    => $item['description'],
                    'is_active'      => true,
                    'is_broadcast'   => $item['coins'] >= 5000,
                    'sort_order'     => $idx + 1,
                ]
            );
        }

        echo "Successfully generated " . count($assets) . " streaming assets in uploads/all_image/\n";
    }

    /**
     * Generate rich vector 3D-styled SVG for gifts & profile bases.
     */
    protected function generateVectorSvg(array $item): string
    {
        $name = htmlspecialchars($item['name']);
        $c1 = $item['color1'];
        $c2 = $item['color2'];
        $c3 = $item['color3'];
        $icon = $item['icon'];
        $badge = htmlspecialchars($item['badge']);
        $isBase = str_contains($item['filename'], 'profile_base');

        if ($isBase) {
            return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%">
  <defs>
    <radialGradient id="baseGlow" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="{$c2}" stop-opacity="0.9"/>
      <stop offset="60%" stop-color="{$c1}" stop-opacity="0.6"/>
      <stop offset="100%" stop-color="{$c3}" stop-opacity="0"/>
    </radialGradient>
    <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$c2}"/>
      <stop offset="50%" stop-color="{$c1}"/>
      <stop offset="100%" stop-color="{$c3}"/>
    </linearGradient>
    <filter id="glow">
      <feGaussianBlur stdDeviation="8" result="coloredBlur"/>
      <feMerge>
        <feMergeNode in="coloredBlur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <!-- Ambient Aura -->
  <circle cx="256" cy="256" r="230" fill="url(#baseGlow)"/>

  <!-- Outer Decorative Ring -->
  <circle cx="256" cy="256" r="200" fill="none" stroke="url(#ringGrad)" stroke-width="12" filter="url(#glow)"/>
  <circle cx="256" cy="256" r="175" fill="none" stroke="{$c2}" stroke-width="4" stroke-dasharray="8 6"/>

  <!-- Ornate Crown / Crest Top -->
  <g transform="translate(256, 50)" text-anchor="middle">
    <circle cx="0" cy="0" r="32" fill="{$c2}" filter="url(#glow)"/>
    <text y="14" font-size="34" text-anchor="middle">{$icon}</text>
  </g>

  <!-- Avatar Placeholder Cutout Center -->
  <circle cx="256" cy="256" r="145" fill="#0f172a" stroke="{$c2}" stroke-width="4"/>

  <!-- Bottom Badge Tag -->
  <g transform="translate(256, 450)">
    <rect x="-80" y="-20" width="160" height="40" rx="20" fill="url(#ringGrad)" stroke="#ffffff" stroke-width="2" filter="url(#glow)"/>
    <text x="0" y="7" fill="#ffffff" font-family="'Outfit', sans-serif" font-weight="900" font-size="14" text-anchor="middle" letter-spacing="1">{$badge}</text>
  </g>

  <!-- Sparkles -->
  <circle cx="80" cy="160" r="5" fill="{$c2}" filter="url(#glow)"/>
  <circle cx="430" cy="160" r="5" fill="{$c2}" filter="url(#glow)"/>
  <circle cx="100" cy="380" r="4" fill="{$c1}" filter="url(#glow)"/>
  <circle cx="410" cy="380" r="4" fill="{$c1}" filter="url(#glow)"/>
</svg>
SVG;
        }

        // Standard 3D Stream Gift SVG
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%">
  <defs>
    <radialGradient id="bgGlow" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="{$c2}" stop-opacity="0.85"/>
      <stop offset="50%" stop-color="{$c1}" stop-opacity="0.5"/>
      <stop offset="100%" stop-color="#090d16" stop-opacity="0"/>
    </radialGradient>
    <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="{$c2}"/>
      <stop offset="50%" stop-color="{$c1}"/>
      <stop offset="100%" stop-color="{$c3}"/>
    </linearGradient>
    <filter id="softGlow">
      <feGaussianBlur stdDeviation="10" result="coloredBlur"/>
      <feMerge>
        <feMergeNode in="coloredBlur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <!-- Dark Background Plate with Rounded Corners -->
  <rect width="512" height="512" rx="64" fill="#0b0f19"/>
  <circle cx="256" cy="240" r="210" fill="url(#bgGlow)"/>

  <!-- Outer Golden Border -->
  <rect x="16" y="16" width="480" height="480" rx="52" fill="none" stroke="url(#goldGrad)" stroke-width="6" opacity="0.75"/>

  <!-- Sparkling Center Icon / Visual -->
  <g transform="translate(256, 220) scale(1)" filter="url(#softGlow)">
    <circle cx="0" cy="0" r="120" fill="url(#goldGrad)" opacity="0.25"/>
    <text x="0" y="45" font-size="120" text-anchor="middle">{$icon}</text>
  </g>

  <!-- Top Badge Ribbon -->
  <g transform="translate(256, 50)">
    <rect x="-70" y="-16" width="140" height="32" rx="16" fill="url(#goldGrad)" stroke="#ffffff" stroke-width="1.5" filter="url(#softGlow)"/>
    <text x="0" y="6" fill="#ffffff" font-family="'Outfit', sans-serif" font-weight="900" font-size="12" text-anchor="middle" letter-spacing="1">{$badge}</text>
  </g>

  <!-- Title Plate -->
  <g transform="translate(256, 420)">
    <rect x="-170" y="-30" width="340" height="56" rx="28" fill="#1e293b" stroke="url(#goldGrad)" stroke-width="2.5" filter="url(#softGlow)"/>
    <text x="0" y="6" fill="#ffffff" font-family="'Outfit', sans-serif" font-weight="800" font-size="18" text-anchor="middle">{$name}</text>
  </g>

  <!-- Coin Price Pill -->
  <g transform="translate(256, 465)">
    <text x="0" y="0" fill="{$c2}" font-family="'Outfit', sans-serif" font-weight="900" font-size="16" text-anchor="middle">💎 {$item['coins']} Gems</text>
  </g>
</svg>
SVG;
    }
}
