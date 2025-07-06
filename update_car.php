<?php
// Include connection file
require 'connection.php';

// Get car ID and updated values from request
$car_id = $_POST['car_id'];
$car_type = $_POST['car_type'];
$car_model = $_POST['car_model'];
$car_year = $_POST['car_year'];
$car_colour = $_POST['car_colour'];
$number_plate = $_POST['number_plate'];

// Update car record in database
$sql = "UPDATE userCar SET car_type = '$car_type', car_model = '$car_model', car_year = '$car_year', car_colour = '$car_colour', number_plate = '$number_plate' WHERE car_id = '$car_id'";

if (mysqli_query($conn, $sql)) {
    echo json_encode(array("success" => true, "message" => "Car updated successfully!"));
} else {
    echo json_encode(array("success" => false, "message" => "Failed to update car."));
}

// Close database connection
mysqli_close($conn);
?>