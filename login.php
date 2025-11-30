<?php
require_once 'config.php';

// Check if the user is already logged in
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Check if accounts table has a 'password' column before attempting query
        // IMPORTANT: We assume the 'accounts' table has 'username' and 'password' (hashed) columns
        $stmt = $pdo->prepare("SELECT id, username, password FROM accounts WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Authentication successful
            $_SESSION['authenticated'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect to dashboard
            header('Location: index.php');
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MTA Panel</title>
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
                        },
                        brand: {
                            500: '#6366f1', // Indigo
                            600: '#4f46e5',
                        }
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: 0, transform: 'translateY(20px)' },
                            '100%': { opacity: 1, transform: 'translateY(0)' },
                        },
                        spinSlow: {
                            '0%': { transform: 'rotate(0deg)' },
                            '100%': { transform: 'rotate(360deg)' },
                        }
                    },
                    animation: {
                        fadeInUp: 'fadeInUp 0.8s ease-out',
                        spinSlow: 'spinSlow 60s linear infinite',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-dark-900 text-gray-300 font-sans antialiased flex items-center justify-center min-h-screen relative overflow-hidden">

    <!-- Animated Background Elements -->
    <div class="absolute inset-0 z-0 overflow-hidden">
        <div class="absolute w-60 h-60 bg-brand-500/20 rounded-full blur-3xl opacity-30 top-1/4 left-1/4 animate-spinSlow"></div>
        <div class="absolute w-96 h-96 bg-purple-600/20 rounded-full blur-3xl opacity-20 bottom-1/4 right-1/4 animate-spinSlow animation-delay-1000"></div>
    </div>

    <!-- Login Card -->
    <div class="relative z-10 w-full max-w-md p-8 sm:p-10 bg-dark-800/90 backdrop-blur-md rounded-2xl border border-gray-700 shadow-2xl shadow-black/50 animate-fadeInUp">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto rounded-full bg-gradient-to-br from-brand-500 to-purple-600 flex items-center justify-center text-white text-3xl mb-4 shadow-xl shadow-brand-900/40">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight">Admin Access</h1>
            <p class="text-gray-400 mt-1">Sign in to manage whitelist applications.</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-600/10 border border-red-600/30 text-red-300 p-4 rounded-lg mb-6 flex items-center gap-3">
                <i class="fas fa-exclamation-triangle"></i>
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="space-y-6">
                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-400 mb-2">Username</label>
                    <div class="relative">
                        <input type="text" id="username" name="username" required class="w-full bg-dark-900 border border-gray-700 rounded-xl p-4 pl-12 text-white placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/50 outline-none transition duration-200">
                        <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-400 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required class="w-full bg-dark-900 border border-gray-700 rounded-xl p-4 pl-12 text-white placeholder-gray-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/50 outline-none transition duration-200">
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-500 text-white font-semibold py-3 rounded-xl transition duration-150 transform hover:scale-[1.01] active:scale-[0.98] shadow-lg shadow-brand-900/40 flex items-center justify-center gap-2">
                    <i class="fas fa-sign-in-alt"></i>
                    Secure Login
                </button>
            </div>
        </form>

    </div>
</body>
</html>