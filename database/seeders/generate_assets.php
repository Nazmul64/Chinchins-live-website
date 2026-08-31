<?php

$dir = __DIR__ . '/../../public/uploads/gifts';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$gifts = [
    'romantic_couple.png' => ['#ec4899', '#f43f5e', '👩‍❤️‍👨', 'Romantic Couple'],
    'sunset_couple.png' => ['#f59e0b', '#d97706', '🌅', 'Sunset Romance'],
    'vintage_romance.png' => ['#8b5cf6', '#6d28d9', '🕯️', 'Vintage Romance'],
    'candlelight_dinner.png' => ['#ef4444', '#b91c1c', '🍷', 'Dinner'],
    'crystal_castle.png' => ['#06b6d4', '#3b82f6', '🏰', 'Crystal Castle'],
    'supercar_luxury.png' => ['#eab308', '#ca8a04', '🏎️', 'Supercar'],
    'fairy_crown.png' => ['#ec4899', '#a855f7', '👑', 'Fairy Crown'],
    'space_battleship.png' => ['#3b82f6', '#1d4ed8', '🚀', 'Battleship'],
    'fire_dragon.png' => ['#f97316', '#dc2626', '🐉', 'Fire Dragon'],
    'treasure_chest.png' => ['#f59e0b', '#b45309', '💎', 'Treasure'],
    'love_mailbox.png' => ['#f43f5e', '#be123c', '💌', 'Love Mailbox'],
    'genie_lamp.png' => ['#eab308', '#854d0e', '🪔', 'Genie Lamp'],
    'birthday_cake.png' => ['#ec4899', '#db2777', '🎂', 'Birthday Cake'],
    'midnight_lovers.png' => ['#6366f1', '#4338ca', '🌙', 'Midnight Lovers'],
    'galaxy_portal.png' => ['#a855f7', '#7c3aed', '🌌', 'Galaxy Portal'],
    'rose_bouquet.png' => ['#f43f5e', '#e11d48', '🌹', 'Rose Bouquet']
];

foreach ($gifts as $file => $data) {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
      <defs>
        <radialGradient id="grad_' . md5($file) . '" cx="50%" cy="50%" r="50%" fx="30%" fy="30%">
          <stop offset="0%" style="stop-color:' . $data[0] . ';stop-opacity:1" />
          <stop offset="100%" style="stop-color:' . $data[1] . ';stop-opacity:1" />
        </radialGradient>
        <filter id="glow_' . md5($file) . '" x="-20%" y="-20%" width="140%" height="140%">
          <feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="' . $data[0] . '" flood-opacity="0.6"/>
        </filter>
      </defs>
      <circle cx="100" cy="100" r="86" fill="url(#grad_' . md5($file) . ')" filter="url(#glow_' . md5($file) . ')" stroke="#ffffff" stroke-width="4"/>
      <circle cx="100" cy="100" r="76" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="2" stroke-dasharray="6 4"/>
      <text x="100" y="116" font-size="64" text-anchor="middle" dominant-baseline="central">' . $data[2] . '</text>
    </svg>';
    
    file_put_contents($dir . '/' . $file, $svg);
    file_put_contents($dir . '/' . str_replace('.png', '.svg', $file), $svg);
}

echo "Gift icons generated.\n";
