<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure image URL is passed as a query parameter
if (isset($_GET['image'])) {
    $imageUrl = $_GET['image'];

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

        // Prepare SQL statement to delete the image from the database
        $sql = "DELETE FROM images WHERE user_id = ? AND image_url = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $_SESSION['user_id'], $imageUrl);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Delete image file from the server
            $imagePath = __DIR__ . '/uploads/' . basename($imageUrl); // Adjust the path as needed
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            echo "Image deleted successfully.";
        } else {
            echo "Failed to delete image.";
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Invalid image URL.";
    }
} else {
    echo "No image URL provided.";
}

header("Location: index.php"); 
exit();
