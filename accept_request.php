<?php
require 'connection.php';
session_start();
$mech_id = $_SESSION['mech_id'] ?? null;
$service_id = $_POST['service_id'] ?? null;

if (!$mech_id || !$service_id) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$stmt = $conn->prepare("UPDATE service SET mech_id = ?, status = 'in_progress' WHERE service_id = ?");
$stmt->bind_param("ii", $mech_id, $service_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?>