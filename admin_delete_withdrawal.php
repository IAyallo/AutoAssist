<?php
require 'connection.php';
$withdrawal_id = intval($_POST['withdrawal_id'] ?? 0);
if (!$withdrawal_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid withdrawal']);
    exit;
}
$stmt = $conn->prepare("DELETE FROM withdrawal WHERE withdrawal_id=?");
$stmt->bind_param("i", $withdrawal_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 