<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body class="dark-mode">
    <div class="container">
        <header>
            <h1>Account Settings</h1>
        </header>

        <main>
            <form action="update_settings.php" method="post">
                <label for="current_password">Current Password:</label>
                <input type="password" name="current_password" id="current_password" required>

                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required>

                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>

                <input type="submit" value="Update Settings">
            </form>
        </main>
    </div>
    <script src="scripts/script.js"></script>
</body>
</html>
