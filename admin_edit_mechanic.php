<?php
require 'connection.php';
$mech_id = intval($_POST['mech_id'] ?? 0);
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$locality = $_POST['locality'] ?? '';
if (!$mech_id || !$name || !$phone || !$email || !$locality) {
    echo json_encode(['success' => false, 'message' => 'All fields required']);
    exit;
}
$stmt = $conn->prepare("UPDATE mechanic SET name=?, phone=?, email=?, locality=? WHERE mech_id=?");
$stmt->bind_param("ssssi", $name, $phone, $email, $locality, $mech_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 