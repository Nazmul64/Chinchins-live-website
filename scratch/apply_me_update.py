import os

flutter_lib = r"F:\Chinchins Live\lib"
me_path = os.path.join(flutter_lib, "features", "me", "screens", "me_screen.dart")

with open(me_path, "r", encoding="utf-8") as f:
    me_content = f.read()

# 1. Add imports if not present
if "gifts_api_service.dart" not in me_content:
    import_target = "import '../../party/screens/create_room_screen.dart';"
    import_repl = """import '../../party/screens/create_room_screen.dart';
import '../../../core/services/gifts_api_service.dart';
import '../../profile/screens/gifts_received_screen.dart';"""
    me_content = me_content.replace(import_target, import_repl, 1)

# 2. Add state variable
if "UserGiftsData? _receivedGifts;" not in me_content:
    var_target = "  ModelProfile? _myProfile;"
    var_repl = """  ModelProfile? _myProfile;
  UserGiftsData? _receivedGifts;"""
    me_content = me_content.replace(var_target, var_repl, 1)

# 3. Add fetching in _loadUserProfile
if "getReceivedGifts" not in me_content:
    load_target = "        if (walletData != null && walletData['balance'] != null) {\n          _beans = walletData['balance']['beans'] ?? _beans;\n        }"
    load_repl = """        if (walletData != null && walletData['balance'] != null) {
          _beans = walletData['balance']['beans'] ?? _beans;
        }

        final myId = _myProfile?.effectiveAccountId ?? _myProfile?.id ?? savedUser?['id'] ?? savedUser?['account_id'];
        if (myId != null) {
          GiftsApiService.getReceivedGifts(myId, forceRefresh: true).then((gData) {
            if (gData != null && mounted) {
              setState(() {
                _receivedGifts = gData;
              });
            }
          });
        }"""
    me_content = me_content.replace(load_target, load_repl, 1)

# 4. Add _buildReceivedGiftsShowcase widget method
widget_code = """
  Widget _buildReceivedGiftsShowcase() {
    final gifts = _receivedGifts?.gifts ?? [];
    final totalCount = _receivedGifts?.totalGiftsReceived ?? 0;
    final charmPoints = _receivedGifts?.charmPoints ?? 0;
    final charmLevel = _receivedGifts?.charmLevel ?? 'Lv.1';

    return Container(
      width: double.infinity,
      margin: const EdgeInsets.only(top: 14),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.cardDark,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppColors.cardBorder, width: 0.8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(7),
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFFFF007F), Color(0xFFFFB300)],
                      ),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.card_giftcard_rounded, color: Colors.white, size: 16),
                  ),
                  const SizedBox(width: 8),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Text(
                            'Received Gifts',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFFB300).withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFFFB300), width: 0.8),
                            ),
                            child: Text(
                              charmLevel,
                              style: const TextStyle(
                                color: Color(0xFFFFB300),
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                      Text(
                        'Total $totalCount Gifts • $charmPoints Charm Points',
                        style: const TextStyle(color: AppColors.textMuted, fontSize: 11),
                      ),
                    ],
                  ),
                ],
              ),
              GestureDetector(
                onTap: () {
                  final uid = _myProfile?.effectiveAccountId ?? _myProfile?.id ?? 'me';
                  final uname = _myProfile?.name ?? 'My Profile';
                  final uavatar = _myProfile?.avatarUrl;
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (context) => GiftsReceivedScreen(
                        userId: uid,
                        userName: uname,
                        userAvatar: uavatar,
                      ),
                    ),
                  );
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: Colors.white12),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text('View All', style: TextStyle(color: AppColors.neonPink, fontSize: 11, fontWeight: FontWeight.bold)),
                      SizedBox(width: 2),
                      Icon(Icons.chevron_right_rounded, color: AppColors.neonPink, size: 14),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Gift Horizontal Carousel or Empty State
          if (gifts.isNotEmpty)
            SizedBox(
              height: 100,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                physics: const BouncingScrollPhysics(),
                itemCount: gifts.length,
                itemBuilder: (context, index) {
                  final g = gifts[index];
                  return Container(
                    width: 82,
                    margin: const EdgeInsets.only(right: 10),
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 8),
                    decoration: BoxDecoration(
                      color: AppColors.cardDarkElevated,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: g.isHot ? const Color(0xFFFF007F).withValues(alpha: 0.5) : Colors.white10,
                        width: 0.8,
                      ),
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        if (g.animationAssetUrl != null && g.animationAssetUrl!.isNotEmpty)
                          CachedImageLoader(imageUrl: g.animationAssetUrl!, width: 36, height: 36, fit: BoxFit.contain)
                        else
                          Text(g.emoji, style: const TextStyle(fontSize: 26)),
                        const SizedBox(height: 4),
                        Text(
                          g.name,
                          style: const TextStyle(color: Colors.white, fontSize: 10.5, fontWeight: FontWeight.w600),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFF007F).withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            'x${g.quantity}',
                            style: const TextStyle(
                              color: Color(0xFFFF007F),
                              fontSize: 9.5,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
            )
          else
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
              decoration: BoxDecoration(
                color: AppColors.cardDarkElevated,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.white.withValues(alpha: 0.05)),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFFB300).withValues(alpha: 0.15),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.stars_rounded, color: Color(0xFFFFB300), size: 20),
                  ),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'No gifts received yet',
                          style: TextStyle(color: Colors.white, fontSize: 12.5, fontWeight: FontWeight.bold),
                        ),
                        SizedBox(height: 2),
                        Text(
                          'Start live streaming or video calls to earn luxury gifts & diamonds!',
                          style: TextStyle(color: AppColors.textMuted, fontSize: 10.5),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
"""

if "_buildReceivedGiftsShowcase" not in me_content:
    # Add the method before build or at end of class
    idx = me_content.rfind("  Widget _buildGridMenuItem(")
    if idx != -1:
        me_content = me_content[:idx] + widget_code + "\n\n" + me_content[idx:]
    else:
        # Fallback
        idx2 = me_content.rfind("}")
        me_content = me_content[:idx2] + widget_code + "\n}\n"

# 5. Insert _buildReceivedGiftsShowcase() below the Create Party Room banner
banner_target = """                              ],
                            ),
                          ),
                          const Icon(Icons.chevron_right_rounded, color: Colors.white54, size: 22),
                        ],
                      ),
                    ),
                  ),"""

banner_repl = """                              ],
                            ),
                          ),
                          const Icon(Icons.chevron_right_rounded, color: Colors.white54, size: 22),
                        ],
                      ),
                    ),
                  ),

                // 6.1 Received Gifts Showcase (Display under Create Party Room)
                _buildReceivedGiftsShowcase(),"""

if banner_target in me_content:
    me_content = me_content.replace(banner_target, banner_repl, 1)

with open(me_path, "w", encoding="utf-8") as f:
    f.write(me_content)

print("me_screen.dart updated with Received Gifts showcase!")
