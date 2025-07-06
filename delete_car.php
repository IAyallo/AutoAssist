<?php
// Include connection file
require 'connection.php';

// Get car ID from request
$car_id = $_POST['car_id'];

// Delete car record from database
$sql = "DELETE FROM userCar WHERE car_id = '$car_id'";

if (mysqli_query($conn, $sql)) {
    echo json_encode(array("success" => true, "message" => "Car deleted."));
} else {
    echo json_encode(array("success" => false, "message" => "Failed to delete car."));
}

// Close database connection
mysqli_close($conn);
?>