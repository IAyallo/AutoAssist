<?php
require 'connection.php';
$mech_id = intval($_POST['mech_id'] ?? 0);
$withdrawal_method = $_POST['withdrawal_method'] ?? '';
$date = $_POST['date'] ?? date('Y-m-d H:i:s');
$withdrawal_amt = floatval($_POST['withdrawal_amt'] ?? 0);
if (!$mech_id || !$withdrawal_method || !$withdrawal_amt) {
    echo json_encode(['success' => false, 'message' => 'All fields required']);
    exit;
}
$stmt = $conn->prepare("INSERT INTO withdrawal (mech_id, withdrawal_method, date, withdrawal_amt) VALUES (?, ?, ?, ?)");
$stmt->bind_param("issd", $mech_id, $withdrawal_method, $date, $withdrawal_amt);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 