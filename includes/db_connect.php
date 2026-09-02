<?php
/**
 * AutoPulse - Database Connection Configuration
 * Uses PHP Data Objects (PDO) with prepared statements for security against SQL injection.
 * Automatically tries MySQL default port 3306 and XAMPP alternate port 3307.
 */

$db_host = '127.0.0.1';
$db_name = 'autopulse_db';
$db_user = 'root';
$db_pass = ''; // Default XAMPP has no password

// Ports to attempt (3307 first since detected on this system, then standard 3306)
$ports_to_try = [3307, 3306];
$pdo = null;
$connection_error = '';

foreach ($ports_to_try as $port) {
    try {
        $dsn = "mysql:host={$db_host};port={$port};dbname={$db_name};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        break; // Connected successfully!
    } catch (PDOException $e) {
        $connection_error = $e->getMessage();
        continue;
    }
}

if (!$pdo) {
    // If running in development, show helpful message
    die("<div style='font-family: sans-serif; padding: 20px; background: #ffe6e6; border: 1px solid #d90000; border-radius: 6px; margin: 20px; color: #1a1a1a;'>
        <h2 style='color: #d90000; margin-top: 0;'>AutoPulse: Database Connection Error</h2>
        <p>Could not connect to MySQL server. Please ensure MySQL is started in your XAMPP Control Panel and database <code>autopulse_db</code> is imported from <code>database.sql</code>.</p>
        <p><small>Technical details: " . htmlspecialchars($connection_error) . "</small></p>
    </div>");
}

// Start session if not already started and headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
