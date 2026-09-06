<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Gift;
use Illuminate\Support\Facades\File;

$giftsDir = public_path('uploads/gifts');
if (!File::exists($giftsDir)) {
    File::makeDirectory($giftsDir, 0777, true, true);
}

// Helper to create clean vector SVG if not exists
function makeSvgIfMissing($path, $svgContent) {
    if (!File::exists($path)) {
        File::put($path, trim($svgContent));
    }
}

// 1. Trophy Cup
makeSvgIfMissing($giftsDir . '/trophy_cup.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <radialGradient id="goldGlow" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#FFF4B8" stop-opacity="0.8"/>
      <stop offset="60%" stop-color="#F59E0B" stop-opacity="0.2"/>
      <stop offset="100%" stop-color="#B45309" stop-opacity="0"/>
    </radialGradient>
    <linearGradient id="goldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFFBEB"/>
      <stop offset="30%" stop-color="#FBBF24"/>
      <stop offset="70%" stop-color="#D97706"/>
      <stop offset="100%" stop-color="#78350F"/>
    </linearGradient>
    <linearGradient id="gemGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#67E8F9"/>
      <stop offset="100%" stop-color="#0284C7"/>
    </linearGradient>
  </defs>
  <circle cx="100" cy="100" r="90" fill="url(#goldGlow)"/>
  <!-- Trophy Cup Body -->
  <path d="M60 40H140V85C140 110 122 128 100 128C78 128 60 110 60 85V40Z" fill="url(#goldGrad)" stroke="#FFEAA7" stroke-width="3"/>
  <!-- Handles -->
  <path d="M60 50C35 50 35 90 60 90" stroke="url(#goldGrad)" stroke-width="8" stroke-linecap="round" fill="none"/>
  <path d="M140 50C165 50 165 90 140 90" stroke="url(#goldGrad)" stroke-width="8" stroke-linecap="round" fill="none"/>
  <!-- Stem & Base -->
  <path d="M92 128H108V155H92V128Z" fill="url(#goldGrad)"/>
  <path d="M65 155H135L145 175H55L65 155Z" fill="url(#goldGrad)" stroke="#FDE68A" stroke-width="2"/>
  <!-- Star on Cup -->
  <polygon points="100,60 104,72 116,72 106,80 110,92 100,84 90,92 94,80 84,72 96,72" fill="#FFFFFF"/>
  <circle cx="100" cy="165" r="5" fill="url(#gemGrad)"/>
</svg>');

// 2. Mystery Box
makeSvgIfMissing($giftsDir . '/mystery_box.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <radialGradient id="boxGlow" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#C084FC" stop-opacity="0.8"/>
      <stop offset="100%" stop-color="#6B21A8" stop-opacity="0"/>
    </radialGradient>
    <linearGradient id="boxGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#9333EA"/>
      <stop offset="100%" stop-color="#4C1D95"/>
    </linearGradient>
    <linearGradient id="goldRibbon" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FDE047"/>
      <stop offset="100%" stop-color="#CA8A04"/>
    </linearGradient>
  </defs>
  <circle cx="100" cy="100" r="90" fill="url(#boxGlow)"/>
  <!-- Box Base -->
  <rect x="45" y="80" width="110" height="85" rx="10" fill="url(#boxGrad)" stroke="#E9D5FF" stroke-width="3"/>
  <!-- Box Lid -->
  <rect x="38" y="65" width="124" height="25" rx="6" fill="#A855F7" stroke="#F3E8FF" stroke-width="2"/>
  <!-- Gold Ribbons -->
  <rect x="90" y="65" width="20" height="100" fill="url(#goldRibbon)"/>
  <!-- Question Mark -->
  <text x="100" y="132" font-family="Arial, sans-serif" font-size="42" font-weight="900" fill="#FEF08A" text-anchor="middle" filter="drop-shadow(0 2px 4px rgba(0,0,0,0.5))">?</text>
  <!-- Ribbon Bow -->
  <circle cx="85" cy="55" r="14" fill="url(#goldRibbon)"/>
  <circle cx="115" cy="55" r="14" fill="url(#goldRibbon)"/>
  <circle cx="100" cy="58" r="8" fill="#FEF08A"/>
</svg>');

// 3. CP Love Letter
makeSvgIfMissing($giftsDir . '/cp_love_letter.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="envGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FFE4E6"/>
      <stop offset="100%" stop-color="#FDA4AF"/>
    </linearGradient>
  </defs>
  <rect x="35" y="60" width="130" height="95" rx="8" fill="url(#envGrad)" stroke="#F43F5E" stroke-width="3"/>
  <path d="M35 62L100 115L165 62" stroke="#E11D48" stroke-width="3" fill="none"/>
  <!-- Wax Seal Heart -->
  <circle cx="100" cy="115" r="18" fill="#BE123C"/>
  <path d="M100 123L93 115C90 112 90 107 94 105C97 103 100 106 100 106C100 106 103 103 106 105C110 107 110 112 107 115L100 123Z" fill="#FFF1F2"/>
  <!-- Floating Hearts -->
  <path d="M60 45L56 40C54 38 54 35 56 34C58 33 60 35 60 35C60 35 62 33 64 34C66 35 66 38 64 40L60 45Z" fill="#FB7185"/>
  <path d="M140 40L135 34C133 32 133 29 135 28C137 27 140 29 140 29C140 29 143 27 145 28C147 29 147 32 145 34L140 40Z" fill="#F43F5E"/>
</svg>');

// 4. In My Hands (Holding Hands)
makeSvgIfMissing($giftsDir . '/in_my_hands.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="100" cy="100" r="85" fill="#FFE4E6" fill-opacity="0.3"/>
  <path d="M40 130C60 110 80 110 100 120C120 110 140 110 160 130" stroke="#FB7185" stroke-width="8" stroke-linecap="round"/>
  <!-- Glowing Heart Between Hands -->
  <path d="M100 95L80 70C72 60 72 45 84 40C94 35 100 45 100 45C100 45 106 35 116 40C128 45 128 60 120 70L100 95Z" fill="#E11D48" filter="drop-shadow(0 0 10px rgba(225,29,72,0.6))"/>
  <circle cx="85" cy="55" r="3" fill="#FFF"/>
  <circle cx="115" cy="55" r="3" fill="#FFF"/>
</svg>');

// 5. CP Kiss
makeSvgIfMissing($giftsDir . '/cp_romantic_kiss.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <radialGradient id="kissGlow" cx="50%" cy="50%" r="50%">
      <stop offset="0%" stop-color="#FDA4AF" stop-opacity="0.8"/>
      <stop offset="100%" stop-color="#E11D48" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <circle cx="100" cy="100" r="85" fill="url(#kissGlow)"/>
  <!-- Boy Profile Silhouette -->
  <circle cx="70" cy="85" r="22" fill="#38BDF8"/>
  <path d="M50 145C50 115 65 115 70 115H80C85 115 100 115 100 145" fill="#0284C7"/>
  <!-- Girl Profile Silhouette -->
  <circle cx="130" cy="85" r="22" fill="#F472B6"/>
  <path d="M100 145C100 115 115 115 120 115H130C135 115 150 115 150 145" fill="#DB2777"/>
  <!-- Sweet Kiss Heart -->
  <path d="M100 75L92 65C87 60 87 50 95 47C100 45 100 50 100 50C100 50 100 45 105 47C113 50 113 60 108 65L100 75Z" fill="#E11D48"/>
</svg>');

// 6. Slot Machine
makeSvgIfMissing($giftsDir . '/slot_machine.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <!-- Slot Body -->
  <rect x="45" y="40" width="110" height="125" rx="12" fill="#EF4444" stroke="#B91C1C" stroke-width="4"/>
  <rect x="55" y="70" width="90" height="50" rx="6" fill="#1E293B" stroke="#FDE047" stroke-width="3"/>
  <!-- 7 7 7 -->
  <text x="70" y="108" font-family="Arial, sans-serif" font-size="28" font-weight="900" fill="#FDE047" text-anchor="middle">7</text>
  <text x="100" y="108" font-family="Arial, sans-serif" font-size="28" font-weight="900" fill="#FDE047" text-anchor="middle">7</text>
  <text x="130" y="108" font-family="Arial, sans-serif" font-size="28" font-weight="900" fill="#FDE047" text-anchor="middle">7</text>
  <!-- Handle -->
  <line x1="155" y1="90" x2="175" y2="70" stroke="#CBD5E1" stroke-width="6" stroke-linecap="round"/>
  <circle cx="178" cy="65" r="10" fill="#F59E0B"/>
</svg>');

// 7. World Cup Trophy
makeSvgIfMissing($giftsDir . '/world_cup_trophy.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="wcGold" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FEF08A"/>
      <stop offset="50%" stop-color="#EAB308"/>
      <stop offset="100%" stop-color="#854D0E"/>
    </linearGradient>
  </defs>
  <!-- Globe on top -->
  <circle cx="100" cy="65" r="32" fill="url(#wcGold)" stroke="#FEF9C3" stroke-width="2"/>
  <path d="M85 55C95 65 105 65 115 55" stroke="#713F12" stroke-width="2" fill="none"/>
  <!-- Two figures holding globe -->
  <path d="M75 90C80 80 88 75 95 95V140H105V95C112 75 120 80 125 90L130 145H70L75 90Z" fill="url(#wcGold)"/>
  <!-- Base with Green Malachite bands -->
  <rect x="65" y="145" width="70" height="25" rx="4" fill="#713F12"/>
  <rect x="65" y="152" width="70" height="4" fill="#10B981"/>
  <rect x="65" y="160" width="70" height="4" fill="#10B981"/>
  <text x="100" y="140" font-family="Arial, sans-serif" font-size="10" font-weight="bold" fill="#FEF08A" text-anchor="middle">2026</text>
</svg>');

// 8. Taj Mahal Palace
makeSvgIfMissing($giftsDir . '/taj_mahal_palace.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <!-- Sky / Night Background -->
  <circle cx="100" cy="100" r="85" fill="#0F172A"/>
  <circle cx="150" cy="50" r="12" fill="#FEF08A"/>
  <!-- Main Taj Mahal Dome -->
  <path d="M85 85C85 65 100 50 100 50C100 50 115 65 115 85H85Z" fill="#F8FAFC"/>
  <!-- Main Building Block -->
  <rect x="70" y="85" width="60" height="55" fill="#E2E8F0"/>
  <path d="M85 140V105C85 100 100 95 100 95C100 95 115 100 115 105V140H85Z" fill="#334155"/>
  <!-- Side Minarets -->
  <rect x="42" y="70" width="8" height="70" fill="#F1F5F9"/>
  <path d="M40 70L46 60L52 70Z" fill="#CBD5E1"/>
  <rect x="150" y="70" width="8" height="70" fill="#F1F5F9"/>
  <path d="M148 70L154 60L160 70Z" fill="#CBD5E1"/>
  <!-- Water Reflection Base -->
  <rect x="30" y="140" width="140" height="25" fill="#1E293B"/>
  <line x1="50" y1="150" x2="150" y2="150" stroke="#38BDF8" stroke-width="2" stroke-dasharray="4 4"/>
</svg>');

// 9. Hot Chili Pepper
makeSvgIfMissing($giftsDir . '/hot_chili_pepper.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="chiliGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#FF4D4D"/>
      <stop offset="70%" stop-color="#DC2626"/>
      <stop offset="100%" stop-color="#7F1D1D"/>
    </linearGradient>
  </defs>
  <!-- Flame background -->
  <path d="M100 30C120 60 145 75 135 110C125 145 95 160 100 160C85 160 65 140 70 100C75 70 90 50 100 30Z" fill="#F59E0B" fill-opacity="0.3"/>
  <!-- Chili Curve -->
  <path d="M110 60C125 80 135 110 115 145C100 170 80 170 70 175C75 165 85 145 88 120C92 95 98 75 110 60Z" fill="url(#chiliGrad)"/>
  <!-- Green Stem -->
  <path d="M110 60C112 45 125 35 135 38" stroke="#16A34A" stroke-width="6" stroke-linecap="round" fill="none"/>
  <path d="M102 62C108 58 116 58 120 64" fill="#22C55E"/>
</svg>');

// 10. Golden Lucky Egg
makeSvgIfMissing($giftsDir . '/golden_egg_crack.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <radialGradient id="eggGold" cx="40%" cy="40%" r="60%">
      <stop offset="0%" stop-color="#FEF08A"/>
      <stop offset="60%" stop-color="#EAB308"/>
      <stop offset="100%" stop-color="#A16207"/>
    </radialGradient>
  </defs>
  <!-- Egg Body -->
  <path d="M100 35C65 35 45 80 45 120C45 155 70 175 100 175C130 175 155 155 155 120C155 80 135 35 100 35Z" fill="url(#eggGold)" stroke="#FEF9C3" stroke-width="3"/>
  <!-- Crack with Starlight -->
  <path d="M100 80L90 95L110 110L95 125L105 140" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" fill="none"/>
  <polygon points="100,105 103,115 113,115 105,121 108,131 100,125 92,131 95,121 87,115 97,115" fill="#FFFFFF"/>
</svg>');

// 11. I Am Rich Cash Shower
makeSvgIfMissing($giftsDir . '/i_am_rich_cash.svg', '<?xml version="1.0" encoding="UTF-8"?>
<svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
  <!-- Gold Bullion & Dollar Stacks -->
  <rect x="40" y="70" width="120" height="70" rx="8" fill="#16A34A" stroke="#22C55E" stroke-width="3"/>
  <circle cx="100" cy="105" r="22" fill="#15803D" stroke="#86EFAC" stroke-width="2"/>
  <text x="100" y="115" font-family="Arial, sans-serif" font-size="28" font-weight="bold" fill="#FEF08A" text-anchor="middle">$</text>
  <text x="100" y="55" font-family="Arial, sans-serif" font-size="14" font-weight="900" fill="#FDE047" text-anchor="middle">I AM RICH</text>
</svg>');

echo "SVGs prepared successfully!\n";
