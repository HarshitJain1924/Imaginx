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

// Fetch image URLs from database
$sql = "SELECT image_url FROM images WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$images = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

// Create a new ZIP file
$zip = new ZipArchive();
$zipFileName = 'all_images.zip';
$zipFilePath = __DIR__ . '/' . $zipFileName;

if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
    die("Cannot open <$zipFileName>\n");
}

// Add images to ZIP
foreach ($images as $image) {
    $imageUrl = $image['image_url'];

    // Initialize cURL session
    $ch = curl_init($imageUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);

    // Execute cURL request
    $response = curl_exec($ch);

    if ($response === false) {
        error_log("cURL error: " . curl_error($ch));
        continue;
    }

    // Get the header size
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

    // Extract headers and body from response
    $headers = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);

    // Extract Content-Type from headers
    $contentType = '';
    if (preg_match('/Content-Type: ([^\s]+)/i', $headers, $matches)) {
        $contentType = trim($matches[1]);
    }

    // Determine file extension based on content type
    $fileExtension = '';
    switch ($contentType) {
        case 'image/jpeg':
            $fileExtension = '.jpg';
            break;
        case 'image/png':
            $fileExtension = '.png';
            break;
        case 'image/gif':
            $fileExtension = '.gif';
            break;
        default:
            $fileExtension = '';
            break;
    }

    // Generate a unique file name
    $fileName = uniqid('image_') . $fileExtension;

    // Add file to ZIP if valid
    if ($body !== false && !empty($fileExtension)) {
        if (!$zip->addFromString($fileName, $body)) {
            error_log("Failed to add $fileName to ZIP file.");
        }
    } else {
        error_log("Invalid image data or unsupported format for URL: $imageUrl");
    }

    // Close cURL session
    curl_close($ch);
}

$zip->close();

// Check if the ZIP file exists before attempting to send it
if (!file_exists($zipFilePath)) {
    error_log("ZIP file does not exist at $zipFilePath.");
    die("ZIP file does not exist.");
}

// Send the ZIP file to the user
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
header('Content-Length: ' . filesize($zipFilePath));
readfile($zipFilePath);

// Delete the ZIP file from server
if (!unlink($zipFilePath)) {
    error_log("Failed to delete ZIP file: $zipFilePath");
}

exit;
