<?php
require 'connection.php';

$userPhone = $_GET['userPhone'] ?? '';
if (!$userPhone) {
    echo json_encode([]);
    exit;
}

// Get user_id from phone
$userRes = $conn->query("SELECT user_id FROM user WHERE phone = '$userPhone'");
if ($userRes->num_rows === 0) {
    echo json_encode([]);
    exit;
}
$user_id = $userRes->fetch_assoc()['user_id'];

// Get service history
$sql = "SELECT s.service_id, s.time_served, s.service_name, s.locality, s.rating,
               c.car_type, c.car_model, c.number_plate,
               m.name AS mechanic_name
        FROM service s
        JOIN userCar c ON s.car_id = c.car_id
        JOIN mechanic m ON s.mech_id = m.mech_id
        WHERE s.user_id = '$user_id'
        ORDER BY s.time_served DESC
        LIMIT 10";
$result = $conn->query($sql);

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
echo json_encode($history);
$conn->close();
?> 