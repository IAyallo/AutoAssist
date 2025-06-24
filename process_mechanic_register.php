<?php
<?php
require 'connection.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($phone) || empty($password)) {
        echo "Phone number and password are required.";
        exit;
    }

    $stmt = $conn->prepare("SELECT mech_id, name, password FROM mechanic WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($mech_id, $name, $password_hash);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            // Store mechanic info in session
            $_SESSION['mech_id'] = $mech_id;
            $_SESSION['mech_name'] = $name;
            header("Location: MechHomePage.php"); // Change to .php
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Mechanic not found or invalid credentials.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>