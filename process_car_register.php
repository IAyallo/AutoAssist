<?php
require 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userPhone = $_POST['userPhone'] ?? '';
    $car_make = $_POST['car_make'] ?? '';
    $car_model = $_POST['car_model'] ?? '';
    $car_year = $_POST['car_year'] ?? '';
    $car_colour = $_POST['car_colour'] ?? '';
    $number_plate = $_POST['number_plate'] ?? '';

    if (!$userPhone || !$car_make || !$car_model || !$car_year || !$car_colour || !$number_plate) {
        echo "All fields are required.";
        exit;
    }

    // Get user_id from phone
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE phone = ?");
    $stmt->bind_param("s", $userPhone);
    $stmt->execute();
    $stmt->bind_result($user_id);
    if ($stmt->fetch()) {
        $stmt->close();

        // Insert car
        $stmt = $conn->prepare("INSERT INTO usercar (user_id, car_type, car_model, number_plate, car_year, car_colour) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("isssss", $user_id, $car_make, $car_model, $number_plate, $car_year, $car_colour);

        if ($stmt->execute()) {
            // After successful insert, output this JS:
            echo "<script>
                let cars = JSON.parse(localStorage.getItem('cars') || '[]');
                cars.push({
                    car_type: " . json_encode($car_make) . ",
                    car_year: " . json_encode($car_year) . ",
                    car_colour: " . json_encode($car_colour) . ",
                    number_plate: " . json_encode($number_plate) . "
                });
                localStorage.setItem('cars', JSON.stringify(cars));
                localStorage.setItem('carRegistered', 'true');
                window.location.href = '/AutoAssist/home.html';
            </script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "User not found.";
    }
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>