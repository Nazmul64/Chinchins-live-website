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

        $items = [
            'romantic_kiss_gift' => [
                'name'        => 'Passionate Kiss',
                'category'    => 'Popular',
                'coins'       => 520,
                'svg'         => $this->getPassionateKissSvg(),
                'description' => 'Animated 3D flying kiss lips blowing floating hearts across screen',
            ],
            'dragon_gift' => [
                'name'        => 'Golden Fire Dragon',
                'category'    => 'Luxury',
                'coins'       => 20000,
                'svg'         => $this->getDragonSvg(),
                'description' => 'Majestic animated golden fire dragon breathing swirling flames',
            ],
            'helicopter_gift' => [
                'name'        => 'VIP Luxury Helicopter',
                'category'    => 'Vehicles',
                'coins'       => 14000,
                'svg'         => $this->getHelicopterSvg(),
                'description' => 'Luxury executive helicopter with rapidly spinning rotor blades',
            ],
            'sports_car_gift' => [
                'name'        => 'Luxury Supercar',
                'category'    => 'Vehicles',
                'coins'       => 12000,
                'svg'         => $this->getSupercarSvg(),
                'description' => 'Aerodynamic supercar with spinning neon wheels & drift flames',
            ],
            'sports_bike_gift' => [
                'name'        => 'Cyber Sports Bike',
                'category'    => 'Vehicles',
                'coins'       => 8500,
                'svg'         => $this->getSportsBikeSvg(),
                'description' => 'Futuristic racing sports bike with neon light trails',
            ],
            'luxury_yacht_gift' => [
                'name'        => 'Mega Luxury Yacht',
                'category'    => 'Vehicles',
                'coins'       => 18000,
                'svg'         => $this->getYachtSvg(),
                'description' => 'Multi-deck luxury yacht cruising on animated ocean waves',
            ],
            'private_jet_gift' => [
                'name'        => 'Private Golden Jet',
                'category'    => 'Vehicles',
                'coins'       => 16000,
                'svg'         => $this->getPrivateJetSvg(),
                'description' => 'Supersonic golden private jet flying with contrails',
            ],
            'space_rocket_gift' => [
                'name'        => 'Space Rocket Launch',
                'category'    => 'Vehicles',
                'coins'       => 25000,
                'svg'         => $this->getRocketSvg(),
                'description' => 'Space rocket blasting off with roaring thruster plumes',
            ],
            'diamond_castle_gift' => [
                'name'        => 'Royal Diamond Castle',
                'category'    => 'Luxury',
                'coins'       => 30000,
                'svg'         => $this->getDiamondCastleSvg(),
                'description' => 'Fairytale crystal diamond castle with shimmering towers',
            ],
            'royal_crown_gift' => [
                'name'        => 'Royal SVIP Crown',
                'category'    => 'Luxury',
                'coins'       => 10000,
                'svg'         => $this->getRoyalCrownSvg(),
                'description' => 'Imperial 24K gold crown with glowing jewels and sparkles',
            ],
            'diamond_ring_gift' => [
                'name'        => 'Diamond Solitaire Ring',
                'category'    => 'Luxury',
                'coins'       => 7000,
                'svg'         => $this->getDiamondRingSvg(),
                'description' => 'Sparkling diamond proposal ring with rotating light glints',
            ],
            'rose_bouquet_gift' => [
                'name'        => '999 Luxury Roses',
                'category'    => 'Popular',
                'coins'       => 1999,
                'svg'         => $this->getRoseBouquetSvg(),
                'description' => 'Giant bouquet of 999 red velvet roses with drifting petals',
            ],
            'heart_fireworks_gift' => [
                'name'        => 'Love Heart Fireworks',
                'category'    => 'Popular',
                'coins'       => 3500,
                'svg'         => $this->getHeartFireworksSvg(),
                'description' => 'Dazzling heart fireworks bursting with sparkling glitter',
            ],
            'champagne_gift' => [
                'name'        => 'Luxury Champagne Pop',
                'category'    => 'Popular',
                'coins'       => 2800,
                'svg'         => $this->getChampagneSvg(),
                'description' => 'Popping champagne bottle with golden bubbles and crystal flutes',
            ],
            'teddy_bear_gift' => [
                'name'        => 'Love Teddy Bear',
                'category'    => 'Popular',
                'coins'       => 999,
                'svg'         => $this->getTeddyBearSvg(),
                'description' => 'Cute plush teddy bear hugging a glowing pulsing love heart',
            ],

            // Profile Bases / Avatar Frames
            'profile_base_royal_gold' => [
                'name'        => 'Royal Gold Profile Base',
                'category'    => 'Profile Base',
                'coins'       => 5000,
                'svg'         => $this->getProfileBaseRoyalGoldSvg(),
                'description' => 'Ornate 24K gold profile avatar frame with ruby gems and crown',
            ],
            'profile_base_diamond_wings' => [
                'name'        => 'Diamond Angel Wings Base',
                'category'    => 'Profile Base',
                'coins'       => 8000,
                'svg'         => $this->getProfileBaseWingsSvg(),
                'description' => 'Shining diamond crystal wings wrapping around user avatar',
            ],
            'profile_base_cyber_neon' => [
                'name'        => 'Cyber Neon Profile Base',
                'category'    => 'Profile Base',
                'coins'       => 6000,
                'svg'         => $this->getProfileBaseCyberSvg(),
                'description' => 'Pulsing holographic neon ring with rotating energy arcs',
            ],
            'profile_base_fire_dragon' => [
                'name'        => 'Fire Dragon Avatar Base',
                'category'    => 'Profile Base',
                'coins'       => 12000,
                'svg'         => $this->getProfileBaseDragonSvg(),
                'description' => 'Blazing fire dragon coiled around circular profile frame',
            ],
            'profile_base_svip_crown' => [
                'name'        => 'SVIP Emperor Crown Base',
                'category'    => 'Profile Base',
                'coins'       => 15000,
                'svg'         => $this->getProfileBaseSvipSvg(),
                'description' => 'Imperial SVIP tiara with orbiting star particles',
            ],
        ];

        $order = 1;
        foreach ($items as $key => $data) {
            $svgPath = $targetDir . '/' . $key . '.svg';
            $pngPath = $targetDir . '/' . $key . '.png';

            File::put($svgPath, $data['svg']);
            File::put($pngPath, $data['svg']); // SVG vector is preferred and serves directly

            Gift::updateOrCreate(
                ['name' => $data['name']],
                [
                    'coins'          => $data['coins'],
                    'coin_price'     => $data['coins'],
                    'category'       => $data['category'],
                    'image'          => 'uploads/all_image/' . $key . '.svg',
                    'icon_url'       => 'uploads/all_image/' . $key . '.svg',
                    'file_url'       => 'uploads/all_image/' . $key . '.svg',
                    'animation_url'  => 'uploads/all_image/' . $key . '.svg',
                    'animation_type' => 'svg',
                    'format'         => 'svg',
                    'badge'          => $data['coins'] >= 10000 ? 'SVIP' : ($data['coins'] >= 5000 ? 'HOT' : 'GIFT'),
                    'description'    => $data['description'],
                    'is_active'      => true,
                    'is_broadcast'   => $data['coins'] >= 5000,
                    'sort_order'     => $order++,
                ]
            );
        }

        echo "Successfully generated " . count($items) . " pure animated streaming assets in uploads/all_image/\n";
    }

    // =========================================================================
    // 1. Flying Passionate Kiss Lips (Pure Lips + Floating Flying Hearts)
    // =========================================================================
    protected function getPassionateKissSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <radialGradient id="kissGrad" cx="40%" cy="35%" r="65%">
      <stop offset="0%" stop-color="#FF77A9"/>
      <stop offset="40%" stop-color="#FF1493"/>
      <stop offset="85%" stop-color="#C70039"/>
      <stop offset="100%" stop-color="#900C3F"/>
    </radialGradient>
    <radialGradient id="highlightGrad" cx="30%" cy="30%" r="50%">
      <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.9"/>
      <stop offset="70%" stop-color="#FF69B4" stop-opacity="0"/>
    </radialGradient>
    <linearGradient id="heartGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FF69B4"/>
      <stop offset="100%" stop-color="#FF1493"/>
    </linearGradient>
    <filter id="kissGlow" x="-30%" y="-30%" width="160%" height="160%">
      <feGaussianBlur stdDeviation="12" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes mainKissPulse {
      0%, 100% { transform: scale(1) rotate(0deg); }
      30% { transform: scale(1.15) rotate(-3deg); }
      60% { transform: scale(0.95) rotate(3deg); }
    }
    @keyframes floatHeart1 {
      0% { transform: translate(0, 0) scale(0.3); opacity: 0; }
      20% { opacity: 1; transform: translate(-30px, -60px) scale(0.7); }
      80% { opacity: 0.9; transform: translate(-80px, -200px) scale(1.1); }
      100% { transform: translate(-110px, -280px) scale(1.3); opacity: 0; }
    }
    @keyframes floatHeart2 {
      0% { transform: translate(0, 0) scale(0.2); opacity: 0; }
      30% { opacity: 1; transform: translate(40px, -70px) scale(0.8); }
      85% { opacity: 0.85; transform: translate(90px, -230px) scale(1.2); }
      100% { transform: translate(120px, -300px) scale(1.4); opacity: 0; }
    }
    @keyframes floatHeart3 {
      0% { transform: translate(0, 0) scale(0.2); opacity: 0; }
      25% { opacity: 1; transform: translate(10px, -50px) scale(0.6); }
      75% { opacity: 0.9; transform: translate(20px, -180px) scale(1); }
      100% { transform: translate(30px, -260px) scale(1.2); opacity: 0; }
    }
    @keyframes sparkleSpin {
      0% { transform: rotate(0deg) scale(0.6); opacity: 0.3; }
      50% { transform: rotate(180deg) scale(1.2); opacity: 1; }
      100% { transform: rotate(360deg) scale(0.6); opacity: 0.3; }
    }
    .main-lips { transform-origin: 250px 280px; animation: mainKissPulse 2.4s infinite ease-in-out; }
    .heart-1 { transform-origin: 200px 240px; animation: floatHeart1 3.2s infinite ease-out; }
    .heart-2 { transform-origin: 300px 240px; animation: floatHeart2 3.6s infinite 0.9s ease-out; }
    .heart-3 { transform-origin: 250px 240px; animation: floatHeart3 2.8s infinite 1.8s ease-out; }
    .sparkle { transform-origin: center; animation: sparkleSpin 2s infinite ease-in-out; }
  </style>

  <!-- Flying Little Hearts -->
  <g class="heart-1" filter="url(#kissGlow)">
    <path d="M200,240 C200,225 180,215 170,225 C160,235 160,250 175,265 L200,290 L225,265 C240,250 240,235 230,225 C220,215 200,225 200,240 Z" fill="url(#heartGrad)"/>
  </g>
  <g class="heart-2" filter="url(#kissGlow)">
    <path d="M300,240 C300,225 280,215 270,225 C260,235 260,250 275,265 L300,290 L325,265 C340,250 340,235 330,225 C320,215 300,225 300,240 Z" fill="url(#heartGrad)"/>
  </g>
  <g class="heart-3" filter="url(#kissGlow)">
    <path d="M250,230 C250,218 235,210 227,218 C219,226 219,238 231,250 L250,270 L269,250 C281,238 281,226 273,218 C265,210 250,218 250,230 Z" fill="#FF1493"/>
  </g>

  <!-- Sparkles -->
  <g class="sparkle" style="transform-origin: 140px 180px;">
    <polygon points="140,165 144,176 155,180 144,184 140,195 136,184 125,180 136,176" fill="#FFF" filter="url(#kissGlow)"/>
  </g>
  <g class="sparkle" style="transform-origin: 370px 170px; animation-delay: 1s;">
    <polygon points="370,155 374,166 385,170 374,174 370,185 366,174 355,170 366,166" fill="#FFF" filter="url(#kissGlow)"/>
  </g>

  <!-- Main 3D Lips (NO Background, NO text) -->
  <g class="main-lips" filter="url(#kissGlow)">
    <!-- Aura Glow Behind Lips -->
    <ellipse cx="250" cy="280" rx="170" ry="100" fill="#FF1493" opacity="0.35" filter="url(#kissGlow)"/>

    <!-- Upper Lip -->
    <path d="M 100,280 C 130,220 190,190 230,215 C 242,223 250,228 258,223 C 298,190 358,220 388,280 C 340,265 290,260 250,265 C 210,260 160,265 100,280 Z" fill="url(#kissGrad)"/>
    
    <!-- Upper Lip Gloss Highlights -->
    <path d="M 160,235 C 190,215 220,220 235,230 C 220,238 185,242 160,235 Z" fill="url(#highlightGrad)"/>
    <path d="M 328,235 C 298,215 268,220 253,230 C 268,238 303,242 328,235 Z" fill="url(#highlightGrad)"/>

    <!-- Lower Lip -->
    <path d="M 100,280 C 145,290 195,300 250,300 C 305,300 355,290 388,280 C 375,340 325,385 250,385 C 175,385 125,340 100,280 Z" fill="url(#kissGrad)"/>

    <!-- Lower Lip Big Plump Gloss -->
    <ellipse cx="250" cy="335" rx="65" ry="25" fill="url(#highlightGrad)"/>
    <ellipse cx="220" cy="340" rx="25" ry="10" fill="#FFFFFF" opacity="0.75"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 2. Golden Fire Dragon (Animated Flying Dragon + Swirling Flame Breath)
    // =========================================================================
    protected function getDragonSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="100%" height="100%">
  <defs>
    <radialGradient id="dragonGold" cx="45%" cy="35%" r="65%">
      <stop offset="0%" stop-color="#FFF3B0"/>
      <stop offset="35%" stop-color="#FFD700"/>
      <stop offset="75%" stop-color="#FF8C00"/>
      <stop offset="100%" stop-color="#B8860B"/>
    </radialGradient>
    <linearGradient id="fireGrad" x1="0%" y1="100%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#FF0000"/>
      <stop offset="40%" stop-color="#FF4500"/>
      <stop offset="80%" stop-color="#FFD700"/>
      <stop offset="100%" stop-color="#FFFFFF"/>
    </linearGradient>
    <filter id="fireGlow" x="-30%" y="-30%" width="160%" height="160%">
      <feGaussianBlur stdDeviation="10" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes dragonHover {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(2deg); }
    }
    @keyframes fireBreath {
      0%, 100% { transform: scale(0.9) translate(0,0); opacity: 0.8; }
      50% { transform: scale(1.15) translate(10px,-10px); opacity: 1; }
    }
    @keyframes wingFlap {
      0%, 100% { transform: rotate(0deg); }
      50% { transform: rotate(-8deg) skewX(4deg); }
    }
    .dragon-body { transform-origin: 300px 300px; animation: dragonHover 3.5s infinite ease-in-out; }
    .fire-breath { transform-origin: 180px 180px; animation: fireBreath 1.5s infinite ease-in-out; }
    .dragon-wing { transform-origin: 320px 240px; animation: wingFlap 2s infinite ease-in-out; }
  </style>

  <g class="dragon-body" filter="url(#fireGlow)">
    <!-- Swirling Flame Aura -->
    <ellipse cx="300" cy="300" rx="220" ry="180" fill="#FF8C00" opacity="0.3" filter="url(#fireGlow)"/>

    <!-- Dragon Wings -->
    <g class="dragon-wing">
      <path d="M 320,240 C 370,120 480,80 540,110 C 510,170 470,200 420,220 C 470,240 490,280 470,320 C 420,290 380,270 320,260 Z" fill="url(#dragonGold)" stroke="#FFD700" stroke-width="4"/>
      <path d="M 340,230 L 520,125 M 345,245 L 450,220 M 345,255 L 450,305" stroke="#FF4500" stroke-width="3"/>
    </g>

    <!-- Dragon Coiled Serpent Body -->
    <path d="M 220,180 C 260,140 350,140 380,200 C 410,260 380,340 310,360 C 220,380 140,320 150,240 C 160,160 260,110 360,120 C 460,130 520,230 480,340 C 440,450 310,500 200,480 C 130,460 80,390 100,320 C 110,290 140,280 150,300 C 140,340 170,410 240,430 C 320,450 410,400 430,320 C 450,240 400,180 340,170 C 280,160 210,190 200,240" fill="none" stroke="url(#dragonGold)" stroke-width="42" stroke-linecap="round"/>

    <!-- Dorsal Spines -->
    <path d="M 240,150 L 250,125 L 265,155 L 285,130 L 295,160 L 320,135 L 330,168 L 360,145 L 365,180" fill="#FF4500"/>

    <!-- Dragon Head & Jaws -->
    <g transform="translate(180, 160)">
      <!-- Horns -->
      <path d="M 30,-10 C 60,-50 110,-70 140,-60 C 120,-40 90,-20 70,-5" fill="url(#dragonGold)" stroke="#FF4500" stroke-width="2"/>
      <path d="M 10,-5 C 30,-35 70,-50 90,-45 C 75,-30 55,-15 40,-2" fill="url(#dragonGold)"/>
      
      <!-- Upper Jaw & Snout -->
      <path d="M -60,0 C -30,-25 30,-20 60,10 C 40,30 -10,30 -60,0 Z" fill="url(#dragonGold)"/>
      <!-- Lower Jaw -->
      <path d="M -40,15 C -20,45 20,40 40,20 C 10,25 -20,25 -40,15 Z" fill="url(#dragonGold)"/>

      <!-- Glowing Eye -->
      <circle cx="15" cy="-2" r="7" fill="#FF0000"/>
      <circle cx="17" cy="-3" r="3" fill="#FFFF00"/>
      
      <!-- Whiskers / Mane -->
      <path d="M -20,10 C -60,40 -90,70 -120,90" fill="none" stroke="#FFD700" stroke-width="4" stroke-linecap="round"/>
      <path d="M -10,15 C -40,50 -60,90 -80,120" fill="none" stroke="#FF8C00" stroke-width="3" stroke-linecap="round"/>
    </g>

    <!-- Claws -->
    <g transform="translate(140, 260)">
      <path d="M 0,0 L -30,20 M 0,5 L -35,35 M 5,10 L -20,45" stroke="#FFD700" stroke-width="8" stroke-linecap="round"/>
    </g>
  </g>

  <!-- Animated Fire Breath -->
  <g class="fire-breath" filter="url(#fireGlow)">
    <path d="M 110,165 C 50,140 -20,110 -90,90 C -40,130 -10,180 -70,220 C 10,190 60,180 110,175 Z" fill="url(#fireGrad)"/>
    <circle cx="-30" cy="130" r="18" fill="#FFD700"/>
    <circle cx="-60" cy="180" r="14" fill="#FF4500"/>
    <circle cx="20" cy="150" r="12" fill="#FFFFFF"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 3. VIP Helicopter (Executive Helicopter with Spinning Main & Tail Rotors)
    // =========================================================================
    protected function getHelicopterSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 500" width="100%" height="100%">
  <defs>
    <linearGradient id="heliBody" x1="0%" y1="0%" x2="100%" y2="50%">
      <stop offset="0%" stop-color="#1E293B"/>
      <stop offset="50%" stop-color="#334155"/>
      <stop offset="100%" stop-color="#0F172A"/>
    </linearGradient>
    <linearGradient id="heliGold" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#F59E0B"/>
      <stop offset="50%" stop-color="#FDE047"/>
      <stop offset="100%" stop-color="#D97706"/>
    </linearGradient>
    <linearGradient id="glassGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.8"/>
      <stop offset="100%" stop-color="#0284C7" stop-opacity="0.3"/>
    </linearGradient>
    <filter id="heliGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes heliFloat {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      25% { transform: translateY(-12px) rotate(-1deg); }
      75% { transform: translateY(8px) rotate(1.5deg); }
    }
    @keyframes mainRotorSpin {
      0% { transform: scaleX(1); opacity: 0.9; }
      50% { transform: scaleX(0.08); opacity: 0.4; }
      100% { transform: scaleX(1); opacity: 0.9; }
    }
    @keyframes tailRotorSpin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    @keyframes beamPulse {
      0%, 100% { opacity: 0.3; transform: scaleY(0.9); }
      50% { opacity: 0.7; transform: scaleY(1.1); }
    }
    .heli-group { transform-origin: 300px 250px; animation: heliFloat 3s infinite ease-in-out; }
    .main-rotor { transform-origin: 260px 145px; animation: mainRotorSpin 0.12s infinite linear; }
    .tail-rotor { transform-origin: 520px 200px; animation: tailRotorSpin 0.08s infinite linear; }
    .searchlight { transform-origin: 120px 320px; animation: beamPulse 1.8s infinite ease-in-out; }
  </style>

  <g class="heli-group">
    <!-- Downward Searchlight Beam -->
    <g class="searchlight">
      <polygon points="120,320 0,480 180,480" fill="url(#glassGrad)" opacity="0.45" filter="url(#heliGlow)"/>
    </g>

    <!-- Main Rotor Mast -->
    <rect x="254" y="145" width="12" height="35" rx="3" fill="url(#heliGold)"/>

    <!-- Spinning Main Rotor Blades -->
    <g class="main-rotor">
      <ellipse cx="260" cy="145" rx="250" ry="10" fill="url(#heliGold)" opacity="0.85" filter="url(#heliGlow)"/>
      <line x1="10" y1="145" x2="510" y2="145" stroke="#FFFFFF" stroke-width="4"/>
    </g>

    <!-- Tail Boom -->
    <path d="M 320,240 L 520,200 L 520,220 L 320,270 Z" fill="url(#heliBody)"/>
    <path d="M 320,248 L 520,208" stroke="url(#heliGold)" stroke-width="6"/>

    <!-- Vertical Tail Fin & Stabilizer -->
    <polygon points="500,210 535,140 550,140 530,240" fill="url(#heliGold)"/>

    <!-- Spinning Tail Rotor -->
    <g class="tail-rotor">
      <line x1="520" y1="160" x2="520" y2="240" stroke="#FDE047" stroke-width="6"/>
      <line x1="480" y1="200" x2="560" y2="200" stroke="#FDE047" stroke-width="6"/>
      <circle cx="520" cy="200" r="8" fill="#EF4444"/>
    </g>

    <!-- Main Fuselage Cabin -->
    <path d="M 120,270 C 110,210 180,180 260,180 C 330,180 360,220 340,290 C 320,320 220,330 140,310 Z" fill="url(#heliBody)" stroke="url(#heliGold)" stroke-width="3.5" filter="url(#heliGlow)"/>

    <!-- Golden Racing Stripe -->
    <path d="M 125,275 C 190,265 270,265 338,285" stroke="url(#heliGold)" stroke-width="8" fill="none"/>

    <!-- Cockpit Windshield (Glass) -->
    <path d="M 125,260 C 135,215 190,195 230,195 L 225,260 C 180,265 145,265 125,260 Z" fill="url(#glassGrad)" stroke="#38BDF8" stroke-width="2"/>

    <!-- Passenger Windows -->
    <rect x="245" y="210" width="35" height="30" rx="6" fill="url(#glassGrad)" stroke="#38BDF8" stroke-width="1.5"/>
    <rect x="290" y="215" width="35" height="28" rx="6" fill="url(#glassGrad)" stroke="#38BDF8" stroke-width="1.5"/>

    <!-- Landing Skids & Struts -->
    <path d="M 190,320 L 170,365 M 280,320 L 260,365" stroke="url(#heliGold)" stroke-width="6" stroke-linecap="round"/>
    <path d="M 110,365 L 330,365 C 345,365 355,355 355,340" fill="none" stroke="url(#heliGold)" stroke-width="8" stroke-linecap="round"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 4. Luxury Supercar (Aerodynamic Exotic Sports Car + Spinning Neon Wheels)
    // =========================================================================
    protected function getSupercarSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 400" width="100%" height="100%">
  <defs>
    <linearGradient id="carBody" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFE066"/>
      <stop offset="40%" stop-color="#F59E0B"/>
      <stop offset="85%" stop-color="#DC2626"/>
      <stop offset="100%" stop-color="#7F1D1D"/>
    </linearGradient>
    <linearGradient id="neonCyan" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#00F0FF"/>
      <stop offset="100%" stop-color="#0066FF"/>
    </linearGradient>
    <linearGradient id="wheelRim" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FDE047"/>
      <stop offset="100%" stop-color="#B45309"/>
    </linearGradient>
    <filter id="carGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes wheelRotate {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    @keyframes carDrift {
      0%, 100% { transform: translateY(0px) skewX(0deg); }
      50% { transform: translateY(-6px) skewX(-1.5deg); }
    }
    @keyframes speedTrail {
      0% { transform: translateX(0px); opacity: 0.8; }
      50% { transform: translateX(-40px); opacity: 0.3; }
      100% { transform: translateX(0px); opacity: 0.8; }
    }
    .car-chassis { animation: carDrift 1.8s infinite ease-in-out; }
    .wheel-front { transform-origin: 155px 270px; animation: wheelRotate 0.35s infinite linear; }
    .wheel-rear { transform-origin: 445px 270px; animation: wheelRotate 0.35s infinite linear; }
    .speed-lines { animation: speedTrail 0.8s infinite ease-in-out; }
  </style>

  <g class="car-chassis">
    <!-- Neon Underglow -->
    <ellipse cx="300" cy="285" rx="240" ry="22" fill="#00F0FF" opacity="0.6" filter="url(#carGlow)"/>

    <!-- Speed Flame Trails Behind -->
    <g class="speed-lines" filter="url(#carGlow)">
      <line x1="520" y1="240" x2="600" y2="240" stroke="#00F0FF" stroke-width="4" stroke-linecap="round"/>
      <line x1="540" y1="260" x2="590" y2="260" stroke="#FF0055" stroke-width="5" stroke-linecap="round"/>
      <line x1="510" y1="275" x2="580" y2="275" stroke="#F59E0B" stroke-width="6" stroke-linecap="round"/>
    </g>

    <!-- Rear Wing Spoiler -->
    <polygon points="480,170 540,155 535,175 490,185" fill="#7F1D1D" stroke="#F59E0B" stroke-width="2"/>
    <line x1="500" y1="180" x2="495" y2="215" stroke="#F59E0B" stroke-width="5"/>

    <!-- Main Aerodynamic Car Body Shell -->
    <path d="M 60,265 C 75,225 120,215 170,215 C 215,160 270,140 370,140 C 440,140 480,180 525,215 C 555,225 565,255 545,275 C 500,285 470,240 420,240 C 370,240 350,285 240,285 C 200,285 180,240 130,240 C 85,240 65,275 60,265 Z" fill="url(#carBody)" stroke="#FDE047" stroke-width="3" filter="url(#carGlow)"/>

    <!-- Cockpit Canopy / Tinted Glass -->
    <path d="M 230,205 C 265,160 300,150 365,150 C 420,150 445,180 470,205 Z" fill="#0F172A" stroke="#00F0FF" stroke-width="2.5"/>

    <!-- Carbon Fiber Side Air Intake -->
    <polygon points="360,220 415,220 395,255 350,255" fill="#1E293B" stroke="#F59E0B" stroke-width="1.5"/>

    <!-- Sleek LED Headlights -->
    <polygon points="75,240 115,235 105,248" fill="#00F0FF" filter="url(#carGlow)"/>
    <polygon points="535,235 550,245 530,250" fill="#FF0055" filter="url(#carGlow)"/>

    <!-- Front Wheel Assembly -->
    <g class="wheel-front">
      <circle cx="155" cy="270" r="42" fill="#0F172A" stroke="url(#neonCyan)" stroke-width="8" filter="url(#carGlow)"/>
      <circle cx="155" cy="270" r="28" fill="none" stroke="url(#wheelRim)" stroke-width="4"/>
      <!-- Spokes -->
      <line x1="155" y1="242" x2="155" y2="298" stroke="url(#wheelRim)" stroke-width="4"/>
      <line x1="127" y1="270" x2="183" y2="270" stroke="url(#wheelRim)" stroke-width="4"/>
      <circle cx="155" cy="270" r="8" fill="#FF0055"/>
    </g>

    <!-- Rear Wheel Assembly -->
    <g class="wheel-rear">
      <circle cx="445" cy="270" r="45" fill="#0F172A" stroke="url(#neonCyan)" stroke-width="9" filter="url(#carGlow)"/>
      <circle cx="445" cy="270" r="30" fill="none" stroke="url(#wheelRim)" stroke-width="4"/>
      <!-- Spokes -->
      <line x1="445" y1="240" x2="445" y2="300" stroke="url(#wheelRim)" stroke-width="4"/>
      <line x1="415" y1="270" x2="475" y2="270" stroke="url(#wheelRim)" stroke-width="4"/>
      <circle cx="445" cy="270" r="9" fill="#FF0055"/>
    </g>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 5. Cyber Sports Bike
    // =========================================================================
    protected function getSportsBikeSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 550 400" width="100%" height="100%">
  <defs>
    <linearGradient id="bikeNeon" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#00F0FF"/>
      <stop offset="100%" stop-color="#7000FF"/>
    </linearGradient>
    <filter id="bikeGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes bikeWheel { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    @keyframes bikeBounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-5px); } }
    .bike-body { animation: bikeBounce 1.4s infinite ease-in-out; }
    .bike-w1 { transform-origin: 120px 270px; animation: bikeWheel 0.3s infinite linear; }
    .bike-w2 { transform-origin: 410px 270px; animation: bikeWheel 0.3s infinite linear; }
  </style>

  <g class="bike-body">
    <!-- Neon Ground Aura -->
    <ellipse cx="265" cy="310" rx="200" ry="18" fill="#00F0FF" opacity="0.4" filter="url(#bikeGlow)"/>

    <!-- Front Wheel -->
    <g class="bike-w1">
      <circle cx="120" cy="270" r="48" fill="#0F172A" stroke="#00F0FF" stroke-width="10" filter="url(#bikeGlow)"/>
      <circle cx="120" cy="270" r="30" fill="none" stroke="#F59E0B" stroke-width="4"/>
      <line x1="120" y1="225" x2="120" y2="315" stroke="#00F0FF" stroke-width="4"/>
      <line x1="75" y1="270" x2="165" y2="270" stroke="#00F0FF" stroke-width="4"/>
    </g>

    <!-- Rear Wheel -->
    <g class="bike-w2">
      <circle cx="410" cy="270" r="50" fill="#0F172A" stroke="#00F0FF" stroke-width="12" filter="url(#bikeGlow)"/>
      <circle cx="410" cy="270" r="32" fill="none" stroke="#F59E0B" stroke-width="4"/>
      <line x1="410" y1="222" x2="410" y2="318" stroke="#00F0FF" stroke-width="4"/>
      <line x1="362" y1="270" x2="458" y2="270" stroke="#00F0FF" stroke-width="4"/>
    </g>

    <!-- Frame & Bodywork -->
    <path d="M 120,270 L 190,160 L 250,140 L 330,170 L 410,270 L 300,270 L 240,210 Z" fill="#1E293B" stroke="url(#bikeNeon)" stroke-width="6" filter="url(#bikeGlow)"/>
    <!-- Gas Tank & Seat -->
    <path d="M 180,160 C 210,120 270,120 300,160 L 370,180 L 350,210 L 260,180 Z" fill="#F59E0B" stroke="#FDE047" stroke-width="3"/>
    <!-- Front Fork & Handlebars -->
    <line x1="120" y1="270" x2="185" y2="135" stroke="#FDE047" stroke-width="8" stroke-linecap="round"/>
    <circle cx="185" cy="135" r="10" fill="#00F0FF" filter="url(#bikeGlow)"/>
    <!-- Exhaust Pipe & Flame -->
    <path d="M 280,260 L 380,280 L 430,260" stroke="#94A3B8" stroke-width="8" fill="none" stroke-linecap="round"/>
    <polygon points="435,260 480,250 460,265 480,270" fill="#FF0055" filter="url(#bikeGlow)"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 6. Mega Luxury Yacht (Cruising on Animated Waves)
    // =========================================================================
    protected function getYachtSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 450" width="100%" height="100%">
  <defs>
    <linearGradient id="yachtHull" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFFFFF"/>
      <stop offset="70%" stop-color="#E2E8F0"/>
      <stop offset="100%" stop-color="#0F172A"/>
    </linearGradient>
    <linearGradient id="oceanWave" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#0284C7"/>
      <stop offset="50%" stop-color="#38BDF8"/>
      <stop offset="100%" stop-color="#0369A1"/>
    </linearGradient>
    <filter id="yachtGlow">
      <feGaussianBlur stdDeviation="6" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes yachtBob {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-8px) rotate(1.5deg); }
    }
    @keyframes waveMove {
      0% { transform: translateX(0px); }
      50% { transform: translateX(-30px); }
      100% { transform: translateX(0px); }
    }
    .yacht-float { animation: yachtBob 3s infinite ease-in-out; }
    .wave-anim { animation: waveMove 2s infinite ease-in-out; }
  </style>

  <!-- Yacht Structure -->
  <g class="yacht-float">
    <!-- Main Hull -->
    <path d="M 60,280 L 150,330 L 480,330 L 530,260 L 220,260 Z" fill="url(#yachtHull)" stroke="#F59E0B" stroke-width="3" filter="url(#yachtGlow)"/>

    <!-- Gold Waterline Stripe -->
    <path d="M 120,310 L 490,310" stroke="#F59E0B" stroke-width="6"/>

    <!-- Deck 1 & Windows -->
    <path d="M 160,260 L 200,210 L 440,210 L 470,260 Z" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>
    <rect x="220" y="225" width="25" height="18" rx="4" fill="#0284C7"/>
    <rect x="260" y="225" width="25" height="18" rx="4" fill="#0284C7"/>
    <rect x="300" y="225" width="25" height="18" rx="4" fill="#0284C7"/>
    <rect x="340" y="225" width="25" height="18" rx="4" fill="#0284C7"/>
    <rect x="380" y="225" width="25" height="18" rx="4" fill="#0284C7"/>

    <!-- Flybridge / Deck 2 -->
    <path d="M 230,210 L 260,160 L 390,160 L 410,210 Z" fill="#FFFFFF" stroke="#F59E0B" stroke-width="2"/>
    <path d="M 270,175 L 340,175 L 330,195 L 260,195 Z" fill="#38BDF8"/>

    <!-- Radar Mast & Satellite Domes -->
    <line x1="330" y1="160" x2="330" y2="120" stroke="#F59E0B" stroke-width="4"/>
    <circle cx="330" cy="115" r="10" fill="#FFFFFF" stroke="#F59E0B" stroke-width="2"/>
    <circle cx="355" cy="135" r="8" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2"/>

    <!-- Bow Railing -->
    <path d="M 60,280 L 160,260" stroke="#F59E0B" stroke-width="3"/>
  </g>

  <!-- Animated Ocean Waves at Bottom -->
  <g class="wave-anim" filter="url(#yachtGlow)">
    <path d="M -50,340 Q 50,310 150,340 T 350,340 T 550,340 T 700,340 L 700,420 L -50,420 Z" fill="url(#oceanWave)" opacity="0.8"/>
    <path d="M -30,360 Q 70,330 170,360 T 370,360 T 570,360 T 720,360 L 720,440 L -30,440 Z" fill="#0369A1" opacity="0.9"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 7. Private Golden Jet
    // =========================================================================
    protected function getPrivateJetSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 450" width="100%" height="100%">
  <defs>
    <linearGradient id="jetGold" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FEF08A"/>
      <stop offset="40%" stop-color="#F59E0B"/>
      <stop offset="100%" stop-color="#B45309"/>
    </linearGradient>
    <filter id="jetGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes jetFlight {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      50% { transform: translate(15px, -15px) rotate(-2deg); }
    }
    @keyframes jetTrail {
      0% { transform: scaleX(0.8); opacity: 0.5; }
      50% { transform: scaleX(1.2); opacity: 1; }
      100% { transform: scaleX(0.8); opacity: 0.5; }
    }
    .jet-anim { animation: jetFlight 3s infinite ease-in-out; }
    .trail-anim { animation: jetTrail 1s infinite ease-in-out; }
  </style>

  <g class="jet-anim" filter="url(#jetGlow)">
    <!-- Contrails Behind Engines -->
    <g class="trail-anim">
      <line x1="380" y1="210" x2="580" y2="210" stroke="#38BDF8" stroke-width="6" stroke-linecap="round" opacity="0.7"/>
      <line x1="380" y1="250" x2="550" y2="250" stroke="#38BDF8" stroke-width="4" stroke-linecap="round" opacity="0.6"/>
    </g>

    <!-- Main Swept Wings -->
    <polygon points="260,225 360,90 410,105 320,235" fill="url(#jetGold)" stroke="#FEF08A" stroke-width="2"/>
    <polygon points="260,240 380,360 420,345 330,230" fill="url(#jetGold)" stroke="#FEF08A" stroke-width="2"/>

    <!-- Fuselage Body -->
    <path d="M 60,230 C 130,200 240,205 450,205 L 510,140 L 535,145 L 490,225 C 490,240 450,255 60,230 Z" fill="url(#jetGold)" stroke="#FEF08A" stroke-width="3"/>

    <!-- Cockpit Glass -->
    <path d="M 90,220 C 110,210 140,210 155,225 Z" fill="#0F172A" stroke="#38BDF8" stroke-width="2"/>

    <!-- Jet Engines -->
    <rect x="360" y="195" width="55" height="20" rx="8" fill="#1E293B" stroke="#FEF08A" stroke-width="2"/>
    <circle cx="360" cy="205" r="7" fill="#00F0FF" filter="url(#jetGlow)"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 8. Space Rocket Launch (Blasting off with Roaring Fire Plume)
    // =========================================================================
    protected function getRocketSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 600" width="100%" height="100%">
  <defs>
    <linearGradient id="rocketBody" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#FFFFFF"/>
      <stop offset="50%" stop-color="#F1F5F9"/>
      <stop offset="100%" stop-color="#94A3B8"/>
    </linearGradient>
    <linearGradient id="rocketFire" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%" stop-color="#FFFFFF"/>
      <stop offset="30%" stop-color="#FFD700"/>
      <stop offset="70%" stop-color="#FF4500"/>
      <stop offset="100%" stop-color="#990000"/>
    </linearGradient>
    <filter id="rocketGlow">
      <feGaussianBlur stdDeviation="10" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes rocketShake {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      25% { transform: translateY(-15px) rotate(-1deg); }
      75% { transform: translateY(-5px) rotate(1deg); }
    }
    @keyframes fireThrust {
      0%, 100% { transform: scaleY(1); opacity: 0.9; }
      50% { transform: scaleY(1.3); opacity: 1; }
    }
    .rocket-anim { animation: rocketShake 1.5s infinite ease-in-out; }
    .flame-anim { transform-origin: 250px 380px; animation: fireThrust 0.15s infinite ease-in-out; }
  </style>

  <g class="rocket-anim" filter="url(#rocketGlow)">
    <!-- Exhaust Flame Plume -->
    <g class="flame-anim">
      <path d="M 215,380 C 200,460 210,540 250,590 C 290,540 300,460 285,380 Z" fill="url(#rocketFire)"/>
      <ellipse cx="250" cy="430" rx="25" ry="40" fill="#FFFFFF"/>
    </g>

    <!-- Side Fins -->
    <polygon points="175,340 200,280 200,370 150,390" fill="#EF4444" stroke="#DC2626" stroke-width="2"/>
    <polygon points="325,340 300,280 300,370 350,390" fill="#EF4444" stroke="#DC2626" stroke-width="2"/>

    <!-- Main Rocket Fuselage -->
    <path d="M 250,80 C 200,160 200,330 200,380 L 300,380 C 300,330 300,160 250,80 Z" fill="url(#rocketBody)" stroke="#CBD5E1" stroke-width="3"/>

    <!-- Red Nose Cone -->
    <path d="M 250,80 C 220,130 208,160 208,180 L 292,180 C 292,160 280,130 250,80 Z" fill="#EF4444"/>

    <!-- Round Porthole Window -->
    <circle cx="250" cy="240" r="28" fill="#0F172A" stroke="#38BDF8" stroke-width="5"/>
    <circle cx="250" cy="240" r="18" fill="#38BDF8" opacity="0.8"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 9. Royal Diamond Castle
    // =========================================================================
    protected function getDiamondCastleSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <linearGradient id="castleGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FBCFE8"/>
      <stop offset="40%" stop-color="#EC4899"/>
      <stop offset="80%" stop-color="#8B5CF6"/>
      <stop offset="100%" stop-color="#4C1D95"/>
    </linearGradient>
    <filter id="castleGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes castleShimmer {
      0%, 100% { transform: scale(1); filter: drop-shadow(0 0 15px #EC4899); }
      50% { transform: scale(1.04); filter: drop-shadow(0 0 30px #F59E0B); }
    }
    .castle-anim { transform-origin: 250px 300px; animation: castleShimmer 3s infinite ease-in-out; }
  </style>

  <g class="castle-anim">
    <!-- Magical Back Aura -->
    <circle cx="250" cy="260" r="190" fill="#EC4899" opacity="0.25" filter="url(#castleGlow)"/>

    <!-- Base Walls -->
    <rect x="130" y="270" width="240" height="150" rx="10" fill="url(#castleGrad)" stroke="#FDE047" stroke-width="3"/>
    
    <!-- Left Tower -->
    <rect x="100" y="200" width="60" height="200" rx="6" fill="url(#castleGrad)" stroke="#FDE047" stroke-width="2"/>
    <polygon points="130,110 90,200 170,200" fill="#FDE047" stroke="#F59E0B" stroke-width="2"/>

    <!-- Right Tower -->
    <rect x="340" y="200" width="60" height="200" rx="6" fill="url(#castleGrad)" stroke="#FDE047" stroke-width="2"/>
    <polygon points="370,110 330,200 410,200" fill="#FDE047" stroke="#F59E0B" stroke-width="2"/>

    <!-- Center Grand Spire -->
    <rect x="210" y="170" width="80" height="150" rx="8" fill="url(#castleGrad)" stroke="#FDE047" stroke-width="3"/>
    <polygon points="250,50 195,170 305,170" fill="#FDE047" stroke="#F59E0B" stroke-width="3" filter="url(#castleGlow)"/>

    <!-- Grand Arch Gate -->
    <path d="M 220,420 L 220,340 C 220,315 280,315 280,340 L 280,420 Z" fill="#0F172A" stroke="#FDE047" stroke-width="3"/>

    <!-- Diamond Star on Peak -->
    <polygon points="250,25 256,40 270,45 256,50 250,65 244,50 230,45 244,40" fill="#FFFFFF" filter="url(#castleGlow)"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 10. Royal SVIP Crown
    // =========================================================================
    protected function getRoyalCrownSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 400" width="100%" height="100%">
  <defs>
    <linearGradient id="crownGold" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFFBEB"/>
      <stop offset="30%" stop-color="#FDE047"/>
      <stop offset="70%" stop-color="#F59E0B"/>
      <stop offset="100%" stop-color="#B45309"/>
    </linearGradient>
    <filter id="crownGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes crownFloat {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-12px) rotate(1.5deg); }
    }
    .crown-anim { transform-origin: 250px 220px; animation: crownFloat 2.5s infinite ease-in-out; }
  </style>

  <g class="crown-anim" filter="url(#crownGlow)">
    <!-- Glowing Aura -->
    <ellipse cx="250" cy="220" rx="180" ry="110" fill="#F59E0B" opacity="0.3" filter="url(#crownGlow)"/>

    <!-- Main Crown Structure -->
    <path d="M 70,280 L 110,130 L 190,210 L 250,90 L 310,210 L 390,130 L 430,280 C 350,305 150,305 70,280 Z" fill="url(#crownGold)" stroke="#FFFFFF" stroke-width="3"/>

    <!-- Crown Base Band -->
    <path d="M 70,280 C 150,305 350,305 430,280 L 420,320 C 340,345 160,345 80,320 Z" fill="url(#crownGold)" stroke="#B45309" stroke-width="2"/>

    <!-- Jewels on Peaks -->
    <circle cx="110" cy="130" r="14" fill="#EF4444" stroke="#FFF" stroke-width="2"/>
    <circle cx="250" cy="90" r="18" fill="#3B82F6" stroke="#FFF" stroke-width="3" filter="url(#crownGlow)"/>
    <circle cx="390" cy="130" r="14" fill="#10B981" stroke="#FFF" stroke-width="2"/>

    <!-- Inset Rubies & Diamonds along base -->
    <circle cx="140" cy="305" r="9" fill="#EF4444"/>
    <circle cx="200" cy="315" r="10" fill="#38BDF8"/>
    <circle cx="250" cy="318" r="12" fill="#EF4444" stroke="#FFF" stroke-width="1.5"/>
    <circle cx="300" cy="315" r="10" fill="#38BDF8"/>
    <circle cx="360" cy="305" r="9" fill="#EF4444"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 11. Diamond Ring
    // =========================================================================
    protected function getDiamondRingSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <linearGradient id="ringMetal" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFFFFF"/>
      <stop offset="50%" stop-color="#E2E8F0"/>
      <stop offset="100%" stop-color="#64748B"/>
    </linearGradient>
    <linearGradient id="gemDiamond" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFFFFF"/>
      <stop offset="30%" stop-color="#BAE6FD"/>
      <stop offset="70%" stop-color="#38BDF8"/>
      <stop offset="100%" stop-color="#0284C7"/>
    </linearGradient>
    <filter id="ringGlow">
      <feGaussianBlur stdDeviation="10" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes diamondPulse {
      0%, 100% { transform: scale(1); filter: drop-shadow(0 0 15px #38BDF8); }
      50% { transform: scale(1.08); filter: drop-shadow(0 0 30px #FFFFFF); }
    }
    .ring-anim { transform-origin: 250px 250px; animation: diamondPulse 2.5s infinite ease-in-out; }
  </style>

  <g class="ring-anim">
    <!-- Circular Platinum Band -->
    <ellipse cx="250" cy="310" rx="140" ry="110" fill="none" stroke="url(#ringMetal)" stroke-width="26" filter="url(#ringGlow)"/>
    <ellipse cx="250" cy="310" rx="140" ry="110" fill="none" stroke="#FFFFFF" stroke-width="6"/>

    <!-- Large Solitaire Diamond -->
    <g transform="translate(250, 160)" filter="url(#ringGlow)">
      <polygon points="-70,-40 70,-40 100,0 0,90 -100,0" fill="url(#gemDiamond)" stroke="#FFFFFF" stroke-width="3"/>
      <!-- Facet Lines -->
      <polygon points="-40,-40 40,-40 60,0 0,90 -60,0" fill="#E0F2FE" opacity="0.6"/>
      <line x1="-70" y1="-40" x2="-60" y2="0" stroke="#FFF" stroke-width="2"/>
      <line x1="70" y1="-40" x2="60" y2="0" stroke="#FFF" stroke-width="2"/>
      <line x1="-100" y1="0" x2="100" y2="0" stroke="#FFF" stroke-width="2"/>
      <circle cx="-10" cy="-10" r="8" fill="#FFF"/>
    </g>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 12. 999 Red Roses Bouquet
    // =========================================================================
    protected function getRoseBouquetSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <radialGradient id="roseRed" cx="40%" cy="40%" r="60%">
      <stop offset="0%" stop-color="#FF4D6D"/>
      <stop offset="50%" stop-color="#C9184A"/>
      <stop offset="100%" stop-color="#590D22"/>
    </radialGradient>
    <linearGradient id="wrapGold" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#1E293B"/>
      <stop offset="50%" stop-color="#334155"/>
      <stop offset="100%" stop-color="#0F172A"/>
    </linearGradient>
    <filter id="roseGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes roseBloom {
      0%, 100% { transform: scale(1) rotate(0deg); }
      50% { transform: scale(1.05) rotate(2deg); }
    }
    .rose-anim { transform-origin: 250px 250px; animation: roseBloom 2.8s infinite ease-in-out; }
  </style>

  <g class="rose-anim" filter="url(#roseGlow)">
    <!-- Paper Wrapper Bottom Cone -->
    <polygon points="250,470 120,250 380,250" fill="url(#wrapGold)" stroke="#F59E0B" stroke-width="4"/>

    <!-- Gold Ribbon Bow -->
    <circle cx="250" cy="330" r="16" fill="#F59E0B"/>
    <path d="M 250,330 C 200,300 180,360 250,330 C 320,300 300,360 250,330 Z" fill="#FDE047" stroke="#F59E0B" stroke-width="2"/>

    <!-- Clustered Blooming Rose Buds -->
    <!-- Center Rose -->
    <circle cx="250" cy="180" r="45" fill="url(#roseRed)" stroke="#FF758F" stroke-width="3"/>
    <!-- Surrounding Roses -->
    <circle cx="180" cy="190" r="38" fill="url(#roseRed)" stroke="#FF758F" stroke-width="2"/>
    <circle cx="320" cy="190" r="38" fill="url(#roseRed)" stroke="#FF758F" stroke-width="2"/>
    <circle cx="215" cy="125" r="36" fill="url(#roseRed)" stroke="#FF758F" stroke-width="2"/>
    <circle cx="285" cy="125" r="36" fill="url(#roseRed)" stroke="#FF758F" stroke-width="2"/>
    <circle cx="140" cy="235" r="32" fill="url(#roseRed)"/>
    <circle cx="360" cy="235" r="32" fill="url(#roseRed)"/>
    <circle cx="250" cy="235" r="35" fill="url(#roseRed)"/>

    <!-- Rose Petal Swirls -->
    <path d="M 250,165 C 235,170 235,190 250,195 C 265,190 265,170 250,165 Z" fill="#FF758F"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 13. Heart Fireworks
    // =========================================================================
    protected function getHeartFireworksSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <filter id="fwGlow">
      <feGaussianBlur stdDeviation="6" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes fwBurst {
      0% { transform: scale(0.2); opacity: 0; }
      50% { opacity: 1; transform: scale(1.1); }
      100% { transform: scale(1.3); opacity: 0; }
    }
    .fw-anim { transform-origin: 250px 250px; animation: fwBurst 1.8s infinite ease-out; }
  </style>

  <g class="fw-anim" filter="url(#fwGlow)">
    <!-- Outer Heart Rings -->
    <path d="M 250,150 C 250,90 150,50 100,120 C 50,190 100,280 250,380 C 400,280 450,190 400,120 C 350,50 250,90 250,150 Z" fill="none" stroke="#EC4899" stroke-width="8" stroke-dasharray="14 10"/>
    <path d="M 250,180 C 250,130 180,100 140,150 C 100,200 140,260 250,330 C 360,260 400,200 360,150 C 320,100 250,130 250,180 Z" fill="none" stroke="#F59E0B" stroke-width="6" stroke-dasharray="10 8"/>

    <!-- Exploding Spark Rays -->
    <line x1="250" y1="240" x2="250" y2="40" stroke="#FDE047" stroke-width="4"/>
    <line x1="250" y1="240" x2="450" y2="240" stroke="#FF0055" stroke-width="4"/>
    <line x1="250" y1="240" x2="50" y2="240" stroke="#FF0055" stroke-width="4"/>
    <line x1="250" y1="240" x2="390" y2="100" stroke="#00F0FF" stroke-width="4"/>
    <line x1="250" y1="240" x2="110" y2="100" stroke="#00F0FF" stroke-width="4"/>

    <!-- Center Flash -->
    <circle cx="250" cy="240" r="22" fill="#FFFFFF" filter="url(#fwGlow)"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 14. Luxury Champagne Pop
    // =========================================================================
    protected function getChampagneSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <linearGradient id="champBottle" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#064E3B"/>
      <stop offset="50%" stop-color="#047857"/>
      <stop offset="100%" stop-color="#022C22"/>
    </linearGradient>
    <filter id="champGlow">
      <feGaussianBlur stdDeviation="6" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes bottlePop {
      0%, 100% { transform: rotate(-25deg) translateY(0); }
      50% { transform: rotate(-28deg) translateY(-10px); }
    }
    @keyframes fizzSpray {
      0% { opacity: 0; transform: scale(0.5); }
      50% { opacity: 1; transform: scale(1.2); }
      100% { opacity: 0; transform: scale(1.5); }
    }
    .bottle-anim { transform-origin: 320px 380px; animation: bottlePop 2s infinite ease-in-out; }
    .fizz-anim { transform-origin: 160px 140px; animation: fizzSpray 1.2s infinite ease-out; }
  </style>

  <!-- Champagne Bottle -->
  <g class="bottle-anim" filter="url(#champGlow)">
    <path d="M 300,420 C 260,420 240,360 240,290 L 260,200 L 260,150 L 285,150 L 285,200 L 360,290 C 360,360 340,420 300,420 Z" fill="url(#champBottle)" stroke="#F59E0B" stroke-width="3"/>
    <!-- Gold Neck Foil -->
    <rect x="258" y="150" width="29" height="40" fill="#F59E0B"/>
    <!-- Bottle Label -->
    <rect x="260" y="270" width="80" height="50" rx="6" fill="#FFFBEB" stroke="#F59E0B" stroke-width="2"/>
  </g>

  <!-- Golden Fizz Bubbles Spraying -->
  <g class="fizz-anim" filter="url(#champGlow)">
    <circle cx="160" cy="140" r="14" fill="#FDE047"/>
    <circle cx="130" cy="110" r="18" fill="#F59E0B"/>
    <circle cx="110" cy="70" r="12" fill="#FEF08A"/>
    <circle cx="150" cy="60" r="16" fill="#FDE047"/>
    <circle cx="80" cy="90" r="10" fill="#FFFFFF"/>
    <circle cx="190" cy="80" r="14" fill="#F59E0B"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 15. Cute Teddy Bear
    // =========================================================================
    protected function getTeddyBearSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <radialGradient id="bearFur" cx="40%" cy="35%" r="65%">
      <stop offset="0%" stop-color="#FBBF24"/>
      <stop offset="60%" stop-color="#D97706"/>
      <stop offset="100%" stop-color="#92400E"/>
    </radialGradient>
    <filter id="bearGlow">
      <feGaussianBlur stdDeviation="6" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes bearWiggle {
      0%, 100% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-8px) rotate(2deg); }
    }
    @keyframes heartPulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.15); }
    }
    .bear-anim { transform-origin: 250px 300px; animation: bearWiggle 2.5s infinite ease-in-out; }
    .heart-anim { transform-origin: 250px 290px; animation: heartPulse 1.2s infinite ease-in-out; }
  </style>

  <g class="bear-anim" filter="url(#bearGlow)">
    <!-- Ears -->
    <circle cx="170" cy="130" r="32" fill="url(#bearFur)" stroke="#78350F" stroke-width="2"/>
    <circle cx="170" cy="130" r="16" fill="#FDE68A"/>
    <circle cx="330" cy="130" r="32" fill="url(#bearFur)" stroke="#78350F" stroke-width="2"/>
    <circle cx="330" cy="130" r="16" fill="#FDE68A"/>

    <!-- Head -->
    <ellipse cx="250" cy="190" rx="80" ry="70" fill="url(#bearFur)" stroke="#78350F" stroke-width="3"/>

    <!-- Eyes & Snout -->
    <circle cx="215" cy="180" r="8" fill="#0F172A"/>
    <circle cx="217" cy="178" r="2.5" fill="#FFF"/>
    <circle cx="285" cy="180" r="8" fill="#0F172A"/>
    <circle cx="287" cy="178" r="2.5" fill="#FFF"/>

    <!-- Snout Muzzle -->
    <ellipse cx="250" cy="210" rx="28" ry="20" fill="#FEF3C7"/>
    <polygon points="250,202 240,196 260,196" fill="#78350F"/>
    <path d="M 250,204 L 250,218 M 240,216 Q 250,224 260,216" stroke="#78350F" stroke-width="3" fill="none"/>

    <!-- Body -->
    <ellipse cx="250" cy="340" rx="90" ry="85" fill="url(#bearFur)" stroke="#78350F" stroke-width="3"/>

    <!-- Pulsing Red Love Heart in Paws -->
    <g class="heart-anim">
      <path d="M 250,260 C 250,230 215,210 195,230 C 175,250 175,275 205,305 L 250,345 L 295,305 C 325,275 325,250 305,230 C 285,210 250,230 250,260 Z" fill="#EF4444" stroke="#FFF" stroke-width="3" filter="url(#bearGlow)"/>
    </g>

    <!-- Paws Hugging Heart -->
    <circle cx="185" cy="290" r="24" fill="url(#bearFur)" stroke="#78350F" stroke-width="2"/>
    <circle cx="315" cy="290" r="24" fill="url(#bearFur)" stroke="#78350F" stroke-width="2"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 16. Profile Base: Royal Gold (Pure Avatar Frame with Transparent Hole)
    // =========================================================================
    protected function getProfileBaseRoyalGoldSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <linearGradient id="frameGold" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFFBEB"/>
      <stop offset="35%" stop-color="#FDE047"/>
      <stop offset="70%" stop-color="#F59E0B"/>
      <stop offset="100%" stop-color="#B45309"/>
    </linearGradient>
    <filter id="frameGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes frameRotate { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    @keyframes crownBob { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
    .gem-orbit { transform-origin: 250px 250px; animation: frameRotate 12s infinite linear; }
    .top-crown { transform-origin: 250px 50px; animation: crownBob 2s infinite ease-in-out; }
  </style>

  <!-- Outer Ambient Glow Ring -->
  <circle cx="250" cy="250" r="210" fill="none" stroke="#F59E0B" stroke-width="4" opacity="0.4" filter="url(#frameGlow)"/>

  <!-- Main 24K Gold Filigree Avatar Ring -->
  <circle cx="250" cy="250" r="185" fill="none" stroke="url(#frameGold)" stroke-width="18" filter="url(#frameGlow)"/>
  <circle cx="250" cy="250" r="172" fill="none" stroke="#FFFFFF" stroke-width="3"/>
  <circle cx="250" cy="250" r="198" fill="none" stroke="#D97706" stroke-width="3"/>

  <!-- Rotating Jewels on Ring Orbit -->
  <g class="gem-orbit">
    <circle cx="250" cy="55" r="9" fill="#EF4444" stroke="#FFF" stroke-width="2"/>
    <circle cx="445" cy="250" r="9" fill="#38BDF8" stroke="#FFF" stroke-width="2"/>
    <circle cx="250" cy="445" r="9" fill="#EF4444" stroke="#FFF" stroke-width="2"/>
    <circle cx="55" cy="250" r="9" fill="#38BDF8" stroke="#FFF" stroke-width="2"/>
  </g>

  <!-- Royal Crown Top Crest -->
  <g class="top-crown" transform="translate(250, 45)" filter="url(#frameGlow)">
    <path d="M -40,30 L -50,-5 L -20,12 L 0,-20 L 20,12 L 50,-5 L 40,30 Z" fill="url(#frameGold)" stroke="#FFF" stroke-width="2"/>
    <circle cx="0" cy="-20" r="6" fill="#EF4444"/>
    <circle cx="-50" cy="-5" r="4.5" fill="#38BDF8"/>
    <circle cx="50" cy="-5" r="4.5" fill="#38BDF8"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 17. Profile Base: Diamond Wings
    // =========================================================================
    protected function getProfileBaseWingsSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <linearGradient id="wingGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFFFFF"/>
      <stop offset="40%" stop-color="#E0F2FE"/>
      <stop offset="80%" stop-color="#38BDF8"/>
      <stop offset="100%" stop-color="#0284C7"/>
    </linearGradient>
    <filter id="wingGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes wingFlapL { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(-5deg); } }
    @keyframes wingFlapR { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(5deg); } }
    .wing-l { transform-origin: 150px 250px; animation: wingFlapL 2s infinite ease-in-out; }
    .wing-r { transform-origin: 350px 250px; animation: wingFlapR 2s infinite ease-in-out; }
  </style>

  <!-- Left Diamond Angel Wing -->
  <g class="wing-l" filter="url(#wingGlow)">
    <path d="M 120,220 C 70,160 20,130 0,160 C 20,200 60,230 110,250 C 40,260 20,300 30,330 C 60,320 100,300 120,280 Z" fill="url(#wingGrad)" stroke="#FFFFFF" stroke-width="2"/>
  </g>

  <!-- Right Diamond Angel Wing -->
  <g class="wing-r" filter="url(#wingGlow)">
    <path d="M 380,220 C 430,160 480,130 500,160 C 480,200 440,230 390,250 C 460,260 480,300 470,330 C 440,320 400,300 380,280 Z" fill="url(#wingGrad)" stroke="#FFFFFF" stroke-width="2"/>
  </g>

  <!-- Center Diamond Halo Ring -->
  <circle cx="250" cy="250" r="180" fill="none" stroke="url(#wingGrad)" stroke-width="16" filter="url(#wingGlow)"/>
  <circle cx="250" cy="250" r="168" fill="none" stroke="#FFFFFF" stroke-width="3"/>
  <circle cx="250" cy="65" r="10" fill="#FFFFFF" filter="url(#wingGlow)"/>
