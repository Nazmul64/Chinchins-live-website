<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProfileBase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProfileBaseAdminController extends Controller
{
    /**
     * Target directory for all uploaded base frame images.
     */
    protected string $uploadFolder = 'uploads/bases';

    /**
     * Display a listing of all Profile Bases / Level Badges and frame configurations.
     */
    public function index()
    {
        // Seed default bases if not present
        ProfileBase::seedDefaultBases();

        $bases = ProfileBase::orderBy('level', 'asc')->get();
        $totalBases = $bases->count();
        $activeBases = $bases->where('is_active', true)->count();
        $maxLevel = $bases->max('level') ?? 0;
        $maxCoins = $bases->max('required_coins') ?? 0;

        // Sample avatars for live preview
        $sampleAvatars = [
            asset('assets/images/users/avatar-1.jpg'),
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
        ];

        // Available pre-made SVGs and PNGs in uploads/bases
        $availablePresetFrames = [
            'uploads/bases/profile_base_novice_cadet.png'      => 'Lv.0 Novice Cadet Frame',
            'uploads/bases/profile_base_bronze_star.png'       => 'Lv.1 Bronze Star Starter Frame',
            'uploads/bases/profile_base_dollar_ring.png'       => 'Lv.2 Fatima Dollar Ring Rich Gold',
            'uploads/bases/profile_base_circus_gentleman.png'  => 'Lv.3 Maryam Circus Gentleman Hat',
            'uploads/bases/profile_base_top3_spotlight.png'    => 'Lv.4 Abdul TOP 3 Stage Spotlight',
            'uploads/bases/profile_base_blue_captain.png'      => 'Lv.5 Momo Blue Captain Sailor Wheel',
            'uploads/bases/profile_base_devil_horns.png'       => 'Lv.6 Omar Devil Horns Flame Crest',
            'uploads/bases/profile_base_cricket_superstar.png' => 'Lv.7 Amir Cricket Superstar (3D PNG)',
            'uploads/bases/profile_base_cyber_speedometer.png' => 'Lv.8 Zainab Cyber Speedometer Shield',
            'uploads/bases/profile_base_rose_garden.png'       => 'Lv.9 Priya Luxury Rose Garden Queen',
            'uploads/bases/profile_base_queen_imperial.png'    => 'Lv.10 QUEEN Imperial Diamond Wings (3D PNG)',
            'uploads/bases/profile_base_king_royal.png'        => 'Lv.11 KING 24K Gold Winged Crown (3D PNG)',
            'uploads/bases/profile_base_superbike_rider.png'   => 'Lv.12 Superbike Nitro Racer Helmet',
            'uploads/bases/profile_base_phoenix_immortal.png'  => 'Lv.13 Fire Phoenix Immortal Wings',
            'uploads/bases/profile_base_ocean_poseidon.png'    => 'Lv.14 Ocean Leviathan Poseidon Trident',
            'uploads/bases/profile_base_supreme_sovereign.png' => 'Lv.15 Supreme Sovereign God-Tier Aura',
            'uploads/bases/profile_base_top1_emperor_wings.png'=> 'SVIP 9 TOP 1 Imperial Gold Wings Arch',
            'uploads/bases/profile_base_top1_family_crest.png' => 'Warm Family Golden Wings Trophy Crest',
            'uploads/bases/profile_base_spartan_gold_warrior.png'=> 'Spartan Golden Warrior & Rubies',
            'uploads/bases/profile_base_golden_lion_family.png'=> 'TOP 1 Family Golden Lion Wreath',
            'uploads/bases/profile_base_crystal_bubble_globe.png'=> 'VIP Crystal Bubble Globe Sphere',
        ];

        return view('admin.profile-bases.index', compact(
            'bases',
            'totalBases',
            'activeBases',
            'maxLevel',
            'maxCoins',
            'sampleAvatars',
            'availablePresetFrames'
        ));
    }

    /**
     * Bulk update all 10+ levels: coin requirements, titles, badge icons, colors, active statuses,
     * AND handle direct image file uploads per level row simultaneously.
     */
    public function batchUpdate(Request $request)
    {
        $levelsData = $request->input('levels', []);

        if (empty($levelsData) || !is_array($levelsData)) {
            return redirect()->back()->with('error', 'No level data received.');
        }

        $destinationPath = public_path($this->uploadFolder);
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        $updatedCount = 0;
        foreach ($levelsData as $id => $data) {
            $base = ProfileBase::find($id);
            if ($base) {
                $base->name = $data['name'] ?? $base->name;
                $base->required_coins = isset($data['required_coins']) ? max(0, (int) $data['required_coins']) : $base->required_coins;
                $base->badge_icon = $data['badge_icon'] ?? $base->badge_icon;
                $base->badge_color = $data['badge_color'] ?? $base->badge_color;
                $base->glow_color = $data['glow_color'] ?? $base->glow_color;
                $base->privilege_text = $data['privilege_text'] ?? $base->privilege_text;
                $base->is_active = !empty($data['is_active']);

                // If a preset frame was selected in the row
                if (!empty($data['preset_frame'])) {
                    $base->base_frame_image = $data['preset_frame'];
                }

                // Check for individual file upload in batch row
                // 1. levels[id][frame_image]
                if ($request->hasFile("levels.{$id}.frame_image")) {
                    $file = $request->file("levels.{$id}.frame_image");
                    $filename = 'base_level_' . $base->level . '_' . time() . '_' . Str::random(4) . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $filename);
                    $base->base_frame_image = $this->uploadFolder . '/' . $filename;
                }
                // 2. frame_files[id]
                elseif ($request->hasFile("frame_files.{$id}")) {
                    $file = $request->file("frame_files.{$id}");
                    $filename = 'base_level_' . $base->level . '_' . time() . '_' . Str::random(4) . '.' . $file->getClientOriginalExtension();
                    $file->move($destinationPath, $filename);
                    $base->base_frame_image = $this->uploadFolder . '/' . $filename;
                }

                $base->save();
                $updatedCount++;
            }
        }

        return redirect()->route('admin.profile-bases.index')
            ->with('success', "Successfully updated {$updatedCount} level badge & frame settings!");
    }

    /**
     * Store a newly created Level Base with frame image upload to uploads/bases.
     */
    public function store(Request $request)
    {
        $request->validate([
            'level'            => 'required|integer|min:0|unique:profile_bases,level',
            'name'             => 'required|string|max:190',
            'required_coins'   => 'required|integer|min:0',
            'frame_image'      => 'nullable|file|mimes:svg,png,webp,jpg,jpeg,gif|max:5120',
            'preset_frame'     => 'nullable|string',
            'badge_icon'       => 'nullable|string|max:50',
            'badge_color'      => 'nullable|string|max:50',
            'glow_color'       => 'nullable|string|max:50',
            'privilege_text'   => 'nullable|string|max:255',
            'is_active'        => 'nullable|boolean',
        ]);

        $frameImagePath = $request->input('preset_frame') ?: ($this->uploadFolder . '/profile_base_royal_gold.svg');

        // Handle custom image/SVG upload to uploads/bases
        if ($request->hasFile('frame_image')) {
            $file = $request->file('frame_image');
            $filename = 'base_level_' . $request->level . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path($this->uploadFolder);
            
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            $file->move($destinationPath, $filename);
            $frameImagePath = $this->uploadFolder . '/' . $filename;
        }

        ProfileBase::create([
            'level'            => (int) $request->level,
            'name'             => $request->name,
            'required_coins'   => (int) $request->required_coins,
            'base_frame_image' => $frameImagePath,
            'badge_icon'       => $request->badge_icon ?: 'star',
            'badge_color'      => $request->badge_color ?: '#f59e0b',
            'glow_color'       => $request->glow_color ?: 'rgba(245, 158, 11, 0.45)',
            'privilege_text'   => $request->privilege_text,
            'is_active'        => $request->has('is_active') ? (bool) $request->is_active : true,
            'sort_order'       => (int) $request->level,
        ]);

        return redirect()->route('admin.profile-bases.index')
            ->with('success', "Level {$request->level} ({$request->name}) badge and base frame created successfully!");
    }

    /**
     * Update an existing Level Base and optionally replace its frame image in uploads/bases.
     */
    public function update(Request $request, $id)
    {
        $base = ProfileBase::findOrFail($id);

        $request->validate([
            'name'             => 'required|string|max:190',
            'required_coins'   => 'required|integer|min:0',
            'frame_image'      => 'nullable|file|mimes:svg,png,webp,jpg,jpeg,gif|max:5120',
            'preset_frame'     => 'nullable|string',
            'badge_icon'       => 'nullable|string|max:50',
            'badge_color'      => 'nullable|string|max:50',
            'glow_color'       => 'nullable|string|max:50',
            'privilege_text'   => 'nullable|string|max:255',
            'is_active'        => 'nullable',
        ]);

        $base->name = $request->name;
        $base->required_coins = (int) $request->required_coins;
        $base->badge_icon = $request->badge_icon ?: $base->badge_icon;
        $base->badge_color = $request->badge_color ?: $base->badge_color;
        $base->glow_color = $request->glow_color ?: $base->glow_color;
        $base->privilege_text = $request->privilege_text;
        $base->is_active = $request->has('is_active') ? (bool) $request->is_active : false;

        // Update preset frame if chosen
        if (!empty($request->preset_frame)) {
            $base->base_frame_image = $request->preset_frame;
        }

        // Handle uploaded file to uploads/bases
        if ($request->hasFile('frame_image')) {
            $file = $request->file('frame_image');
            $filename = 'base_level_' . $base->level . '_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path($this->uploadFolder);
            
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            $file->move($destinationPath, $filename);
            $base->base_frame_image = $this->uploadFolder . '/' . $filename;
        }

        $base->save();

        return redirect()->route('admin.profile-bases.index')
            ->with('success', "Level {$base->level} ({$base->name}) updated successfully!");
    }

    /**
     * Remove a Level Base.
     */
    public function destroy($id)
    {
        $base = ProfileBase::findOrFail($id);

        if ($base->level === 0) {
            return redirect()->back()->with('error', 'Level 0 (Novice base) cannot be deleted.');
        }

        $levelNum = $base->level;
        $base->delete();

        return redirect()->route('admin.profile-bases.index')
            ->with('success', "Level {$levelNum} base frame has been deleted.");
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus($id)
    {
        $base = ProfileBase::findOrFail($id);
        $base->is_active = !$base->is_active;
        $base->save();

        $statusStr = $base->is_active ? 'Activated' : 'Deactivated';
        return redirect()->route('admin.profile-bases.index')
            ->with('success', "Level {$base->level} has been {$statusStr}.");
    }
}
