<?php
require 'connection.php';
$service_id = intval($_POST['service_id'] ?? 0);
if (!$service_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid service']);
    exit;
}
$stmt = $conn->prepare("DELETE FROM service WHERE service_id=?");
$stmt->bind_param("i", $service_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 