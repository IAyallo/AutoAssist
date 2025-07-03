<?php
require 'connection.php';

$userPhone = $_POST['userPhone'] ?? '';
$car_id = $_POST['car_id'] ?? '';
$service_name = $_POST['service_type'] ?? '';
$locality = $_POST['locality'] ?? '';
$time_served = date('Y-m-d H:i:s'); // or use NULL if not served yet

if (!$userPhone || !$car_id || !$service_name) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

// Get user_id from phone
$stmt = $conn->prepare("SELECT user_id FROM user WHERE phone = ?");
$stmt->bind_param("s", $userPhone);
$stmt->execute();
$stmt->bind_result($user_id);
if ($stmt->fetch()) {
    $stmt->close();

    // Insert service request (mech_id, rating can be NULL for now)
    $stmt = $conn->prepare("INSERT INTO service (user_id, car_id, service_name, time_served, locality, status) VALUES (?, ?, ?, ?, ?, ?)");
    $status = 'pending';
    $stmt->bind_param("iissss", $user_id, $car_id, $service_name, $time_served, $locality, $status);
    if ($stmt->execute()) {
        $service_id = $stmt->insert_id;
        echo json_encode(['success' => true, 'service_id' => $service_id]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
}
$conn->close();
