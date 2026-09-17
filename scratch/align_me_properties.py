import os

flutter_lib = r"F:\Chinchins Live\lib"
me_path = os.path.join(flutter_lib, "features", "me", "screens", "me_screen.dart")

with open(me_path, "r", encoding="utf-8") as f:
    me_content = f.read()

# Replace _buildReceivedGiftsShowcase with accurate GiftItem and GiftsSummary properties
old_method_start = me_content.find("  Widget _buildReceivedGiftsShowcase() {")
old_method_end = me_content.find("  Widget _buildGridMenuItem(", old_method_start)

accurate_method = """  Widget _buildReceivedGiftsShowcase() {
    final gifts = _receivedGifts?.giftsReceived ?? _receivedGifts?.profilePreviewGifts ?? [];
    final totalCount = _receivedGifts?.summary.totalItemsCount ?? 0;
    final totalCoins = _receivedGifts?.summary.formattedCoins ?? '0';
    final charmLevel = _receivedGifts?.charmLevel.levelTag ?? 'Lv1';

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
                        'Total $totalCount Gifts • $totalCoins Coins Value',
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
                  final cnt = g.receivedCount > 0 ? g.receivedCount : 1;
                  return Container(
                    width: 82,
                    margin: const EdgeInsets.only(right: 10),
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 8),
                    decoration: BoxDecoration(
                      color: AppColors.cardDarkElevated,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: g.coins >= 1000 ? const Color(0xFFFF007F).withValues(alpha: 0.5) : Colors.white10,
                        width: 0.8,
                      ),
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        if (g.animationUrl != null && g.animationUrl!.isNotEmpty)
                          CachedImageLoader(imageUrl: g.animationUrl!, width: 36, height: 36, fit: BoxFit.contain)
                        else if (g.imageUrl.isNotEmpty)
                          CachedImageLoader(imageUrl: g.imageUrl, width: 36, height: 36, fit: BoxFit.contain)
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
                            'x$cnt',
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
\n"""

me_content = me_content[:old_method_start] + accurate_method + me_content[old_method_end:]

with open(me_path, "w", encoding="utf-8") as f:
    f.write(me_content)
print("me_screen.dart exact properties aligned successfully")
