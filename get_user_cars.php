<?php
require 'connection.php';

header('Content-Type: application/json');

$userPhone = $_GET['userPhone'] ?? '';
if (!$userPhone) {
    echo json_encode([]);
    exit;
}

// Get user_id from phone
$stmt = $conn->prepare("SELECT user_id FROM user WHERE phone = ?");
$stmt->bind_param("s", $userPhone);
$stmt->execute();
$stmt->bind_result($user_id);
if ($stmt->fetch()) {
    $stmt->close();

    // Fetch cars for this user
    $cars = [];
    $stmt = $conn->prepare("SELECT car_id, car_type, car_model, number_plate, car_year, car_colour FROM usercar WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($car_id, $car_type, $car_model, $number_plate, $car_year, $car_colour);
    while ($stmt->fetch()) {
        $cars[] = [
            'car_id' => $car_id,
            'car_type' => $car_type,
            'car_model' => $car_model,
            'number_plate' => $number_plate,
            'car_year' => $car_year,
            'car_colour' => $car_colour
        ];
    }
    $stmt->close();
    echo json_encode($cars);
} else {
    echo json_encode([]);
}
$conn->close();
?>