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

// Fetch image URLs to delete files from server if necessary
$sqlSelect = "SELECT image_url FROM images WHERE user_id = ?";
$stmtSelect = $conn->prepare($sqlSelect);
$stmtSelect->bind_param("i", $_SESSION['user_id']);
$stmtSelect->execute();
$result = $stmtSelect->get_result();
$images = $result->fetch_all(MYSQLI_ASSOC);

// Delete images from the database
$sqlDelete = "DELETE FROM images WHERE user_id = ?";
$stmtDelete = $conn->prepare($sqlDelete);
$stmtDelete->bind_param("i", $_SESSION['user_id']);
$stmtDelete->execute();
$stmtDelete->close();

// Optionally, delete image files from the server (if they are stored locally)
foreach ($images as $image) {
    $imageUrl = $image['image_url'];

    // Only delete files from your server, not external URLs
    if (file_exists($imageUrl)) {
        unlink($imageUrl); // Delete the file from the server
    }
}

$stmtSelect->close();
$conn->close();

// Redirect back to the image page or show a success message
header("Location: index.php?message=All images deleted successfully");
exit();
?>
