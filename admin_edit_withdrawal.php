<?php
require 'connection.php';
$withdrawal_id = intval($_POST['withdrawal_id'] ?? 0);
$mech_id = intval($_POST['mech_id'] ?? 0);
$withdrawal_method = $_POST['withdrawal_method'] ?? '';
$date = $_POST['date'] ?? date('Y-m-d H:i:s');
$withdrawal_amt = floatval($_POST['withdrawal_amt'] ?? 0);
if (!$withdrawal_id || !$mech_id || !$withdrawal_method || !$withdrawal_amt) {
    echo json_encode(['success' => false, 'message' => 'All fields required']);
    exit;
}
$stmt = $conn->prepare("UPDATE withdrawal SET mech_id=?, withdrawal_method=?, date=?, withdrawal_amt=? WHERE withdrawal_id=?");
$stmt->bind_param("issdi", $mech_id, $withdrawal_method, $date, $withdrawal_amt, $withdrawal_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 