<?php
// get_available_mechanics.php
require 'connection.php';
header('Content-Type: application/json');
$result = $conn->query("SELECT mech_id, name, locality FROM mechanic");
$mechs = [];
while ($row = $result->fetch_assoc()) {
    $mechs[] = $row;
}
echo json_encode($mechs);
$conn->close();
?>