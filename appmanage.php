<?php
require_once 'config.php';
require_once 'layout.php';

$current_page = 'appmanage';
$content = '';
$msg = '';
$msgType = '';

// --- Application Detail View Function ---
function display_application_detail($pdo, $app_id) {
    global $msg, $msgType;
    
    try {
        // Fetch all individual columns from the application, including the new 'username'
        $stmt = $pdo->prepare("SELECT 
            id, email, full_name, username, dob, timezone, facebook_name, discord_username, 
            discord_id, roleplay_explanation, new_life_rules, rp_experience, 
            read_rules, agree_rules, status, created_at
            FROM applications WHERE id = ?");
        $stmt->execute([$app_id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            $msg = "Error: Application not found.";
            $msgType = "error";
            return '';
        }

        ob_start();
        ?>
        <div class="bg-slate-800 p-8 rounded-xl border border-slate-700 shadow-2xl space-y-6">
            <h3 class="text-2xl font-bold text-blue-400">Application Details #<?php echo htmlspecialchars($application['id']); ?></h3>
            
            <!-- Status Update Form -->
            <div class="flex items-center justify-between p-4 bg-slate-700 rounded-lg">
                <p class="text-lg font-medium text-white">Current Status: 
                    <span class="<?php 
                        if ($application['status'] == 'Accepted') echo 'text-green-400';
                        else if ($application['status'] == 'Declined') echo 'text-red-400';
                        else echo 'text-yellow-400';
                    ?> font-extrabold"><?php echo htmlspecialchars($application['status']); ?></span>
                </p>
                <form method="POST" action="appmanage.php?action=update_status&id=<?php echo $app_id; ?>" class="flex space-x-3">
                    <select name="new_status" class="bg-slate-900 border border-slate-600 rounded-lg px-3 py-2 text-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="Pending" <?php echo $application['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Accepted" <?php echo $application['status'] == 'Accepted' ? 'selected' : ''; ?>>Accept</option>
                        <option value="Declined" <?php echo $application['status'] == 'Declined' ? 'selected' : ''; ?>>Decline</option>
                    </select>
                    <button type="submit" name="update_status" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">Update</button>
                </form>
            </div>

            <!-- Application Data Display (Using the new individual columns) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">Email</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['email']); ?></p>
                </div>
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">1.1 Full Name (Real)</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['full_name']); ?></p>
                </div>
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">1.2 In-Game Username</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['username']); ?></p>
                </div>
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">1.3 Date of Birth</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['dob']); ?></p>
                </div>
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">1.4 Country/Time Zone</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['timezone']); ?></p>
                </div>
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">1.5 Facebook Name</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['facebook_name']); ?></p>
                </div>
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">1.6 Discord Username</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['discord_username']); ?></p>
                </div>
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">1.7 Discord ID</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['discord_id']); ?></p>
                </div>
                 <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">2.0 Previous RP Experience?</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['rp_experience']); ?></p>
                </div>
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">2.1 Read Rules?</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['read_rules']); ?></p>
                </div>
                <div class="detail-item p-3 bg-slate-700 rounded-lg">
                    <p class="text-xs text-gray-400">2.2 Agree to Rules?</p>
                    <p class="text-white font-medium"><?php echo htmlspecialchars($application['agree_rules']); ?></p>
                </div>
            </div>

            <div class="form-group">
                <label class="block text-gray-300 mb-1 font-bold">1.8 Explain what is Roleplay in your own words?</label>
                <div class="p-4 bg-slate-700 rounded-lg text-gray-200 whitespace-pre-wrap"><?php echo htmlspecialchars($application['roleplay_explanation']); ?></div>
            </div>
            
            <div class="form-group">
                <label class="block text-gray-300 mb-1 font-bold">1.9 What are New life rules? (Explain one situation)</label>
                <div class="p-4 bg-slate-700 rounded-lg text-gray-200 whitespace-pre-wrap"><?php echo htmlspecialchars($application['new_life_rules']); ?></div>
            </div>

            <div class="text-xs text-gray-500 pt-4 border-t border-slate-700">
                Submitted on: <?php echo htmlspecialchars($application['created_at']); ?>
            </div>
        </div>

        <div class="mt-6">
             <a href="appmanage.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to List
            </a>
        </div>
        <?php
        return ob_get_clean();
    } catch (PDOException $e) {
        error_log("Detail view failed: " . $e->getMessage());
        $msg = "A database error occurred while fetching details.";
        $msgType = "error";
        return '';
    }
}

// --- Application Listing Function (Default View) ---
function list_applications($pdo) {
    global $msg, $msgType;
    ob_start();
    
    try {
        // Line 60 (as mentioned in the original error)
        $query = $pdo->query('SELECT id, full_name, discord_username, created_at, status FROM applications ORDER BY created_at DESC');
        $applications = $query->fetchAll(PDO::FETCH_ASSOC);

        ?>
        <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 shadow-2xl">
            <h3 class="text-xl font-bold text-white mb-4">Pending Applications (<?php echo count($applications); ?>)</h3>
            
            <?php if (empty($applications)): ?>
                <p class="text-gray-400 p-4 border border-dashed border-slate-700 rounded-lg text-center">No applications found.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-700">
                        <thead class="bg-slate-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Applicant Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Discord</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">View</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            <?php foreach ($applications as $app): ?>
                                <tr class="hover:bg-slate-700/50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-300"><?php echo htmlspecialchars($app['id']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white"><?php echo htmlspecialchars($app['full_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-white"><?php echo htmlspecialchars($app['discord_username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400"><?php echo date('Y-m-d H:i', strtotime($app['created_at'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                                if ($app['status'] == 'Accepted') echo 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100';
                                                else if ($app['status'] == 'Declined') echo 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100';
                                                else echo 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100';
                                            ?>">
                                            <?php echo htmlspecialchars($app['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="appmanage.php?action=view&id=<?php echo htmlspecialchars($app['id']); ?>" class="text-blue-400 hover:text-blue-300 transition">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    } catch (PDOException $e) {
        error_log("Application listing failed: " . $e->getMessage());
        $msg = "A database error occurred while listing applications.";
        $msgType = "error";
    }

    return ob_get_clean();
}

// --- Handle Status Update Action ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && isset($_GET['action']) && $_GET['action'] == 'update_status') {
    $app_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $new_status = filter_input(INPUT_POST, 'new_status', FILTER_SANITIZE_STRING);

    if ($app_id && in_array($new_status, ['Pending', 'Accepted', 'Declined'])) {
        try {
            $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
            if ($stmt->execute([$new_status, $app_id])) {
                $msg = "Application #{$app_id} status updated to '{$new_status}' successfully.";
                $msgType = "success";
            } else {
                $msg = "Error updating status for application #{$app_id}.";
                $msgType = "error";
            }
        } catch (PDOException $e) {
            error_log("Status update failed: " . $e->getMessage());
            $msg = "Database error during status update.";
            $msgType = "error";
        }
    } else {
        $msg = "Invalid update request.";
        $msgType = "error";
    }
}

// --- Routing Logic ---
if (isset($_GET['action']) && $_GET['action'] == 'view' && isset($_GET['id'])) {
    $app_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($app_id) {
        $content = display_application_detail($pdo, $app_id);
    } else {
        $msg = "Invalid Application ID.";
        $msgType = "error";
        $content = list_applications($pdo);
    }
} else {
    // Default: Show list of applications
    $content = list_applications($pdo);
}

?>

<!-- Notification Message -->
<?php if ($msg): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $msgType == 'success' ? 'bg-green-500/20 text-green-300 border border-green-500/50' : 'bg-red-500/20 text-red-300 border border-red-500/50'; ?>">
        <?php echo $msg; ?>
    </div>
<?php endif; ?>

<?php
// Output the content using the layout
print_layout('Application Management', $content, $current_page);
?>