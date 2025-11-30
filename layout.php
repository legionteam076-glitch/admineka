<?php
// Get the current user for dynamic display
$currentUser = getCurrentUser();

function siteHeader($title) {
    global $currentUser;
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Admin Panel</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        dark: {
                            900: '#0f172a',
                            800: '#1e293b',
                            700: '#334155',
                            card: '#1e293b', 
                        },
                        brand: {
                            500: '#6366f1', // Indigo
                            600: '#4f46e5',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
        
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="bg-dark-900 text-gray-300 font-sans antialiased selection:bg-brand-500 selection:text-white">
    <div class="min-h-screen flex flex-col md:flex-row">
<aside class="w-full md:w-72 bg-dark-900 border-r border-gray-800 flex-shrink-0 flex flex-col relative z-20">
    <div class="h-20 flex items-center px-8 border-b border-gray-800">
        <div class="flex items-center gap-3">

            <!-- Logo Image -->
            <div class="w-10 h-10 rounded overflow-hidden">
                <img src="logo.png" alt="Logo" class="w-full h-full object-cover">
            </div>

            <span class="text-xl font-bold text-white tracking-wide">Arp Whitelist Admin</span>
        </div>
    </div>


            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Main Menu</p>
                
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                    <i class="fas fa-layer-group w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Applications</span>
                </a>

                <a href="addwhitelist.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?= basename($_SERVER['PHP_SELF']) == 'addwhitelist.php' ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                    <i class="fas fa-terminal w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">Add Manual</span>
                </a>

                <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-8 mb-2">System</p>

                <a href="usermanage.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group <?= basename($_SERVER['PHP_SELF']) == 'usermanage.php' ? 'bg-brand-600 text-white shadow-lg shadow-brand-500/30' : 'text-gray-400 hover:bg-gray-800 hover:text-white' ?>">
                    <i class="fas fa-users-cog w-5 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium">User Manager</span>
                </a>
            </nav>

            <div class="p-4 border-t border-gray-800">
                <?php if ($currentUser): ?>
                    <div class="flex items-center gap-3 px-4 py-2 mb-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-xs text-white font-bold">
                            <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-white"><?= e($currentUser['username']) ?></span>
                            <span class="text-xs text-gray-500">Admin ID: #<?= $currentUser['id'] ?></span>
                        </div>
                    </div>
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-gray-400 hover:bg-red-800/30 hover:text-red-400 border border-transparent hover:border-red-800/50">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i>
                        <span class="font-medium">Logout</span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-gray-400 hover:bg-brand-800/30 hover:text-brand-400 border border-transparent hover:border-brand-800/50">
                        <i class="fas fa-sign-in-alt w-5 text-center"></i>
                        <span class="font-medium">Login</span>
                    </a>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 relative overflow-y-auto h-screen bg-gradient-to-br from-dark-900 to-gray-900">
            <!-- Top Header Mobile -->
            <div class="md:hidden h-16 bg-dark-800 border-b border-gray-700 flex items-center justify-between px-4">
                <span class="font-bold text-white">MTA Admin</span>
                <button class="text-gray-400"><i class="fas fa-bars"></i></button>
            </div>

            <div class="p-6 md:p-12 max-w-7xl mx-auto">
<?php
}

function siteFooter() {
?>
            </div>
        </main>
    </div>
</body>
</html>
<?php
}
?>