@extends('layouts.admin')

@section('title', 'Chinchins Live - User Profile & API Integration')

@section('content')
<div class="profile-management-container" style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Top Banner / Header -->
    <div style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); border-radius: 16px; padding: 24px; color: #fff; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);">
        <div>
            <h2 style="margin: 0 0 6px 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-mobile-screen-button" style="color: #ec4899;"></i>
                Chinchins Live - Customer Profile & RESTful API
            </h2>
            <p style="margin: 0; color: #cbd5e1; font-size: 14px;">
                Auto-generates 10-12 digit Account IDs, handles dynamic multi-image gallery & cover photo uploads, and connects seamlessly to database.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <select id="userSelector" class="form-control" style="background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; padding: 8px 12px;" onchange="window.location.href='?user_id='+this.value">
                @foreach($allUsers as $u)
                    <option value="{{ $u->id }}" {{ $user && $user->id == $u->id ? 'selected' : '' }} style="color: #000;">
                        {{ $u->display_name }} (ID: {{ $u->account_id ?? $u->id }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Main Grid: Live Mobile Mockup Preview vs Admin Live Editor -->
    <div style="display: grid; grid-template-columns: 420px 1fr; gap: 24px; align-items: start;">

        <!-- LEFT: Mobile Mockup Preview (Matching User Screenshot 100%) -->
        <div style="display: flex; flex-direction: column; align-items: center;">
            <div style="font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fa-solid fa-eye"></i> Live Mobile Screen Preview
            </div>

            <!-- Mobile Phone Frame -->
            <div class="chinchins-phone" style="width: 380px; min-height: 760px; background: #0f0f18; border-radius: 36px; border: 8px solid #1e293b; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); overflow: hidden; position: relative; color: #fff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
                
                <!-- Background Cover Photo with Gradient Overlay -->
                <div id="previewCoverBackground" style="position: absolute; top: 0; left: 0; right: 0; height: 380px; background-image: url('{{ $user->cover_photo_url ?: ($user->gallery_image_urls[0] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600&auto=format&fit=crop&q=80') }}'); background-size: cover; background-position: center; z-index: 1;">
                    <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(15,15,24,0.2) 0%, rgba(15,15,24,0.85) 75%, #0f0f18 100%);"></div>
                </div>

                <!-- Top Status Bar Mock -->
                <div style="position: relative; z-index: 10; padding: 12px 18px 6px 18px; display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: #fff; font-weight: 600;">
                    <span><i class="fa-solid fa-signal"></i> 4G 5:41</span>
                    <span style="background: #ef4444; color: #fff; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold;">Dev mode</span>
                </div>

                <!-- App Top Bar (Back button & More options) -->
                <div style="position: relative; z-index: 10; padding: 10px 18px; display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" style="width: 38px; height: 38px; border-radius: 50%; background: rgba(30, 41, 59, 0.7); border: none; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(8px);">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" style="width: 38px; height: 38px; border-radius: 50%; background: rgba(30, 41, 59, 0.7); border: none; color: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; backdrop-filter: blur(8px);">
                        <i class="fa-solid fa-ellipsis"></i>
                    </button>
                </div>

                <!-- Main Scrollable Content Area -->
                <div style="position: relative; z-index: 5; padding: 110px 18px 90px 18px; display: flex; flex-direction: column; gap: 14px;">

                    <!-- Gallery Thumbnails Strip (Up to 5 images) -->
                    <div style="display: flex; gap: 8px; align-items: center; overflow-x: auto; padding-bottom: 4px; scrollbar-width: none;">
                        <div id="previewGalleryStrip" style="display: flex; gap: 8px; align-items: center;">
                            @php
                                $gallery = $user->gallery_image_urls;
                                if (empty($gallery)) {
                                    $gallery = [
                                        'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80',
                                        'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&auto=format&fit=crop&q=80',
                                        'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=200&auto=format&fit=crop&q=80',
                                        'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=200&auto=format&fit=crop&q=80',
                                        'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=200&auto=format&fit=crop&q=80'
                                    ];
                                }
                            @endphp

                            @foreach($gallery as $idx => $img)
                                <div style="width: 54px; height: 68px; border-radius: 12px; overflow: hidden; border: {{ $idx === 0 ? '2px solid #a855f7' : '1px solid rgba(255,255,255,0.2)' }}; flex-shrink: 0; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
                                    <img src="{{ $img }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Gallery">
                                </div>
                            @endforeach
                        </div>
                        <div style="margin-left: auto;">
                            <i class="fa-solid fa-heart" style="color: #ec4899; font-size: 16px;"></i>
                        </div>
                    </div>

                    <!-- Profile Avatar, Name, Verified Badge, User ID & Like button -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <!-- Avatar with Gradient Border -->
                            <div style="width: 60px; height: 60px; border-radius: 50%; padding: 2px; background: linear-gradient(135deg, #3b82f6, #ec4899); position: relative;">
                                <img id="previewAvatarImg" src="{{ $user->avatar_url ?: ($gallery[0] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200') }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;" alt="Avatar">
                            </div>
                            <!-- Name & ID -->
                            <div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span id="previewDisplayName" style="font-size: 18px; font-weight: 700; color: #fff;">{{ $user->display_name }}</span>
                                    <span style="background: #00bcd4; color: #fff; width: 16px; height: 16px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold;">✓</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 4px; color: #94a3b8; font-size: 12px; margin-top: 2px;">
                                    <span>ID <span id="previewAccountId">{{ $user->account_id ?? '602281635' }}</span></span>
                                    <i class="fa-regular fa-copy" style="cursor: pointer; font-size: 11px;" title="Copy ID" onclick="navigator.clipboard.writeText('{{ $user->account_id }}'); alert('Copied ID');"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Big Heart Action Button -->
                        <button type="button" style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #f97316, #ef4444); border: none; color: #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 15px rgba(239, 68, 68, 0.4); cursor: pointer;">
                            <i class="fa-solid fa-heart" style="font-size: 20px;"></i>
                        </button>
                    </div>

                    <!-- Badges Row: Status, Level, Location, Gender+Age -->
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-top: 2px;">
                        <!-- Active Status Badge -->
                        <span id="previewActiveBadge" style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #10b981; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                            <span id="previewStatusText">{{ $user->is_active ? 'Active' : 'Offline' }}</span>
                        </span>

                        <!-- Level Badge -->
                        <span id="previewLevelBadge" style="background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                            {{ $user->level ?: 'Lv4' }}
                        </span>

                        <!-- Location Badge -->
                        <span id="previewLocationBadge" style="background: #0284c7; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            📍 <span id="previewCountryText">{{ $user->country ?: 'Pakistan' }}</span>
                        </span>

                        <!-- Gender & Age Badge -->
                        <span id="previewGenderAgeBadge" style="background: #ec4899; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                            ♀ <span id="previewAgeText">{{ $user->age ?: 27 }}</span>
                        </span>
                    </div>

                    <!-- Close Friends Card (0/3) -->
                    <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 14px; margin-top: 4px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 13px; font-weight: 600; color: #e2e8f0;">Close Friends ({{ $user->close_friends_count ?: '0' }}/3)</span>
                            <i class="fa-regular fa-circle-question" style="color: #64748b; font-size: 12px;"></i>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(51, 65, 85, 0.8); display: flex; align-items: center; justify-content: center; color: #64748b;">
                                <i class="fa-solid fa-couch"></i>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(51, 65, 85, 0.8); display: flex; align-items: center; justify-content: center; color: #64748b;">
                                <i class="fa-solid fa-couch"></i>
                            </div>
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(51, 65, 85, 0.8); display: flex; align-items: center; justify-content: center; color: #64748b;">
                                <i class="fa-solid fa-couch"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Introduction Card -->
                    <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 14px;">
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 4px; text-transform: uppercase; font-weight: 600;">Introduction</div>
                        <div id="previewIntroductionText" style="font-size: 13px; color: #e2e8f0; line-height: 1.4;">
                            {{ $user->introduction ?: 'Sweet girl looking for honest talk ❤️' }}
                        </div>
                    </div>

                    <!-- Languages & Tags Cards -->
                    <div style="background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 14px;">
                        <div style="font-size: 11px; color: #64748b; margin-bottom: 6px; text-transform: uppercase; font-weight: 600;">Languages & Interests</div>
                        <div id="previewTagsList" style="display: flex; flex-wrap: wrap; gap: 6px;">
                            @foreach(($user->languages ?? ['English', 'Urdu']) as $lang)
                                <span style="background: rgba(99, 102, 241, 0.2); border: 1px solid #6366f1; color: #a5b4fc; font-size: 11px; padding: 3px 8px; border-radius: 12px;">🌐 {{ $lang }}</span>
                            @endforeach
                            @foreach(($user->tags ?? ['Live video', 'Music']) as $tg)
                                <span style="background: rgba(236, 72, 153, 0.2); border: 1px solid #ec4899; color: #f472b6; font-size: 11px; padding: 3px 8px; border-radius: 12px;">🏷️ {{ $tg }}</span>
                            @endforeach
                        </div>
                    </div>

                </div>

                <!-- Bottom Floating Actions: "Hi" Button & "Video Call 1800/min" Button -->
                <div style="position: absolute; bottom: 0; left: 0; right: 0; z-index: 20; padding: 14px 18px; background: rgba(15, 15, 24, 0.95); backdrop-filter: blur(10px); border-top: 1px solid rgba(255,255,255,0.08); display: flex; gap: 10px; align-items: center;">
                    <!-- Hi Button -->
                    <button type="button" style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #a855f7, #9333ea); border: none; color: #fff; font-weight: 700; font-size: 15px; cursor: pointer; flex-shrink: 0; box-shadow: 0 4px 10px rgba(147, 51, 234, 0.3);">
                        Hi
                    </button>
                    <!-- Video Call Button -->
                    <button type="button" style="flex: 1; height: 48px; border-radius: 24px; background: linear-gradient(90deg, #7c3aed 0%, #db2777 50%, #e11d48 100%); border: none; color: #fff; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; box-shadow: 0 6px 20px rgba(219, 39, 119, 0.4);">
                        <i class="fa-solid fa-video"></i>
                        <span>Video Call</span>
                        <span style="font-size: 13px; font-weight: 600; color: #fef08a;">💎 {{ $user->video_call_rate ?: 1800 }}/min</span>
                    </button>
                </div>

            </div>
        </div>

        <!-- RIGHT: Interactive Profile Editor & API Controller Panel -->
        <div style="display: flex; flex-direction: column; gap: 20px;">

            <!-- Card 1: Real Multi-Image & Cover Upload Form -->
            <div class="card-widget" style="background: #fff; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div class="widget-header" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-images" style="color: #6366f1;"></i>
                        Upload Profile & Gallery Photos (Real Storage & DB)
                    </h3>
                    <span style="font-size: 12px; background: #e0e7ff; color: #4338ca; padding: 3px 8px; border-radius: 12px; font-weight: 600;">
                        Endpoint: POST /api/profile/upload-photos
                    </span>
                </div>

                <form id="photosUploadForm" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <label style="font-size: 13px; font-weight: 600; color: #475569; display: block; margin-bottom: 6px;">
                                Select 1 to 5 Images (Gallery & Cover)
                            </label>
                            <input type="file" id="galleryFilesInput" name="photos[]" multiple accept="image/*" class="form-control" style="width: 100%; border: 1px dashed #6366f1; padding: 12px; border-radius: 8px; background: #f8fafc;">
                            <small style="color: #64748b; font-size: 11px; margin-top: 4px; display: block;">
                                * First image will automatically set as background cover photo.
                            </small>
                        </div>
                        <div style="display: flex; flex-direction: column; justify-content: flex-end;">
                            <button type="button" id="btnUploadPhotos" class="btn btn-primary" style="background: #6366f1; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Upload & Save to Database
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Current Uploaded Gallery List with delete buttons -->
                <div style="margin-top: 16px; border-top: 1px solid #f1f5f9; padding-top: 12px;">
                    <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;">Current Gallery Photos in Database:</div>
                    <div id="currentGalleryThumbnails" style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @forelse($user->gallery_image_urls as $imgUrl)
                            <div style="position: relative; width: 70px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1;">
                                <img src="{{ $imgUrl }}" style="width: 100%; height: 100%; object-fit: cover;" alt="Photo">
                                <button type="button" onclick="deleteGalleryPhoto('{{ $imgUrl }}')" style="position: absolute; top: 2px; right: 2px; background: rgba(239,68,68,0.9); color: #fff; border: none; border-radius: 50%; width: 20px; height: 20px; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;" title="Delete">
                                    &times;
                                </button>
                            </div>
                        @empty
                            <span style="font-size: 12px; color: #94a3b8;">No gallery photos uploaded yet. Using default previews.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Card 2: Profile Details Live Editor -->
            <div class="card-widget" style="background: #fff; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                <div class="widget-header" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-user-pen" style="color: #ec4899;"></i>
                        Edit Customer Information (Direct Database Update)
                    </h3>
                    <span style="font-size: 12px; background: #fce7f3; color: #be185d; padding: 3px 8px; border-radius: 12px; font-weight: 600;">
                        Endpoint: POST /api/profile/update
                    </span>
                </div>

                <form id="profileUpdateForm">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">First Name</label>
                            <input type="text" id="inputFirstName" name="first_name" value="{{ $user->first_name }}" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Last Name</label>
                            <input type="text" id="inputLastName" name="last_name" value="{{ $user->last_name }}" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Nickname / Display Name (e.g. Ayeena04)</label>
                            <input type="text" id="inputNickname" name="nickname" value="{{ $user->nickname ?: $user->name }}" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">10-12 Digit User ID (Auto)</label>
                            <input type="text" value="{{ $user->account_id }}" readonly class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f1f5f9; color: #64748b;">
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Gender</label>
                            <select id="inputGender" name="gender" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                                <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female (♀)</option>
                                <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male (♂)</option>
                                <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Age</label>
                            <input type="number" id="inputAge" name="age" value="{{ $user->age ?: 27 }}" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Country / Location (e.g. Pakistan)</label>
                            <input type="text" id="inputCountry" name="country" value="{{ $user->country ?: 'Pakistan' }}" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Level Badge</label>
                            <input type="text" id="inputLevel" name="level" value="{{ $user->level ?: 'Lv4' }}" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Active Status</label>
                            <select id="inputIsActive" name="is_active" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                                <option value="1" {{ $user->is_active ? 'selected' : '' }}>🟢 Active (Online)</option>
                                <option value="0" {{ !$user->is_active ? 'selected' : '' }}>⚪ Offline</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Video Call Rate (Diamonds/min)</label>
                            <input type="number" id="inputVideoRate" name="video_call_rate" value="{{ $user->video_call_rate ?: 1800 }}" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        </div>

                        <div style="grid-column: span 2;">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Introduction / Bio</label>
                            <textarea id="inputIntroduction" name="introduction" rows="2" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">{{ $user->introduction ?: 'Sweet girl looking for honest talk ❤️' }}</textarea>
                        </div>

                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Languages (comma separated)</label>
                            <input type="text" id="inputLanguages" name="languages" value="{{ is_array($user->languages) ? implode(', ', $user->languages) : 'English, Urdu' }}" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Interest Tags (comma separated)</label>
                            <input type="text" id="inputTags" name="tags" value="{{ is_array($user->tags) ? implode(', ', $user->tags) : 'Live video, Music, Singing' }}" class="form-control" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                        </div>
                    </div>

                    <div style="margin-top: 16px; display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" id="btnSaveProfile" class="btn btn-success" style="background: #10b981; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-check"></i> Save Changes to Database
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card 3: REST API Quick Documentation & Examples for Flutter / Mobile App -->
            <div class="card-widget" style="background: #0f172a; border-radius: 16px; padding: 20px; color: #f8fafc;">
                <h3 style="margin: 0 0 12px 0; font-size: 15px; font-weight: 700; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-code"></i>
                    RESTful API Endpoints Quick Reference (for Mobile App)
                </h3>
                <div style="font-size: 12px; line-height: 1.6; color: #cbd5e1;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 1px solid #334155; color: #94a3b8; text-align: left;">
                                <th style="padding: 6px 0;">Method</th>
                                <th style="padding: 6px 0;">Endpoint</th>
                                <th style="padding: 6px 0;">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="color: #4ade80; font-weight: bold; padding: 6px 0;">POST</td>
                                <td style="color: #fcd34d;">/api/register</td>
                                <td>Register with first/last name, phone, email, password. Auto-creates 10-12 digit ID.</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="color: #4ade80; font-weight: bold; padding: 6px 0;">POST</td>
                                <td style="color: #fcd34d;">/api/login</td>
                                <td>Login with email, phone, or 10-12 digit Account ID.</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="color: #60a5fa; font-weight: bold; padding: 6px 0;">GET</td>
                                <td style="color: #fcd34d;">/api/user & /api/profile/{id}</td>
                                <td>Retrieve full user profile with URLs, gallery, badges.</td>
                            </tr>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="color: #4ade80; font-weight: bold; padding: 6px 0;">POST</td>
                                <td style="color: #fcd34d;">/api/profile/upload-photos</td>
                                <td>Upload multiple photos (multipart/form-data <code>photos[]</code>).</td>
                            </tr>
                            <tr>
                                <td style="color: #4ade80; font-weight: bold; padding: 6px 0;">POST</td>
                                <td style="color: #fcd34d;">/api/profile/update</td>
                                <td>Update bio, tags, age, location, gender, rate, nickname.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userId = "{{ $user->id }}";

    // Live sync form inputs to preview
    document.getElementById('inputNickname').addEventListener('input', function(e) {
        document.getElementById('previewDisplayName').textContent = e.target.value || 'User';
    });

    document.getElementById('inputAge').addEventListener('input', function(e) {
        document.getElementById('previewAgeText').textContent = e.target.value || '27';
    });

    document.getElementById('inputCountry').addEventListener('input', function(e) {
        document.getElementById('previewCountryText').textContent = e.target.value || 'Pakistan';
    });

    document.getElementById('inputLevel').addEventListener('input', function(e) {
        document.getElementById('previewLevelBadge').textContent = e.target.value || 'Lv4';
    });

    document.getElementById('inputIntroduction').addEventListener('input', function(e) {
        document.getElementById('previewIntroductionText').textContent = e.target.value;
    });

    document.getElementById('inputIsActive').addEventListener('change', function(e) {
        const isActive = e.target.value === '1';
        document.getElementById('previewStatusText').textContent = isActive ? 'Active' : 'Offline';
        document.getElementById('previewActiveBadge').style.borderColor = isActive ? '#10b981' : '#64748b';
        document.getElementById('previewActiveBadge').style.color = isActive ? '#10b981' : '#64748b';
    });

    // Save Profile via AJAX
    document.getElementById('btnSaveProfile').addEventListener('click', async function() {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        const formData = {
            first_name: document.getElementById('inputFirstName').value,
            last_name: document.getElementById('inputLastName').value,
            nickname: document.getElementById('inputNickname').value,
            gender: document.getElementById('inputGender').value,
            age: document.getElementById('inputAge').value,
            country: document.getElementById('inputCountry').value,
            level: document.getElementById('inputLevel').value,
            is_active: document.getElementById('inputIsActive').value,
            video_call_rate: document.getElementById('inputVideoRate').value,
            introduction: document.getElementById('inputIntroduction').value,
            languages: document.getElementById('inputLanguages').value,
            tags: document.getElementById('inputTags').value,
        };

        try {
            const response = await fetch('/api/profile/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();
            if (result.status) {
                alert('Profile updated successfully in database!');
                window.location.reload();
            } else {
                alert('Update failed: ' + (result.message || 'Error'));
            }
        } catch (err) {
            console.error(err);
            alert('Error updating profile: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Save Changes to Database';
        }
    });

    // Upload Multi Photos via AJAX
    document.getElementById('btnUploadPhotos').addEventListener('click', async function() {
        const filesInput = document.getElementById('galleryFilesInput');
        if (!filesInput.files || filesInput.files.length === 0) {
            alert('Please select at least one photo to upload.');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';

        const formData = new FormData();
        for (let i = 0; i < filesInput.files.length; i++) {
            formData.append('photos[]', filesInput.files[i]);
        }

        try {
            const response = await fetch('/api/profile/upload-photos', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });

            const result = await response.json();
            if (result.status) {
                alert(result.message || 'Photos uploaded successfully!');
                window.location.reload();
            } else {
                alert('Upload failed: ' + (result.message || 'Error'));
            }
        } catch (err) {
            console.error(err);
            alert('Upload error: ' + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload & Save to Database';
        }
    });
});

async function deleteGalleryPhoto(photoUrl) {
    if (!confirm('Are you sure you want to remove this photo?')) return;

    try {
        const response = await fetch('/api/profile/delete-photo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ photo: photoUrl })
        });

        const result = await response.json();
        if (result.status) {
            alert('Photo deleted.');
            window.location.reload();
        } else {
            alert('Failed: ' + (result.message || 'Error'));
        }
    } catch (err) {
        console.error(err);
        alert('Delete error: ' + err.message);
    }
}
</script>
@endsection
