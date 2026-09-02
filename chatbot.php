<?php
/**
 * AutoPulse - Chatbot Backend Endpoint
 * Rule-based FAQ matcher & dynamic database car query engine.
 * Fully offline - no external AI APIs or paid services required.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

// Parse incoming JSON payload
$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if (empty($message)) {
    echo json_encode([
        'reply' => 'Please enter a question or choose from the suggested topics below.',
        'suggestions' => ['Price of Nexon', 'Compare cars', 'Upcoming cars', 'Latest news']
    ]);
    exit;
}

$lowerMsg = strtolower($message);

try {
    // 1. DYNAMIC CAR DATABASE QUERY: Check if user is asking about a specific car's price or specs
    // Fetch all cars for dynamic recognition
    $stmt = $pdo->query("SELECT c.id, c.name, c.slug, c.price_min, c.price_max, c.fuel_type, c.mileage, c.power, c.safety_rating, b.name AS brand_name 
                         FROM cars c 
                         LEFT JOIN brands b ON c.brand_id = b.id");
    $cars = $stmt->fetchAll();

    $matchedCar = null;
    foreach ($cars as $car) {
        $carNameLower = strtolower($car['name']);
        // Check if car name or key token (e.g., "nexon", "creta", "xuv700", "swift", "curvv") is present
        $tokens = explode(' ', $carNameLower);
        foreach ($tokens as $t) {
            if (strlen($t) >= 4 && strpos($lowerMsg, $t) !== false) {
                $matchedCar = $car;
                break 2;
            }
        }
    }

    // Dynamic Price Intent
    if ($matchedCar && (strpos($lowerMsg, 'price') !== false || strpos($lowerMsg, 'cost') !== false || strpos($lowerMsg, 'how much') !== false || strpos($lowerMsg, 'rate') !== false)) {
        $priceStr = format_car_price($matchedCar['price_min'], $matchedCar['price_max']);
        $reply = "The ex-showroom price of the <strong>{$matchedCar['name']}</strong> ranges between <strong>{$priceStr}</strong>.<br><br>" .
                 "• <strong>Fuel:</strong> {$matchedCar['fuel_type']}<br>" .
                 "• <strong>Mileage:</strong> {$matchedCar['mileage']}<br>" .
                 "• <strong>Safety:</strong> {$matchedCar['safety_rating']}<br><br>" .
                 "<a href='car-detail.php?slug={$matchedCar['slug']}' style='color:#D90000; font-weight:700;'>Click here to view full specs &amp; gallery &rarr;</a>";

        echo json_encode([
            'reply' => $reply,
            'suggestions' => ['Compare with Creta', 'Test drive booking', 'Latest car news']
        ]);
        exit;
    }

    // Dynamic Specs / Mileage Intent
    if ($matchedCar && (strpos($lowerMsg, 'mileage') !== false || strpos($lowerMsg, 'average') !== false || strpos($lowerMsg, 'kmpl') !== false || strpos($lowerMsg, 'specs') !== false)) {
        $reply = "Here are the key specifications for <strong>{$matchedCar['name']}</strong>:<br><br>" .
                 "• <strong>Mileage:</strong> {$matchedCar['mileage']}<br>" .
                 "• <strong>Power:</strong> {$matchedCar['power']}<br>" .
                 "• <strong>Fuel Type:</strong> {$matchedCar['fuel_type']}<br>" .
                 "• <strong>Crash Safety:</strong> {$matchedCar['safety_rating']}<br><br>" .
                 "<a href='car-detail.php?slug={$matchedCar['slug']}' style='color:#D90000; font-weight:700;'>View Complete Road Test &rarr;</a>";

        echo json_encode([
            'reply' => $reply,
            'suggestions' => ["Price of {$matchedCar['name']}", 'Compare cars', 'Show reviews']
        ]);
        exit;
    }

    // Dynamic Comparison Intent between 2 cars
    if (strpos($lowerMsg, 'compare') !== false || strpos($lowerMsg, ' vs ') !== false || strpos($lowerMsg, 'versus') !== false) {
        $foundCars = [];
        foreach ($cars as $car) {
            $tokens = explode(' ', strtolower($car['name']));
            foreach ($tokens as $t) {
                if (strlen($t) >= 4 && strpos($lowerMsg, $t) !== false) {
                    $foundCars[] = $car;
                    break;
                }
            }
        }

        if (count($foundCars) >= 2) {
            $carA = $foundCars[0];
            $carB = $foundCars[1];
            $priceA = format_car_price($carA['price_min'], $carA['price_max']);
            $priceB = format_car_price($carB['price_min'], $carB['price_max']);

            $reply = "<strong>Quick Comparison: {$carA['name']} vs {$carB['name']}</strong><br><br>" .
                     "• <strong>Price:</strong><br>&nbsp;&nbsp;- {$carA['name']}: {$priceA}<br>&nbsp;&nbsp;- {$carB['name']}: {$priceB}<br><br>" .
                     "• <strong>Mileage:</strong><br>&nbsp;&nbsp;- {$carA['name']}: {$carA['mileage']}<br>&nbsp;&nbsp;- {$carB['name']}: {$carB['mileage']}<br><br>" .
                     "• <strong>Power:</strong><br>&nbsp;&nbsp;- {$carA['name']}: {$carA['power']}<br>&nbsp;&nbsp;- {$carB['name']}: {$carB['power']}<br><br>" .
                     "<a href='compare.php?car1={$carA['id']}&car2={$carB['id']}' style='color:#D90000; font-weight:700;'>Open Side-by-Side Spec Matrix &rarr;</a>";

            echo json_encode([
                'reply' => $reply,
                'suggestions' => ['Book test drive', 'Upcoming cars', 'User reviews']
            ]);
            exit;
        }
    }

    // 2. KEYWORD RULE-BASED MATCHING FROM DATABASE
    $stmt = $pdo->query("SELECT trigger_keywords, response_text FROM chatbot_responses");
    $rules = $stmt->fetchAll();

    foreach ($rules as $rule) {
        $triggers = array_map('trim', explode(',', strtolower($rule['trigger_keywords'])));
        foreach ($triggers as $trigger) {
            if (!empty($trigger) && strpos($lowerMsg, $trigger) !== false) {
                echo json_encode([
                    'reply' => $rule['response_text'],
                    'suggestions' => ['Price of Nexon', 'Price of Creta', 'Compare cars', 'Latest news']
                ]);
                exit;
            }
        }
    }

    // 3. FALLBACK RESPONSE
    echo json_encode([
        'reply' => "I couldn't find an exact match for that. Try asking about car prices (e.g. <em>'Price of Nexon'</em>), comparisons (e.g. <em>'Compare Nexon and Creta'</em>), safety ratings, or latest auto news!",
        'suggestions' => ['Price of Nexon', 'Price of Creta', 'Upcoming cars', 'Compare cars', 'Contact support']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'reply' => 'AutoPulse Assistant is momentarily refreshing. Please try asking again shortly!',
        'suggestions' => ['Price of Creta', 'Compare cars', 'Latest news']
    ]);
}
