<?php
require_once 'config.php';
requireAuth(); // <-- Authentication Check
require_once 'layout.php';

$message = '';

// Handle Actions (Accept/Decline)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appId = $_POST['app_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $serial = $_POST['serial'] ?? null;
    $reviewerName = $_SESSION['username'] ?? 'Admin'; // Use logged-in user

    if ($appId && $action) {
        if ($action === 'accept') {
            try {
                $pdo->beginTransaction();
                // 1. Update whitelistapp
                $stmt = $pdo->prepare("UPDATE whitelistapp SET status = 'Accepted', reviewedBy = ? WHERE id = ?");
                $stmt->execute([$reviewerName, $appId]);

                // 2. Insert into ppwhitelist
                $checkStmt = $pdo->prepare("SELECT id FROM ppwhitelist WHERE serial = ?");
                $checkStmt->execute([$serial]);
                
                if ($checkStmt->rowCount() == 0) {
                    $insertStmt = $pdo->prepare("INSERT INTO ppwhitelist (serial, addedBy) VALUES (?, ?)");
                    $insertStmt->execute([$serial, $reviewerName . ' Application']);
                    $message = "<div class='bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg mb-6 flex items-center gap-3'><i class='fas fa-check-circle text-xl'></i> <div><span class='font-bold'>Success!</span> Application Accepted & Whitelisted by {$reviewerName}.</div></div>";
                } else {
                    $message = "<div class='bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 p-4 rounded-lg mb-6'>Application Accepted, but Serial was already whitelisted.</div>";
                }
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $message = "<div class='bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6'>Error: " . $e->getMessage() . "</div>";
            }
        } elseif ($action === 'decline') {
            $stmt = $pdo->prepare("UPDATE whitelistapp SET status = 'Declined', reviewedBy = ? WHERE id = ?");
            $stmt->execute([$reviewerName, $appId]);
            $message = "<div class='bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-lg mb-6'>Application Declined by {$reviewerName}.</div>";
        }
    }
}

// Fetch Pending
$stmt = $pdo->query("SELECT * FROM whitelistapp WHERE status = 'Pending' ORDER BY applicationDate DESC");
$applications = $stmt->fetchAll();

siteHeader("Dashboard");
?>

<!-- Header Section -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
        <h2 class="text-3xl font-bold text-white">Dashboard</h2>
        <p class="text-gray-400 mt-1">Manage incoming whitelist requests</p>
    </div>
    <div class="flex items-center gap-4 bg-dark-800 px-4 py-2 rounded-lg border border-gray-700">
        <div class="flex flex-col text-right">
            <span class="text-xs text-gray-500 uppercase font-bold">Pending</span>
            <span class="text-xl font-bold text-white"><?= count($applications) ?></span>
        </div>
        <div class="w-10 h-10 rounded-full bg-brand-500/20 flex items-center justify-center text-brand-500">
            <i class="fas fa-clock"></i>
        </div>
    </div>
</div>

<?= $message ?>

<!-- Applications Grid -->
<?php if (count($applications) > 0): ?>
    <div class="grid grid-cols-1 gap-6">
        <?php foreach ($applications as $app): ?>
        <div class="glass-panel rounded-xl p-6 transition hover:border-brand-500/50 group relative">
            <div class="flex flex-col md:flex-row justify-between gap-6">
                
                <!-- Main Info -->
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-2 py-1 bg-yellow-500/20 text-yellow-500 text-xs font-bold uppercase rounded border border-yellow-500/20">Pending</span>
                        <span class="text-gray-500 text-xs"><i class="far fa-calendar-alt mr-1"></i> <?= date('M d, H:i', strtotime($app['applicationDate'])) ?></span>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-1"><?= e($app['fullName']) ?></h3>
                    <div class="flex flex-wrap gap-4 text-sm text-gray-400 mt-3">
                        <div class="flex items-center gap-2 bg-dark-900/50 px-3 py-1.5 rounded-lg border border-gray-700/50">
                            <i class="fab fa-discord text-indigo-400"></i> <?= e($app['discordUsername']) ?>
                        </div>
                        <div class="flex items-center gap-2 bg-dark-900/50 px-3 py-1.5 rounded-lg border border-gray-700/50 font-mono text-xs">
                            <i class="fas fa-fingerprint text-gray-500"></i> <?= substr(e($app['serial']), 0, 15) ?>...
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <!-- View Modal Trigger -->
                    <button onclick='openModal(<?= json_encode($app) ?>)' class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg font-medium transition text-sm flex items-center gap-2">
                        <i class="fas fa-eye"></i> View Full
                    </button>
                    
                    <form method="POST" onsubmit="return confirm('Accept this user?');">
                        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                        <input type="hidden" name="serial" value="<?= $app['serial'] ?>">
                        <input type="hidden" name="action" value="accept">
                        <button class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg font-medium transition text-sm shadow-lg shadow-green-900/20">
                            Accept
                        </button>
                    </form>

                    <form method="POST" onsubmit="return confirm('Decline this user?');">
                        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                        <input type="hidden" name="action" value="decline">
                        <button class="px-4 py-2 bg-red-600/20 hover:bg-red-600/40 border border-red-600/30 text-red-400 hover:text-red-200 rounded-lg font-medium transition text-sm">
                            Decline
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="flex flex-col items-center justify-center py-20 bg-dark-800/50 rounded-xl border border-gray-800 border-dashed">
        <div class="w-16 h-16 bg-dark-800 rounded-full flex items-center justify-center mb-4 text-gray-600 text-2xl">
            <i class="fas fa-inbox"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-400">All caught up!</h3>
        <p class="text-gray-500">No pending applications at the moment.</p>
    </div>
