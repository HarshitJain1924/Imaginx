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
$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$bio = $_POST['bio'];

// Handle file upload
$profile_picture = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
    $profile_picture = $_FILES['profile_picture']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
    move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
}

// Update user profile
$sql = "UPDATE user_profiles SET first_name = ?, last_name = ?, email = ?, profile_picture = ?, bio = ? WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $first_name, $last_name, $email, $profile_picture, $bio, $_SESSION['user_id']);
$stmt->execute();

$stmt->close();
$conn->close();

// Redirect to profile page
header("Location: profile.php");
exit();
?>
