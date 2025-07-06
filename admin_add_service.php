<?php
require 'connection.php';
$user_id = intval($_POST['user_id'] ?? 0);
$car_id = intval($_POST['car_id'] ?? 0);
$service_name = $_POST['service_name'] ?? '';
$time_served = $_POST['time_served'] ?? date('Y-m-d H:i:s');
$locality = $_POST['locality'] ?? '';
$status = $_POST['status'] ?? 'pending';
$mech_id = isset($_POST['mech_id']) && $_POST['mech_id'] !== '' ? intval($_POST['mech_id']) : null;
$rating = isset($_POST['rating']) && $_POST['rating'] !== '' ? floatval($_POST['rating']) : null;
if (!$user_id || !$car_id || !$service_name || !$locality) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}
$stmt = $conn->prepare("INSERT INTO service (user_id, car_id, service_name, time_served, locality, status, mech_id, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iisssssd", $user_id, $car_id, $service_name, $time_served, $locality, $status, $mech_id, $rating);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 