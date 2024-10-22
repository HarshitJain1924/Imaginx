<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Load the environment variables
require_once __DIR__ . '/env.php'; // Ensure you're loading the env variables

// Define your Hugging Face API key from the environment variables
$apiKey = getenv('HUGGING_FACE_API_KEY'); // Use the API key from .env

// Get the topic from the form
$topic = htmlspecialchars(trim($_POST['topic']));

// Hugging Face API endpoint and model
$huggingFaceApiUrl = getenv('HUGGING_FACE_API_URL'); // Use the appropriate model

// Data for the API request
$data = array(
    'inputs' => "Write a detailed article about " . $topic,
    'parameters' => array(
        'max_length' => 150, // Adjust as needed
        'temperature' => 0.7
    )
);

$options = array(
    'http' => array(
        'header'  => "Content-Type: application/json\r\n" .
                     "Authorization: Bearer " . $apiKey . "\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    )
);

$context  = stream_context_create($options);
$response = file_get_contents($huggingFaceApiUrl, false, $context);

if ($response === FALSE) {
    die('Error contacting Hugging Face API');
}

$responseData = json_decode($response, true);
$generatedText = isset($responseData[0]['generated_text']) ? $responseData[0]['generated_text'] : 'No content generated.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generated Text</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body class="dark-mode">
    <div class="container">
        <header>
            <h1>Generated Text for "<?php echo htmlspecialchars($topic); ?>"</h1>
        </header>
        <main>
            <section class="results">
                <p><?php echo nl2br(htmlspecialchars($generatedText)); ?></p>
                <a href="index.php" class="button">Generate More</a>
            </section>
        </main>
    </div>
</body>
</html>
