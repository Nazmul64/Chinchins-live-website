import os

flutter_lib = r"F:\Chinchins Live\lib"
me_path = os.path.join(flutter_lib, "features", "me", "screens", "me_screen.dart")

with open(me_path, "r", encoding="utf-8") as f:
    me_content = f.read()

target = """                // 6. Create Party Room Banner
                GestureDetector(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const CreateRoomScreen()),
                    );
                  },
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF2A1B4E), Color(0xFF1B2B4E)],
                      ),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.cardBorder),
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: const BoxDecoration(
                            color: Color(0xFF6C63FF),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.mic_rounded, color: Colors.white, size: 20),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Create a party room', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
                              SizedBox(height: 2),
                              Text('To start a great party and earn gifts', style: TextStyle(color: AppColors.textMuted, fontSize: 11)),
                            ],
                          ),
                        ),
                        const Icon(Icons.chevron_right_rounded, color: Colors.white54, size: 22),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 14),

                // 7. Settings, Feedback & Update Menu Block"""

replacement = """                // 6. Create Party Room Banner
                GestureDetector(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => const CreateRoomScreen()),
                    );
                  },
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF2A1B4E), Color(0xFF1B2B4E)],
                      ),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.cardBorder),
                    ),
                    child: Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: const BoxDecoration(
                            color: Color(0xFF6C63FF),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(Icons.mic_rounded, color: Colors.white, size: 20),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text('Create a party room', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
                              SizedBox(height: 2),
                              Text('To start a great party and earn gifts', style: TextStyle(color: AppColors.textMuted, fontSize: 11)),
                            ],
                          ),
                        ),
                        const Icon(Icons.chevron_right_rounded, color: Colors.white54, size: 22),
                      ],
                    ),
                  ),
                ),

                // 6.1 Received Gifts Showcase (Rendered under Create party room)
                _buildReceivedGiftsShowcase(),
                const SizedBox(height: 14),

                // 7. Settings, Feedback & Update Menu Block"""

if target in me_content:
    me_content = me_content.replace(target, replacement, 1)
    with open(me_path, "w", encoding="utf-8") as f:
        f.write(me_content)
    print("me_screen.dart showcase successfully inserted!")
else:
    print("target not matched in me_screen.dart")
