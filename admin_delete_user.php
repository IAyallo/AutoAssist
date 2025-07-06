<?php
require 'connection.php';
$user_id = intval($_POST['user_id'] ?? 0);
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}
$stmt = $conn->prepare("DELETE FROM user WHERE user_id=?");
$stmt->bind_param("i", $user_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?> 