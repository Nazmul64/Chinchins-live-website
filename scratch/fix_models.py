import os

flutter_lib = r"F:\Chinchins Live\lib"
gifts_api_path = os.path.join(flutter_lib, "core", "services", "gifts_api_service.dart")

with open(gifts_api_path, "r", encoding="utf-8") as f:
    g_content = f.read()

models_code = """
class ReceivedGiftItem {
  final String id;
  final String giftId;
  final String name;
  final String category;
  final String emoji;
  final String? animationAssetUrl;
  final int coins;
  final int quantity;
  final bool isHot;

  const ReceivedGiftItem({
    required this.id,
    required this.giftId,
    required this.name,
    this.category = 'popular',
    this.emoji = '🎁',
    this.animationAssetUrl,
    required this.coins,
    required this.quantity,
    this.isHot = false,
  });

  factory ReceivedGiftItem.fromJson(Map<String, dynamic> json) {
    final giftObj = json['gift'] is Map ? json['gift'] as Map : null;
    final id = json['id']?.toString() ?? json['gift_id']?.toString() ?? '0';
    final giftId = json['gift_id']?.toString() ?? giftObj?['id']?.toString() ?? id;
    final name = json['gift_name']?.toString() ?? giftObj?['name']?.toString() ?? json['name']?.toString() ?? 'Super Gift';
    final emoji = json['emoji']?.toString() ?? giftObj?['emoji']?.toString() ?? '🎁';
    final animationAssetUrl = json['animation_asset_url']?.toString() ??
        giftObj?['animation_asset_url']?.toString() ??
        json['image_url']?.toString() ??
        giftObj?['icon_url']?.toString();
    final coins = json['coins'] is int
        ? json['coins']
        : int.tryParse('${json['coins'] ?? giftObj?['coin_price'] ?? 100}') ?? 100;
    final quantity = json['quantity'] is int
        ? json['quantity']
        : int.tryParse('${json['quantity'] ?? json['count'] ?? 1}') ?? 1;

    return ReceivedGiftItem(
      id: id,
      giftId: giftId,
      name: name,
      emoji: emoji,
      animationAssetUrl: animationAssetUrl,
      coins: coins,
      quantity: quantity,
      isHot: json['is_hot'] == true || (coins >= 1000),
    );
  }
}

class UserGiftsData {
  final int totalGiftsReceived;
  final String formattedGiftsCount;
  final int charmPoints;
  final String charmLevel;
  final List<ReceivedGiftItem> gifts;

  const UserGiftsData({
    this.totalGiftsReceived = 0,
    this.formattedGiftsCount = '0',
    this.charmPoints = 0,
    this.charmLevel = 'Lv.1',
    this.gifts = const [],
  });

  factory UserGiftsData.fromJson(Map<String, dynamic> json) {
    final data = json['data'] is Map ? json['data'] as Map<String, dynamic> : json;
    final listRaw = data['gifts'] ?? data['received_gifts'] ?? data['list'];
    final List<ReceivedGiftItem> list = [];
    if (listRaw is List) {
      for (final item in listRaw) {
        if (item is Map) {
          list.add(ReceivedGiftItem.fromJson(Map<String, dynamic>.from(item)));
        }
      }
    }

    final total = data['total_gifts'] is int
        ? data['total_gifts'] as int
        : (data['total_count'] is int ? data['total_count'] as int : list.fold<int>(0, (sum, g) => sum + g.quantity));

    final charmPts = data['charm_points'] is int
        ? data['charm_points'] as int
        : (data['charm'] is int ? data['charm'] as int : (data['total_coins'] is int ? data['total_coins'] as int : total * 100));

    final charmLvl = data['charm_level']?.toString() ?? (charmPts > 10000 ? 'Lv.5' : (charmPts > 5000 ? 'Lv.3' : 'Lv.1'));

    return UserGiftsData(
      totalGiftsReceived: total,
      formattedGiftsCount: total > 1000 ? '${(total / 1000).toStringAsFixed(1)}K' : '$total',
      charmPoints: charmPts,
      charmLevel: charmLvl,
      gifts: list,
    );
  }
}
"""

if "class UserGiftsData" not in g_content:
    g_content = g_content + "\n" + models_code
    with open(gifts_api_path, "w", encoding="utf-8") as f:
        f.write(g_content)
    print("gifts_api_service.dart updated with UserGiftsData and ReceivedGiftItem")
else:
    print("UserGiftsData already in gifts_api_service.dart")

