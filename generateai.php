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

// Define your Hugging Face API key
$apiKey= getenv('HUGGING_FACE_API_KEY');// Replace with your actual Hugging Face API key

// Default prompt
$prompt = '';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prompt = htmlspecialchars(trim($_POST['prompt']));
    
    // Call the Hugging Face API and generate the image
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api-inference.huggingface.co/models/stabilityai/stable-diffusion-xl-base-1.0");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ));
    $data = json_encode(array('inputs' => $prompt));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error: ' . curl_error($ch);
    } else {
        // Handle the image URL or response
        $imageUrl = "generated_image_url"; // Assuming the URL is returned or saved somehow
    }

    curl_close($ch);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate AI Image</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body class="dark-mode">
    <div class="container">
        <header>
            <h1>Generate AI Image</h1>
        </header>
        <main>
            <form id="image-form" method="POST">
                <label for="prompt">Enter Prompt:</label>
                <input type="text" id="prompt" name="prompt" value="<?php echo htmlspecialchars($prompt); ?>" required>
                <button type="submit" class="generate-btn" style="background-color: rgba(255, 217, 1, 0.877);">Generate Image</button>
                <!-- Download link initially hidden -->
                <a id="download-link" class="download-btn" style="display:none;" download="generated_image.png">Download Image</a>
            </form>
            <div id="image-container">
                <!-- Image will be displayed here -->
            </div>
        </main>
    </div>

    <script>
        async function query(data) {
            const response = await fetch(
                "https://api-inference.huggingface.co/models/stabilityai/stable-diffusion-xl-base-1.0",
                {
                    headers: {
                        Authorization: "Bearer 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",
                        "Content-Type": "application/json",
                    },
                    method: "POST",
                    body: JSON.stringify(data),
                }
            );

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const result = await response.blob();
            return URL.createObjectURL(result);
        }

        document.getElementById('image-form').addEventListener('submit', async function(event) {
            event.preventDefault();

            const prompt = document.getElementById('prompt').value;
            try {
                const imageUrl = await query({ "inputs": prompt });
                const img = document.createElement('img');
                img.src = imageUrl;
                
                // Create and configure download link
                const downloadLink = document.getElementById('download-link');
                downloadLink.href = imageUrl;
                downloadLink.download = `${prompt}.png`; // Filename for download
                downloadLink.style.display = 'inline'; // Show download link
                
                // Clear the image container and append image and download link
                const container = document.getElementById('image-container');
                container.innerHTML = ''; 
                container.appendChild(img);
                
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
