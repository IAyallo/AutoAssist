<?php
include('db_connect.php'); // Your DB connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);

    // Check if user exists
    $query = "SELECT id, email FROM users WHERE email = ? OR phone = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        $userEmail = $user['email'];

        // Generate reset token
        $token = bin2hex(random_bytes(16));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        // Store token in DB
        $insert = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $insert->bind_param("iss", $userId, $token, $expires);
        $insert->execute();

        // Simulate sending email
        $resetLink = "http://localhost/reset_password.php?token=$token";
        echo "Reset link: <a href='$resetLink'>$resetLink</a>";
        // In real use: mail($userEmail, "Reset Your Password", "Click here: $resetLink");
    } else {
        echo "No account found with that email or phone.";
    }
}
?>
