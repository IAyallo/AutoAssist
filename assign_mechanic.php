<?php
// assign_mechanic.php
require 'connection.php';
require_once 'service_price.php';
$service_id = $_POST['service_id'] ?? null;
$mech_id = $_POST['mech_id'] ?? null;
if ($service_id && $mech_id) {
    // Fetch service_name and user_id for this service
    $stmt = $conn->prepare("SELECT service_name, user_id FROM service WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $stmt->bind_result($service_name, $user_id);
    if ($stmt->fetch()) {
        $price = getServicePrice($service_name);
    } else {
        echo json_encode(['success' => false, 'message' => 'Service not found']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
    // Insert or update payment record
    $stmt = $conn->prepare("SELECT pay_id FROM payment WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $stmt->bind_result($pay_id);
    if ($stmt->fetch()) {
        $stmt->close();
        // Update existing payment
        $stmt = $conn->prepare("UPDATE payment SET payment = ?, user_id = ?, mech_id = ? WHERE service_id = ?");
        $stmt->bind_param("diii", $price, $user_id, $mech_id, $service_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt->close();
        // Insert new payment
        $pay_method = 'Cash'; // Default, adjust as needed
        $stmt = $conn->prepare("INSERT INTO payment (user_id, mech_id, service_id, pay_method, payment) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisd", $user_id, $mech_id, $service_id, $pay_method, $price);
        $stmt->execute();
        $stmt->close();
    }
    $stmt = $conn->prepare("UPDATE service SET mech_id = ?, status = 'assigned' WHERE service_id = ?");
    $stmt->bind_param("ii", $mech_id, $service_id);
    $stmt->execute();
    // Update balance for today
    $today = date('Y-m-d');
    $stmt2 = $conn->prepare("SELECT balance FROM balance WHERE mech_id = ? AND DATE(date_update) = ?");
    $stmt2->bind_param("is", $mech_id, $today);
    $stmt2->execute();
    $stmt2->bind_result($current_balance);
    if ($stmt2->fetch()) {
        $stmt2->close();
        $stmt2 = $conn->prepare("UPDATE balance SET balance = balance + ? WHERE mech_id = ? AND DATE(date_update) = ?");
        $stmt2->bind_param("dis", $price, $mech_id, $today);
        $stmt2->execute();
        $stmt2->close();
    } else {
        $stmt2->close();
        $stmt2 = $conn->prepare("INSERT INTO balance (mech_id, date_update, balance) VALUES (?, NOW(), ?)");
        $stmt2->bind_param("id", $mech_id, $price);
        $stmt2->execute();
        $stmt2->close();
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
$conn->close();
?>