<?php endif; ?>

<!-- MODAL OVERLAY -->
<div id="appModal" class="fixed inset-0 z-50 hidden bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-dark-800 border border-gray-700 w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col transform transition-all scale-95 opacity-0" id="modalContent">
        
        <!-- Modal Header -->
        <div class="bg-dark-900 p-6 border-b border-gray-700 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-white" id="m_fullname">Applicant Name</h3>
                <p class="text-sm text-gray-400" id="m_email">email@example.com</p>
            </div>
            <button onclick="closeModal()" class="w-8 h-8 rounded-full bg-gray-800 hover:bg-gray-700 flex items-center justify-center text-gray-400 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-8 overflow-y-auto space-y-8">
            
            <!-- Grid Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-dark-900/50 p-4 rounded-xl border border-gray-700/50">
                    <label class="block text-xs uppercase tracking-wider text-gray-500 font-bold mb-1">Discord Info</label>
                    <div class="text-white font-medium flex items-center gap-2">
                        <i class="fab fa-discord text-indigo-500"></i> <span id="m_discord">Username</span>
                    </div>
                    <div class="text-xs text-gray-500 font-mono mt-1">ID: <span id="m_discord_id">123456</span></div>
                </div>
                
                <div class="bg-dark-900/50 p-4 rounded-xl border border-gray-700/50">
                    <label class="block text-xs uppercase tracking-wider text-gray-500 font-bold mb-1">Personal</label>
                    <div class="text-white font-medium">DOB: <span id="m_dob">2000-01-01</span></div>
                    <div class="text-sm text-gray-400 mt-1">Timezone: <span id="m_timezone">UTC</span></div>
                </div>
            </div>

            <!-- Long Text Sections -->
            <div>
                <label class="block text-sm font-bold text-brand-500 mb-2 uppercase tracking-wide">RP Experience</label>
                <p class="text-gray-300 leading-relaxed bg-dark-900 p-4 rounded-lg border border-gray-800 text-sm" id="m_rp_exp">
                    Loading...
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-brand-500 mb-2 uppercase tracking-wide">RP Explanation</label>
                    <div class="bg-dark-900 p-4 rounded-lg border border-gray-800 text-gray-300 text-sm h-40 overflow-y-auto" id="m_rp_ans">
                        Loading...
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-brand-500 mb-2 uppercase tracking-wide">NLR Explanation</label>
                    <div class="bg-dark-900 p-4 rounded-lg border border-gray-800 text-gray-300 text-sm h-40 overflow-y-auto" id="m_nlr_ans">
                        Loading...
                    </div>
                </div>
            </div>

            <!-- Technical -->
            <div class="bg-gray-800/50 p-4 rounded-lg border border-gray-700/50">
                <label class="block text-xs uppercase tracking-wider text-gray-500 font-bold mb-2">MTA Serial</label>
                <code class="block bg-black/30 p-3 rounded text-green-400 font-mono text-sm break-all select-all" id="m_serial">
                    Serial
                </code>
            </div>

            <!-- Rules Check -->
            <div class="flex gap-4">
                <span class="px-3 py-1 bg-green-500/10 text-green-400 text-xs rounded-full border border-green-500/20" id="m_rules">Read Rules: Yes</span>
                <span class="px-3 py-1 bg-green-500/10 text-green-400 text-xs rounded-full border border-green-500/20" id="m_agree">Agreed: Yes</span>
            </div>

        </div>
    </div>
</div>

<script>
function openModal(data) {
    // Populate Data
    document.getElementById('m_fullname').innerText = data.fullName;
    document.getElementById('m_email').innerText = data.email;
    document.getElementById('m_dob').innerText = data.dob;
    document.getElementById('m_timezone').innerText = data.timezone || 'N/A';
    document.getElementById('m_discord').innerText = data.discordUsername;
    document.getElementById('m_discord_id').innerText = data.discordId;
    document.getElementById('m_serial').innerText = data.serial;
    
    document.getElementById('m_rp_exp').innerText = data.rpExperience; // Assuming this is Yes/No or short text
    document.getElementById('m_rp_ans').innerText = data.rpExplanation;
    document.getElementById('m_nlr_ans').innerText = data.nlrExplanation;
    
    document.getElementById('m_rules').innerText = "Read Rules: " + data.readRules;
    document.getElementById('m_agree').innerText = "Agreed: " + data.agreeRules;

    // Show Modal
    const modal = document.getElementById('appModal');
    const content = document.getElementById('modalContent');
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeModal() {
    const modal = document.getElementById('appModal');
    const content = document.getElementById('modalContent');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Close on outside click
document.getElementById('appModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php siteFooter(); ?>