<?php
// assign_mechanic.php
require 'connection.php';
$service_id = $_POST['service_id'] ?? null;
$mech_id = $_POST['mech_id'] ?? null;
if ($service_id && $mech_id) {
    $stmt = $conn->prepare("UPDATE service SET mech_id = ?, status = 'assigned' WHERE service_id = ?");
    $stmt->bind_param("ii", $mech_id, $service_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
$conn->close();
?>