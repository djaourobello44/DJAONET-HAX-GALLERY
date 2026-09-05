<?php

// ==========================================
// CONFIGURATION
// ==========================================

// Mets ton token Telegram ici
$BOT_TOKEN = 'TON_TOKEN_TELEGRAM_ICI';

// URL de TON API
$API_URL = 'https://nexus.0-0-0.click/';


// ==========================================
// FONCTION TELEGRAM
// ==========================================

function telegram($method, $data = [])
{
    global $BOT_TOKEN;

    $url = "https://api.telegram.org/bot" . $BOT_TOKEN . "/" . $method;

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_TIMEOUT => 30
    ]);

    $result = curl_exec($ch);

    curl_close($ch);

    return $result;
}


// ==========================================
// RECEVOIR LE MESSAGE
// ==========================================

$update = json_decode(file_get_contents("php://input"), true);

if (!$update || !isset($update['message'])) {
    exit;
}

$chatId = $update['message']['chat']['id'];
$text = $update['message']['text'] ?? '';


// ==========================================
// /START
// ==========================================

if ($text === '/start') {

    telegram('sendMessage', [
        'chat_id' => $chatId,
        'text' => "👋 Bienvenue !\n\nEnvoie ton message."
    ]);

    exit;
}


// ==========================================
// MESSAGE VIDE
// ==========================================

if (trim($text) === '') {

    telegram('sendMessage', [
        'chat_id' => $chatId,
        'text' => "⚠️ Envoie un message texte."
    ]);

    exit;
}


// ==========================================
// APPEL DIRECT À TON API
// ==========================================

// backup = ID Telegram
// msg = message envoyé

$requestUrl = $API_URL
    . '?backup=' . urlencode($chatId)
    . '&msg=' . urlencode($text);


$ch = curl_init($requestUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);


// ==========================================
// ERREUR API
// ==========================================

if ($response === false || $response === '') {

    telegram('sendMessage', [
        'chat_id' => $chatId,
        'text' => "❌ L'API n'a pas répondu."
    ]);

    exit;
}


// ==========================================
// ENVOYER LA VRAIE RÉPONSE
// ==========================================

telegram('sendMessage', [
    'chat_id' => $chatId,
    'text' => $response
]);

?>