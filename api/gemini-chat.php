<?php
/**
 * AutoPulse - Gemini AI Chatbot Endpoint
 * 
 * Uses Google Gemini API to answer automotive questions in context of AutoPulse.
 * Falls back to the rule-based engine if API key is missing or quota exceeded.
 * 
 * Set your key in: includes/config.php  →  GEMINI_API_KEY
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

// ─────────────────────────────────────────────
// CONFIG: API key is read from environment variable only
// For XAMPP local: set in php.ini → add: gemini_api_key = AIzaSy...
// Or create a file: C:\xampp\htdocs\autopulse\.env and set GEMINI_API_KEY=AIzaSy...
// NEVER hardcode the key here — this file is public on GitHub!
// ─────────────────────────────────────────────
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');

// Read user message
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? $_POST['message'] ?? '');

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Please type a question!', 'suggestions' => []]);
    exit;
}

// ─────────────────────────────────────────────
// Build live car data context from DB / JSON
// ─────────────────────────────────────────────
$carContext = '';
try {
    $stmt = $pdo->query("SELECT c.name, b.name AS brand, c.fuel_type, c.price_min, c.price_max, c.mileage, c.power, c.torque, c.safety_rating, c.seating_capacity
                         FROM cars c LEFT JOIN brands b ON c.brand_id = b.id");
    $cars = $stmt->fetchAll();
    $carContext = "Here is the latest AutoPulse car database:\n";
    foreach ($cars as $c) {
        $carContext .= "- {$c['brand']} {$c['name']}: Price Rs {$c['price_min']}–{$c['price_max']} Lakh, Fuel: {$c['fuel_type']}, Mileage: {$c['mileage']}, Power: {$c['power']}, Safety: {$c['safety_rating']}\n";
    }
} catch (Exception $e) {
    // Fallback context from JSON
    $json = file_get_contents(__DIR__ . '/data/cars.json');
    $cars = json_decode($json, true) ?: [];
    $carContext = "AutoPulse car catalog:\n";
    foreach ($cars as $c) {
        $carContext .= "- {$c['brand_name']} {$c['name']}: Price Rs {$c['price_min']}–{$c['price_max']} Lakh, Fuel: {$c['fuel_type']}, Mileage: {$c['mileage']}, Power: {$c['power']}\n";
    }
}

// ─────────────────────────────────────────────
// System Prompt — AutoPulse AI personality
// ─────────────────────────────────────────────
$systemPrompt = <<<PROMPT
You are the AutoPulse AI Assistant — India's premier automotive expert chatbot for the AutoPulse portal (inspired by Autocar India).

Your capabilities & instructions:
1. You have comprehensive, up-to-date knowledge of the entire Indian and global automotive market: upcoming launches, facelifts, pricing, variants, engine specs, EV range, crash safety, and road test verdicts (e.g. BMW X5, Mahindra Thar Roxx, Maruti Dzire, Tata Curvv, Creta, Fortuner, etc.).
2. You also have access to the featured AutoPulse verified database below.
3. Answer questions about ANY car accurately, authoritatively, and concisely like an Autocar India automotive journalist.
4. When asked about an upcoming car or launch timeline (like BMW X5, next-gen models, etc.), provide the real automotive industry launch details, expected prices, and engine options!
5. Format your output cleanly with HTML: use <strong>bold</strong> for car names and figures, use <br> for line breaks, and bullet points (•) for specs.
6. Keep responses under 180 words, punchy and easy to read on mobile.
7. If the car is in the AutoPulse catalog below, mention that they can check full specs or book a test drive on AutoPulse!

AutoPulse Featured Database:
$carContext
PROMPT;

// ─────────────────────────────────────────────
// Call Gemini API (gemini-1.5-flash)
// ─────────────────────────────────────────────
function callGemini(string $apiKey, string $systemPrompt, string $userMessage): array
{
    // gemini-3.6-flash provides lightning-fast responses
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key={$apiKey}";

    $payload = json_encode([
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $systemPrompt . "\n\nUser Question: " . $userMessage]
                ]
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'maxOutputTokens' => 600
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError || $httpCode !== 200) {
        return ['error' => true, 'message' => "HTTP $httpCode: $curlError $response"];
    }

    $data = json_decode($response, true);

    if (isset($data['candidates'][0]['content']['parts'])) {
        $textParts = [];
        foreach ($data['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text']) && !empty(trim($part['text']))) {
                $textParts[] = $part['text'];
            }
        }
        if (!empty($textParts)) {
            return ['error' => false, 'text' => implode("\n", $textParts)];
        }
    }

    if (isset($data['error']['message'])) {
        return ['error' => true, 'message' => $data['error']['message']];
    }

    return ['error' => true, 'message' => 'Unexpected Gemini response structure: ' . substr($response, 0, 400)];
}


// ─────────────────────────────────────────────
// Try Gemini — fallback to rule engine
// ─────────────────────────────────────────────
$apiKeyConfigured = (
    GEMINI_API_KEY !== 'YOUR_GEMINI_API_KEY_HERE' &&
    strlen(GEMINI_API_KEY) > 20 &&
    strpos(GEMINI_API_KEY, 'AIzaSy') === 0   // Gemini REST keys always start with AIzaSy
);

if (!$apiKeyConfigured) {
    // Wrong key format — tell the user exactly what to do
    echo json_encode([
        'reply' => '⚠️ <strong>Gemini API key issue.</strong><br><br>The key you entered does not appear to be a valid Gemini REST API key.<br><br>✅ Valid keys start with <code>AIzaSy...</code><br><br>Get your free key at:<br><a href="https://aistudio.google.com/app/apikey" target="_blank" style="color:#4285f4;font-weight:700;">aistudio.google.com/app/apikey →</a><br><br>Then paste it in <code>api/gemini-chat.php</code> on line 27.',
        'source' => 'error',
        'suggestions' => ['Price of Nexon', 'Compare cars', 'Best EV under 25L']
    ]);
    exit;
}

if ($apiKeyConfigured) {
    $result = callGemini(GEMINI_API_KEY, $systemPrompt, $userMessage);

    if (!$result['error']) {
        // Clean up any markdown asterisks the model might output
        $reply = $result['text'];
        $reply = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $reply);
        $reply = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $reply);
        $reply = str_replace("\n\n", '<br><br>', $reply);
        $reply = str_replace("\n", '<br>', $reply);

        echo json_encode([
            'reply' => $reply,
            'source' => 'gemini',
            'suggestions' => ['Compare cars', 'Upcoming EVs', 'Best mileage car', 'Safest car under 20L']
        ]);
        exit;
    }

    // API returned an error — log it and fall through to rule engine
    $geminiError = $result['message'];
} else {
    $geminiError = 'API key not configured';
}

// ─────────────────────────────────────────────
// Rule-based fallback (original chatbot.php logic)
// ─────────────────────────────────────────────
$lowerMsg = strtolower($userMessage);

// Rebuild car list for rule engine
$carList = [];
try {
    $stmt = $pdo->query("SELECT c.*, b.name AS brand_name FROM cars c LEFT JOIN brands b ON c.brand_id = b.id");
    $carList = $stmt->fetchAll();
} catch (Exception $e) {
    $json = file_get_contents(__DIR__ . '/data/cars.json');
    $carList = json_decode($json, true) ?: [];
}

$matchedCar = null;
foreach ($carList as $car) {
    $tokens = array_filter(preg_split('/[\s\-]+/', strtolower($car['name'])));
    foreach ($tokens as $t) {
        if (strlen($t) >= 4 && strpos($lowerMsg, $t) !== false) {
            $matchedCar = $car;
            break 2;
        }
    }
}

if ($matchedCar && (strpos($lowerMsg, 'price') !== false || strpos($lowerMsg, 'cost') !== false || strpos($lowerMsg, 'how much') !== false)) {
    $p = $matchedCar;
    $reply = "The ex-showroom price of <strong>{$p['name']}</strong> ranges from <strong>Rs {$p['price_min']} – {$p['price_max']} Lakh*</strong>.<br><br>" .
        "• <strong>Fuel:</strong> {$p['fuel_type']}<br>" .
        "• <strong>Mileage:</strong> {$p['mileage']}<br>" .
        "• <strong>Safety:</strong> {$p['safety_rating']}<br><br>" .
        "<em>Note: Gemini AI unavailable ({$geminiError}). Using rule engine.</em>";
    echo json_encode(['reply' => $reply, 'source' => 'rule', 'suggestions' => ['Compare cars', 'Mileage of ' . $p['name']]]);
    exit;
}

$reply = "I received your question but Gemini AI is currently unavailable (<em>{$geminiError}</em>). Try asking about car prices, mileage, or comparisons — I'll use my local knowledge base!";
echo json_encode(['reply' => $reply, 'source' => 'rule', 'suggestions' => ['Price of Nexon', 'Price of Creta', 'Compare cars']]);
