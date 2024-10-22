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

// Fetch user profile
$sql = "SELECT * FROM user_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        header {
            text-align: center;
            margin-bottom: 20px;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            padding: 10px;
            color: #fff;
            border-radius: 5px;
        }
        .navbar ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }
        .navbar li {
            display: inline;
            margin-right: 15px;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
        }
        .navbar .switch {
            display: flex;
            align-items: center;
        }
        .switch input {
            display: none;
        }
        .switch .slider {
            width: 34px;
            height: 20px;
            background-color: #ccc;
            border-radius: 50px;
            position: relative;
            cursor: pointer;
        }
        .switch .slider:before {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background-color: #fff;
            top: 2px;
            left: 2px;
            transition: .3s;
        }
        .switch input:checked + .slider {
            background-color: #2196F3;
        }
        .switch input:checked + .slider:before {
            transform: translateX(14px);
        }
        .profile-picture {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-picture img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #ddd;
        }
        .profile-details {
            margin-top: 20px;
        }
        .profile-details label {
            display: block;
            margin: 10px 0 5px;
        }
        .profile-details input, .profile-details textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .profile-details textarea {
            height: 100px;
        }
        .profile-details input[type="submit"] {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border: none;
            padding: 10px 20px;
            font-size: 1em;
        }
        .profile-details input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="dark-mode">
    <div class="container">
        <header>
            <nav class="navbar">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="generate.php">Generate Images</a></li>
                    <li><a href="generate_text.php">Generate Text</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php" class="button">Logout</a></li>
                </ul>
                <label class="switch">
                    <input type="checkbox" id="dark-mode-toggle">
                    <span class="slider round"></span>
                </label>
            </nav>
            <h1>User Dashboard</h1>
        </header>

        <main>
            <div class="profile-picture">
                <a href="profile.php">
                    <img src="<?php echo isset($profile['profile_picture']) && $profile['profile_picture'] ? 'uploads/' . htmlspecialchars($profile['profile_picture']) : 'https://w7.pngwing.com/pngs/81/570/png-transparent-profile-logo-computer-icons-user-user-blue-heroes-logo-thumbnail.png'; ?>" alt="Profile Picture">
                </a>
            </div>

            <div class="profile-details">
                <form action="update_profile.php" method="post" enctype="multipart/form-data">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo isset($profile['first_name']) ? htmlspecialchars($profile['first_name']) : ''; ?>" required>

                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo isset($profile['last_name']) ? htmlspecialchars($profile['last_name']) : ''; ?>" required>

                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" value="<?php echo isset($profile['email']) ? htmlspecialchars($profile['email']) : ''; ?>" required>

                    <label for="profile_picture">Profile Picture:</label>
                    <input type="file" name="profile_picture" id="profile_picture">

                    <label for="bio">Bio:</label>
                    <textarea name="bio" id="bio"><?php echo isset($profile['bio']) ? htmlspecialchars($profile['bio']) : ''; ?></textarea>

                    <input type="submit" value="Update Profile">
                </form>
            </div>
        </main>
    </div>
    <script src="scripts/script.js"></script>
</body>
</html>
