<?php
file_put_contents('debug.txt', "COMPLETE_SERVICE CALLED\n", FILE_APPEND);

require 'connection.php';

$service_id = intval($_POST['service_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);

if ($service_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

file_put_contents('debug.txt', print_r($_POST, true), FILE_APPEND);

$stmt = $conn->prepare("UPDATE service SET status = 'completed', rating = ? WHERE service_id = ?");
$stmt->bind_param("ii", $rating, $service_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
}
$stmt->close();
$conn->close();
?> 