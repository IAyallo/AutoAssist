<?php
$token = $_GET['token'] ?? '';

if (!$token) {
  die('Invalid or missing token.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reset Password</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #3ce0aa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .reset-container {
      background-color: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
      width: 360px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    .input-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      color: #444;
    }

    input[type="password"] {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }

    .btn {
      width: 100%;
      padding: 12px;
      background-color: #3ce0aa;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }

    .btn:hover {
      background-color: #34c398;
    }
  </style>
</head>
<body>
  <div class="reset-container">
    <h2>Set New Password</h2>
    <form action="process_reset_password.php" method="POST">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>" />
      <div class="input-group">
        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required />
      </div>
      <div class="input-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required />
      </div>
      <button class="btn" type="submit">Reset Password</button>
    </form>
  </div>
</body>
</html>
