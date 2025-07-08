<?php
require 'connection.php';
header('Content-Type: application/json');

$stmt = $conn->prepare("SELECT s.service_id, s.user_id, s.car_id, s.service_name, s.locality, s.time_served, u.name as user_name, c.car_type, c.car_model, c.number_plate
    FROM service s
    JOIN user u ON s.user_id = u.user_id
    JOIN usercar c ON s.car_id = c.car_id
    WHERE s.mech_id IS NULL AND s.status = 'pending'
    ORDER BY s.time_served ASC");
$stmt->execute();
$stmt->bind_result($service_id, $user_id, $car_id, $service_name, $locality, $time_served, $user_name, $car_type, $car_model, $number_plate);

$requests = [];
while ($stmt->fetch()) {
    $requests[] = [
        'service_id' => $service_id,
        'user_id' => $user_id,
        'car_id' => $car_id,
        'service_name' => $service_name,
        'locality' => $locality,
        'time_served' => $time_served,
        'user_name' => $user_name,
        'car_type' => $car_type,
        'car_model' => $car_model,
        'number_plate' => $number_plate
    ];
}
$stmt->close();
$conn->close();
echo json_encode($requests);
?>