</svg>
SVG;
    }

    // =========================================================================
    // 18. Profile Base: Cyber Neon
    // =========================================================================
    protected function getProfileBaseCyberSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <linearGradient id="cyberGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#00F0FF"/>
      <stop offset="50%" stop-color="#7000FF"/>
      <stop offset="100%" stop-color="#FF0077"/>
    </linearGradient>
    <filter id="cyberGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes cyberSpin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    @keyframes cyberSpinRev { 0% { transform: rotate(360deg); } 100% { transform: rotate(0deg); } }
    .c-ring-1 { transform-origin: 250px 250px; animation: cyberSpin 8s infinite linear; }
    .c-ring-2 { transform-origin: 250px 250px; animation: cyberSpinRev 6s infinite linear; }
  </style>

  <!-- Segmented Rotating Outer Cyber Ring -->
  <g class="c-ring-1" filter="url(#cyberGlow)">
    <circle cx="250" cy="250" r="200" fill="none" stroke="#00F0FF" stroke-width="8" stroke-dasharray="80 30 40 30"/>
    <circle cx="250" cy="250" r="220" fill="none" stroke="#FF0077" stroke-width="4" stroke-dasharray="20 40"/>
  </g>

  <!-- Inner Neon Ring -->
  <g class="c-ring-2" filter="url(#cyberGlow)">
    <circle cx="250" cy="250" r="180" fill="none" stroke="url(#cyberGrad)" stroke-width="14"/>
    <polygon points="250,55 260,70 240,70" fill="#00F0FF"/>
    <polygon points="250,445 260,430 240,430" fill="#FF0077"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 19. Profile Base: Fire Dragon
    // =========================================================================
    protected function getProfileBaseDragonSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <linearGradient id="dragonFlame" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFD700"/>
      <stop offset="50%" stop-color="#FF4500"/>
      <stop offset="100%" stop-color="#990000"/>
    </linearGradient>
    <filter id="dragonRingGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes flameFlicker {
      0%, 100% { transform: scale(1); filter: drop-shadow(0 0 10px #FF4500); }
      50% { transform: scale(1.03); filter: drop-shadow(0 0 22px #FFD700); }
    }
    .flame-frame { transform-origin: 250px 250px; animation: flameFlicker 2s infinite ease-in-out; }
  </style>

  <g class="flame-frame">
    <!-- Outer Flaming Ring -->
    <circle cx="250" cy="250" r="185" fill="none" stroke="url(#dragonFlame)" stroke-width="16" filter="url(#dragonRingGlow)"/>
    <circle cx="250" cy="250" r="172" fill="none" stroke="#FFD700" stroke-width="3"/>

    <!-- Dragon Head at Top Right -->
    <g transform="translate(360, 110) rotate(45)" filter="url(#dragonRingGlow)">
      <path d="M 0,0 C 20,-20 50,-10 70,10 C 50,30 20,20 0,0 Z" fill="#FFD700"/>
      <circle cx="35" cy="0" r="4" fill="#FF0000"/>
    </g>

    <!-- Flame Claws around border -->
    <path d="M 120,340 C 90,360 80,400 110,410 C 130,390 140,360 120,340 Z" fill="#FF4500" filter="url(#dragonRingGlow)"/>
    <path d="M 380,340 C 410,360 420,400 390,410 C 370,390 360,360 380,340 Z" fill="#FF4500" filter="url(#dragonRingGlow)"/>
  </g>
</svg>
SVG;
    }

    // =========================================================================
    // 20. Profile Base: SVIP Emperor Tiara
    // =========================================================================
    protected function getProfileBaseSvipSvg(): string
    {
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
  <defs>
    <linearGradient id="svipGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FDE047"/>
      <stop offset="50%" stop-color="#EC4899"/>
      <stop offset="100%" stop-color="#8B5CF6"/>
    </linearGradient>
    <filter id="svipGlow">
      <feGaussianBlur stdDeviation="8" result="blur"/>
      <feMerge>
        <feMergeNode in="blur"/>
        <feMergeNode in="SourceGraphic"/>
      </feMerge>
    </filter>
  </defs>

  <style>
    @keyframes svipPulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.03); }
    }
    .svip-anim { transform-origin: 250px 250px; animation: svipPulse 2.2s infinite ease-in-out; }
  </style>

  <g class="svip-anim">
    <!-- Multi-Color SVIP Ring -->
    <circle cx="250" cy="250" r="185" fill="none" stroke="url(#svipGrad)" stroke-width="16" filter="url(#svipGlow)"/>
    <circle cx="250" cy="250" r="172" fill="none" stroke="#FFFFFF" stroke-width="3"/>

    <!-- Imperial Tiara Top -->
    <g transform="translate(250, 45)" filter="url(#svipGlow)">
      <path d="M -50,30 L -30,-15 L 0,10 L 30,-15 L 50,30 Z" fill="url(#svipGrad)" stroke="#FFFFFF" stroke-width="2"/>
      <circle cx="0" cy="10" r="8" fill="#FFFFFF"/>
      <circle cx="-30" cy="-15" r="6" fill="#FDE047"/>
      <circle cx="30" cy="-15" r="6" fill="#FDE047"/>
    </g>
  </g>
</svg>
SVG;
    }
}
