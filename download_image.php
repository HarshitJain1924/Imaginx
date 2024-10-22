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

    // Validate URL
    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        // Initialize cURL session
        $ch = curl_init($imageUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in the output
        curl_setopt($ch, CURLOPT_NOBODY, false); // Include body in the output

        // Execute cURL request
        $response = curl_exec($ch);

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
                $fileExtension = ''; // Fallback if content type is unknown
                break;
        }

        // Set the file name
        $fileName = 'downloaded_image' . $fileExtension;

        // Check if the image data is retrieved
        if ($body !== false && !empty($fileExtension)) {
            // Set headers to initiate file download
            header('Content-Description: File Transfer');
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . strlen($body));

            // Output the image data
            echo $body;
            exit;
        } else {
            echo "Failed to download image or unsupported format.";
        }

        // Close cURL session
        curl_close($ch);
    } else {
        echo "Invalid image URL.";
    }
} else {
    echo "No image URL provided.";
}
