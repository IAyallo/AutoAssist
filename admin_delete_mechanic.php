<?php
require 'connection.php';
$mech_id = intval($_POST['mech_id'] ?? 0);
if (!$mech_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid mechanic']);
    exit;
}
$stmt = $conn->prepare("DELETE FROM mechanic WHERE mech_id=?");
$stmt->bind_param("i", $mech_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 