<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['mech_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$mech_id = $_SESSION['mech_id'];
$amount = floatval($_POST['amount'] ?? 0);
$method = $_POST['method'] ?? '';
if ($amount <= 0 || !$method) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO withdrawal (mech_id, withdrawal_method, withdrawal_amt) VALUES (?, ?, ?)");
$stmt->bind_param("isd", $mech_id, $method, $amount);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
$stmt->close();
$conn->close();
?> 