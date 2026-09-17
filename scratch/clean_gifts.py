import os

flutter_lib = r"F:\Chinchins Live\lib"

# 1. Clean gifts_api_service.dart (remove duplicate classes)
gifts_api_path = os.path.join(flutter_lib, "core", "services", "gifts_api_service.dart")
with open(gifts_api_path, "r", encoding="utf-8") as f:
    g_content = f.read()

idx = g_content.find("class ReceivedGiftItem")
if idx != -1:
    g_content = g_content[:idx].rstrip() + "\n"
    with open(gifts_api_path, "w", encoding="utf-8") as f:
        f.write(g_content)
    print("Cleaned duplicate classes from gifts_api_service.dart")

# 2. Update me_screen.dart imports and _buildReceivedGiftsShowcase
me_path = os.path.join(flutter_lib, "features", "me", "screens", "me_screen.dart")
with open(me_path, "r", encoding="utf-8") as f:
    me_content = f.read()

if "import '../../../core/models/gift_item.dart';" not in me_content:
    me_content = me_content.replace(
        "import '../../../core/services/gifts_api_service.dart';",
        "import '../../../core/models/gift_item.dart';\nimport '../../../core/services/gifts_api_service.dart';",
        1
    )

old_showcase = """  Widget _buildReceivedGiftsShowcase() {
    final gifts = _receivedGifts?.gifts ?? [];
    final totalCount = _receivedGifts?.totalGiftsReceived ?? 0;
    final charmPoints = _receivedGifts?.charmPoints ?? 0;
    final charmLevel = _receivedGifts?.charmLevel ?? 'Lv.1';"""

new_showcase = """  Widget _buildReceivedGiftsShowcase() {
    final gifts = _receivedGifts?.giftsReceived ?? _receivedGifts?.profilePreviewGifts ?? [];
    final totalCount = _receivedGifts?.summary.totalGiftsReceived ?? 0;
    final charmPoints = _receivedGifts?.charmLevel.currentPoints ?? 0;
    final charmLevel = _receivedGifts?.charmLevel.currentLevelName ?? 'Lv.1';"""

if old_showcase in me_content:
    me_content = me_content.replace(old_showcase, new_showcase, 1)

# Also fix property accesses for GiftItem
me_content = me_content.replace("g.animationAssetUrl", "g.animationUrl")
me_content = me_content.replace("g.isHot", "(g.coins >= 1000)")

with open(me_path, "w", encoding="utf-8") as f:
    f.write(me_content)
print("me_screen.dart updated with GiftItem compatibility")

