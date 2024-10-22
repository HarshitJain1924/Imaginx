<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Load the environment variables
require_once __DIR__ . '/env.php';

// Database connection using environment variables
$servername = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname = getenv('DB_NAME');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input data
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

// Check if passwords match
if ($new_password !== $confirm_password) {
    echo "New passwords do not match.";
    exit();
}

// Fetch current password hash from database
$sql = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!password_verify($current_password, $user['password'])) {
    echo "Current password is incorrect.";
    exit();
}

// Update password
$new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
$sql = "UPDATE users SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_password_hash, $_SESSION['user_id']);
$stmt->execute();

$stmt->close();
$conn->close();

// Redirect to profile page
header("Location: profile.php");
exit();
?>
