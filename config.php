<?php
// Start Session for Authentication
session_start();

// Database Configuration
$host = 'serverdb.oceannodes.cloud';
$dbname = 's4_core';
$username = 'u4_jSc996MNOy';
$password = 'W2UxS21WX2aAI!3M2u+.!ui1';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Helper function to sanitize output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Authentication Check Function
function requireAuth() {
    // Check if the user is authenticated
    if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
        // Redirect to login page if not authenticated
        header('Location: login.php');
        exit;
    }
}

// Get Logged-in User Data
function getCurrentUser() {
    global $pdo;
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT id, username, email FROM accounts WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}
?>