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

// Fetch distinct topics for filtering
$sql_topics = "SELECT DISTINCT topic FROM images WHERE user_id = ?";
$stmt_topics = $conn->prepare($sql_topics);
$stmt_topics->bind_param("i", $_SESSION['user_id']);
$stmt_topics->execute();
$result_topics = $stmt_topics->get_result();
$topics = $result_topics->fetch_all(MYSQLI_ASSOC);

$stmt_topics->close();

// Fetch saved images
$sql = "SELECT image_url, topic FROM images WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$savedImages = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Generator</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body class="dark-mode">
    <div class="container">
        <header>
            <a href="index.php" class="logo">
                <img src="assets/images/logo.png" alt="Logo">
            </a>
            <nav class="navbar">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="generateai.php">Generate AI Images</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php" class="button">Logout</a></li>
                </ul>
                <label class="switch">
                    <input type="checkbox" id="dark-mode-toggle">
                    <span class="slider round"></span>
                </label>
            </nav>
            <a href="profile.php" class="profile-link">
                <img src="assets/images/user.png" alt="Profile Picture" class="profile-pic">
                <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </a>
        </header>
        <div class="flex-box">
            <!-- Slideshow Section -->
            <section class="slideshow">
                <div class="slideshow-container">
                    <div class="slide fade">
                        <img src="assets/images/slide1.jpeg" alt="Slide 1">
                    </div>
                    <div class="slide fade">
                        <img src="assets/images/slide2.jpeg" alt="Slide 2">
                    </div>
                    <div class="slide fade">
                        <img src="assets/images/slide3.jpeg" alt="Slide 3">
                    </div>
                    <div class="slide fade">
                        <img src="assets/images/slide4.jpeg" alt="Slide 4">
                    </div>
                    <div class="slide fade">
                        <img src="assets/images/slide5.jpeg" alt="Slide 5">
                    </div>
                    <div class="slide fade">
                        <img src="assets/images/slide6.jpeg" alt="Slide 6">
                    </div>
                </div>
                <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
                <a class="next" onclick="plusSlides(1)">&#10095;</a>
            </section>

            <!-- Main Section -->
            <main>
                <form action="generate.php" method="post" class="bg1">
                    <label for="image-topic">Enter Topic for Images:</label>
                    <input type="text" name="topic" id="image-topic" required placeholder="e.g., nature, technology">
                    <input type="submit" value="Generate Images">
                </form>

                <form action="generate_text.php" method="post">
                    <label for="text-topic">Enter Topic for Text:</label>
                    <input type="text" name="topic" id="text-topic" required placeholder="e.g., space exploration, AI advancements">
                    <input type="submit" value="Generate Text">
                </form>

                <form action="generateai.php" method="post">
                    <label for="prompt">Enter Prompt for Image Generation:</label>
                    <input type="text" name="prompt" id="prompt" required placeholder="e.g., A futuristic city skyline at sunset">
                    <input type="submit" value="Generate Image">
                </form>
            </main>
        </div>

        <!-- Category Filter Section -->
        <section class="filter-category">
            <label for="category">Filter by Category:</label>
            <select id="category" onchange="filterImages()">
                <option value="all">All</option>
                <?php foreach ($topics as $topic): ?>
                    <option value="<?php echo htmlspecialchars($topic['topic']); ?>">
                        <?php echo htmlspecialchars($topic['topic']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </section>

        <!-- Saved Images Section -->
        <section class="saved-images">
            <h2>Previously Saved Images</h2>
            <div class="image-grid">
                <?php if (count($savedImages) > 0): ?>
                    <?php foreach ($savedImages as $image): ?>
                        <div class="image-item" data-category="<?php echo htmlspecialchars($image['topic']); ?>">
                            <img src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="Saved Image">
                            <p>Topic: <?php echo htmlspecialchars($image['topic']); ?></p>
                            <a href="download_image.php?image=<?php echo urlencode($image['image_url']); ?>" class="download-btn">Download</a>
                            <a href="delete_image.php?image=<?php echo urlencode($image['image_url']); ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this image?');">Delete</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No previously saved images.</p>
                <?php endif; ?>
            </div>
            <?php if (count($savedImages) > 0): ?>
                <a href="download_all.php" class="download-btn space">Download All Images</a>
            <?php endif; ?>
            <?php if (count($savedImages) > 0): ?>
                <a href="delete_all.php" class="delete-btn space">Delete All Images</a>
            <?php endif; ?>
        </section>
    </div>

    <script>
        function filterImages() {
            const selectedCategory = document.getElementById("category").value;
            const images = document.querySelectorAll(".image-item");

            images.forEach(image => {
                if (selectedCategory === "all" || image.dataset.category === selectedCategory) {
                    image.style.display = "block";
                } else {
                    image.style.display = "none";
                }
            });
        }

        let slideIndex = 0;
        showSlides();

        function showSlides() {
            let i;
            const slides = document.getElementsByClassName("slide");
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }
            slideIndex++;
            if (slideIndex > slides.length) {slideIndex = 1}    
            slides[slideIndex-1].style.display = "block";  
            setTimeout(showSlides, 3000); // Change slide every 3 seconds
        }

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }
    </script>
</body>
</html>
