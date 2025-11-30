<?php
require_once 'config.php';
requireAuth(); // <-- Authentication Check
require_once 'layout.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serial = trim($_POST['serial']);
    $adderName = $_SESSION['username'] ?? 'Admin Manual'; // Use logged-in user
    
    if (!empty($serial)) {
        try {
            $check = $pdo->prepare("SELECT id FROM ppwhitelist WHERE serial = ?");
            $check->execute([$serial]);

            if ($check->rowCount() > 0) {
                 $message = "<div class='bg-yellow-500/10 text-yellow-400 p-4 rounded-lg mb-6 border border-yellow-500/20'>Serial already whitelisted.</div>";
            } else {
                $stmt = $pdo->prepare("INSERT INTO ppwhitelist (serial, addedBy) VALUES (?, ?)");
                $stmt->execute([$serial, $adderName]);
                $message = "<div class='bg-green-500/10 text-green-400 p-4 rounded-lg mb-6 border border-green-500/20'>Serial Added successfully by {$adderName}!</div>";
            }
        } catch (Exception $e) {
            $message = "<div class='bg-red-500/10 text-red-400 p-4 rounded-lg mb-6 border border-red-500/20'>Error: " . $e->getMessage() . "</div>";
        }
    }
}

siteHeader("Add Whitelist");
?>

<div class="max-w-4xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Form Section -->
        <div class="md:col-span-2">
            <h2 class="text-3xl font-bold text-white mb-6">Manual Whitelist</h2>
            <?= $message ?>
            
            <div class="bg-dark-800 border border-gray-700 rounded-xl p-8 shadow-xl">
                <form method="POST" action="">
                    <div class="mb-6">
                        <label class="block text-gray-400 text-sm font-bold mb-2 uppercase tracking-wider" for="serial">
                            MTA Serial Key
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-key text-gray-600"></i>
                            </div>
                            <input class="w-full bg-dark-900 text-white border border-gray-700 rounded-lg py-3 pl-10 pr-3 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500 transition font-mono placeholder-gray-600" 
                                   id="serial" name="serial" type="text" placeholder="32-character hash..." required>
                        </div>
                        <p class="text-gray-500 text-xs mt-2">Paste the MD5 serial hash from the player's console.</p>
                    </div>
                    
                    <button class="w-full bg-gradient-to-r from-brand-600 to-purple-600 hover:from-brand-500 hover:to-purple-500 text-white font-bold py-3 px-6 rounded-lg transition transform active:scale-95 shadow-lg shadow-brand-600/30">
                        <i class="fas fa-plus-circle mr-2"></i> Add to Database
                    </button>
                </form>
            </div>
        </div>

        <!-- Sidebar/Recents -->
        <div>
            <h3 class="text-lg font-bold text-white mb-4">Recent Additions</h3>
            <div class="bg-dark-800 border border-gray-700 rounded-xl overflow-hidden">
                <div class="divide-y divide-gray-700/50">
                    <?php
                    $recents = $pdo->query("SELECT * FROM ppwhitelist ORDER BY id DESC LIMIT 5")->fetchAll();
                    foreach ($recents as $r):
                    ?>
                    <div class="p-4 hover:bg-dark-700/30 transition">
                        <div class="text-xs font-mono text-green-400 truncate mb-1"><?= e($r['serial']) ?></div>
                        <div class="flex justify-between items-center text-xs text-gray-500">
                            <span><i class="fas fa-user-shield mr-1"></i> <?= e($r['addedBy']) ?></span>
                            <span><?= date('m/d H:i', strtotime($r['addedOn'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php siteFooter(); ?>