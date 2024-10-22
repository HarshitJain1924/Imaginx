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

// Define your Unsplash API key
$apiKey = getenv('UNSPLASH_API_KEY'); // Replace with your actual Unsplash API key

// Get the topic from the form
$topic = htmlspecialchars(trim($_POST['topic']));

// Unsplash API endpoint
$unsplashApiUrl = 'https://api.unsplash.com/search/photos?query=' . urlencode($topic) . '&client_id=' . $apiKey;

// Make API request
$response = file_get_contents($unsplashApiUrl);

if ($response === FALSE) {
    die('Error contacting Unsplash API');
}

$responseData = json_decode($response, true);
$images = isset($responseData['results']) ? $responseData['results'] : [];

// Save images in database
if (count($images) > 0) {
    foreach ($images as $image) {
        $imageUrl = $image['urls']['regular'];
        $sql = "INSERT INTO images (user_id, image_url, topic) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $_SESSION['user_id'], $imageUrl, $topic);

        if (!$stmt->execute()) {
            echo 'Error saving image to database: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generated Images</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body class="dark-mode">
    <div class="container">
        <header>
            <h1>Generated Images for "<?php echo htmlspecialchars($topic); ?>"</h1>
        </header>
        <section class="results">
    <?php if (count($images) > 0): ?>
        <?php foreach ($images as $image): ?>
            <img src="<?php echo htmlspecialchars($image['urls']['regular']); ?>" alt="Image" class="generated-image">
        <?php endforeach; ?>
    <?php else: ?>
        <p>No images found.</p>
    <?php endif; ?>
    <a href="index.php" class="button">Generate More</a>
</section>
    </div>
</body>
</html>
