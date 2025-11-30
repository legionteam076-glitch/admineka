<?php
require_once 'config.php';
requireAuth(); // <-- Authentication Check
require_once 'layout.php';

$msg = '';

// --- FIRST TIME ADMIN SETUP ---
// Check if the accounts table is empty. If so, display the create form.
try {
    $userCountStmt = $pdo->query("SELECT COUNT(*) AS count FROM accounts");
    $userCount = $userCountStmt->fetchColumn();
    $showSetupForm = $userCount == 0;
} catch (PDOException $e) {
    // Table might be missing or other error. Assume no users and allow setup.
    $showSetupForm = true;
    $msg = "<div class='bg-red-500/10 text-red-400 border border-red-500/20 p-4 rounded-lg mb-6 flex items-center gap-2'><i class='fas fa-exclamation-triangle'></i> Warning: Could not count users. Assuming first-time setup.</div>";
}

// Handle First Time Admin Creation
if ($showSetupForm && isset($_POST['first_admin_setup'])) {
    $uName = trim($_POST['username']);
    $uEmail = trim($_POST['email']);
    $uPass = $_POST['password'];
    
    if (!empty($uName) && !empty($uPass)) {
        $hashedPass = password_hash($uPass, PASSWORD_DEFAULT);
        
        try {
            // NOTE: This insert assumes your 'accounts' table has (id, username, email, password) columns.
            $stmt = $pdo->prepare("INSERT INTO accounts (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$uName, $uEmail, $hashedPass]);
            $msg = "<div class='bg-green-500/10 text-green-400 border border-green-500/20 p-4 rounded-lg mb-6 flex items-center gap-2'><i class='fas fa-check'></i> First admin account created successfully! Please log in now.</div>";
            $showSetupForm = false; // Hide setup after creation
            header("Location: usermanage.php"); // Refresh to apply login
            exit;
        } catch (PDOException $e) {
            $msg = "<div class='bg-red-500/10 text-red-400 border border-red-500/20 p-4 rounded-lg mb-6'>Error creating first admin: " . $e->getMessage() . "</div>";
        }
    } else {
        $msg = "<div class='bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 p-4 rounded-lg mb-6'>Username and Password cannot be empty.</div>";
    }
}


// Handle Delete
if (isset($_POST['delete_id'])) {
    $delId = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM accounts WHERE id = ?");
    $stmt->execute([$delId]);
    $msg = "<div class='bg-red-500/10 text-red-400 border border-red-500/20 p-4 rounded-lg mb-6 flex items-center gap-2'><i class='fas fa-trash'></i> Account deleted.</div>";
}

// Handle Update/Creation
if (isset($_POST['update_user']) || isset($_POST['create_user'])) {
    $uid = $_POST['user_id'] ?? null;
    $uName = trim($_POST['username']);
    $uEmail = trim($_POST['email']);
    $uPass = $_POST['password'];
    $uPassConfirm = $_POST['password_confirm'];

    if (!empty($uPass) && $uPass !== $uPassConfirm) {
        $msg = "<div class='bg-red-500/10 text-red-400 border border-red-500/20 p-4 rounded-lg mb-6 flex items-center gap-2'><i class='fas fa-times-circle'></i> Passwords do not match.</div>";
    } else {
        $hashedPass = !empty($uPass) ? password_hash($uPass, PASSWORD_DEFAULT) : null;

        try {
            if (isset($_POST['create_user'])) {
                if (empty($uPass)) {
                     $msg = "<div class='bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 p-4 rounded-lg mb-6'>New users must have a password set.</div>";
                } else {
                    $stmt = $pdo->prepare("INSERT INTO accounts (username, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$uName, $uEmail, $hashedPass]);
                    $msg = "<div class='bg-green-500/10 text-green-400 border border-green-500/20 p-4 rounded-lg mb-6 flex items-center gap-2'><i class='fas fa-check'></i> New admin created.</div>";
                }
            } elseif (isset($_POST['update_user'])) {
                if ($hashedPass) {
                    $stmt = $pdo->prepare("UPDATE accounts SET username = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->execute([$uName, $uEmail, $hashedPass, $uid]);
                } else {
                    $stmt = $pdo->prepare("UPDATE accounts SET username = ?, email = ? WHERE id = ?");
                    $stmt->execute([$uName, $uEmail, $uid]);
                }
                $msg = "<div class='bg-green-500/10 text-green-400 border border-green-500/20 p-4 rounded-lg mb-6 flex items-center gap-2'><i class='fas fa-check'></i> Account updated.</div>";
            }
        } catch (PDOException $e) {
            $msg = "<div class='bg-red-500/10 text-red-400 border border-red-500/20 p-4 rounded-lg mb-6'>Error processing user: " . $e->getMessage() . "</div>";
        }
    }
}

// Fetch all users after operations
try {
    $stmt = $pdo->query("SELECT id, username, email FROM accounts ORDER BY id DESC"); 
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $error = "Table 'accounts' missing or schema invalid. Ensure it has (id, username, email, password).";
}

siteHeader("User Management");

// Check Edit Mode
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT id, username, email FROM accounts WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch();
}

// Check Create Mode
$createMode = isset($_GET['create']);
?>

<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-3xl font-bold text-white">User Management</h2>
        <p class="text-gray-400">Manage registered administrator accounts</p>
    </div>
    <?php if (!$showSetupForm): ?>
        <?php if (!$createMode): ?>
            <a href="?create=true" class="bg-brand-600 hover:bg-brand-500 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-lg shadow-brand-600/30 transform hover:scale-[1.01] active:scale-[0.99] duration-150 flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Create New Admin
            </a>
        <?php else: ?>
            <a href="usermanage.php" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg font-medium transition duration-150 flex items-center gap-2">
                <i class="fas fa-times"></i> Cancel Creation
            </a>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?= $msg ?>
<?php if (isset($error)) echo "<div class='bg-red-500/10 text-red-400 p-4 rounded-lg mb-4'>$error</div>"; ?>

<!-- First Time Setup Form -->
<?php if ($showSetupForm && $userCount == 0): ?>
<div class="bg-red-800/20 border border-red-600/50 rounded-xl p-8 mb-8 shadow-xl">
    <h3 class="text-2xl font-bold text-red-400 mb-4 flex items-center gap-3"><i class="fas fa-exclamation-circle"></i> Initial Setup Required</h3>
    <p class="text-red-300 mb-6">The administrator accounts table is empty. Please create the first super admin account below to secure your panel access.</p>
    <form method="POST">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-red-400 mb-2">Username</label>
                <input type="text" name="username" required class="w-full bg-dark-900 border border-red-700 rounded-lg p-3 text-white focus:border-red-500 focus:ring-2 focus:ring-red-300 outline-none transition duration-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-red-400 mb-2">Email (Optional)</label>
                <input type="email" name="email" class="w-full bg-dark-900 border border-red-700 rounded-lg p-3 text-white focus:border-red-500 focus:ring-2 focus:ring-red-300 outline-none transition duration-300">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-red-400 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-dark-900 border border-red-700 rounded-lg p-3 text-white focus:border-red-500 focus:ring-2 focus:ring-red-300 outline-none transition duration-300">
            </div>
        </div>
        <button type="submit" name="first_admin_setup" class="bg-red-600 hover:bg-red-500 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-lg shadow-red-900/30 transform hover:scale-[1.01] active:scale-[0.99] duration-150">Create First Admin</button>
    </form>
</div>
<?php endif; ?>


<!-- Edit/Create Form -->
<?php if ($editUser || $createMode): ?>
<div class="bg-dark-800 border border-brand-500/30 rounded-xl p-6 mb-8 shadow-xl shadow-brand-900/10 relative overflow-hidden">
    <div class="absolute top-0 right-0 p-4 opacity-10"><i class="fas fa-<?= $createMode ? 'user-plus' : 'edit' ?> text-9xl text-brand-500"></i></div>
    <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2"><i class="fas fa-<?= $createMode ? 'user-plus' : 'user-edit' ?> text-brand-500"></i> <?= $createMode ? 'Create New Admin' : 'Edit User: ' . e($editUser['username']) ?></h3>
    <form method="POST" action="usermanage.php" class="relative z-10">
        <?php if ($editUser): ?>
            <input type="hidden" name="user_id" value="<?= $editUser['id'] ?>">
        <?php endif; ?>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Username</label>
                <input type="text" name="username" value="<?= e($editUser['username'] ?? '') ?>" required class="w-full bg-dark-900 border border-gray-700 rounded-lg p-3 text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-300 outline-none transition duration-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Email</label>
                <input type="email" name="email" value="<?= e($editUser['email'] ?? '') ?>" class="w-full bg-dark-900 border border-gray-700 rounded-lg p-3 text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-300 outline-none transition duration-300">
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border-t border-gray-700/50 pt-6">
             <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">New Password <?= $editUser ? '(Leave blank to keep current)' : '' ?></label>
                <input type="password" name="password" <?= $createMode ? 'required' : '' ?> class="w-full bg-dark-900 border border-gray-700 rounded-lg p-3 text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-300 outline-none transition duration-300">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Confirm Password</label>
                <input type="password" name="password_confirm" <?= $createMode ? 'required' : '' ?> class="w-full bg-dark-900 border border-gray-700 rounded-lg p-3 text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-300 outline-none transition duration-300">
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" name="<?= $createMode ? 'create_user' : 'update_user' ?>" class="bg-brand-600 hover:bg-brand-500 text-white px-6 py-2.5 rounded-lg font-medium transition shadow-lg shadow-brand-600/30 transform hover:scale-[1.01] active:scale-[0.99] duration-150">
                <i class="fas fa-save mr-2"></i> <?= $createMode ? 'Create Account' : 'Save Changes' ?>
            </button>
            <a href="usermanage.php" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2.5 rounded-lg font-medium transition duration-150">
                <i class="fas fa-ban mr-2"></i> Cancel
            </a>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Users Table -->
<div class="bg-dark-800 border border-gray-700 rounded-xl overflow-hidden shadow-xl mt-8">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-dark-900 text-gray-400 font-medium uppercase text-xs border-b border-gray-700/50">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Username</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-dark-700/50 transition duration-150">
                        <td class="px-6 py-4 text-gray-500 font-mono">#<?= e($user['id']) ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <!-- User initial avatar with brand colors -->
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-brand-600 to-brand-500 flex items-center justify-center text-xs font-bold text-white shadow-md shadow-brand-900/50">
                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                </div>
                                <span class="text-white font-medium"><?= e($user['username']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-400"><?= e($user['email'] ?? 'N/A') ?></td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="?edit=<?= $user['id'] ?>" class="w-8 h-8 flex items-center justify-center rounded bg-yellow-500/10 text-yellow-300 hover:bg-yellow-500 hover:text-white transition border border-yellow-500/20 hover:shadow-lg hover:shadow-yellow-900/30">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                
                                <form method="POST" onsubmit="return confirm('Delete this account?');">
                                    <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded bg-red-500/10 text-red-400 hover:bg-red-600 hover:text-white transition border border-red-500/20 hover:shadow-lg hover:shadow-red-900/30">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500 italic">No admin users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php siteFooter(); ?>