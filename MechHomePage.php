<?php
session_start();
if (!isset($_SESSION['mech_name'])) {
    header("Location: MechLogIn.html");
    exit;
}
$mech_name = $_SESSION['mech_name'];
$profile_pic = $_SESSION['mech_profile_pic'] ?? 'profile.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AutoAssist Mechanic Home</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f8f8f8;
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

    /* Sidebar */
    .sidebar {
      width: 220px;
      background-color: #3ce0aa;
      display: flex;
      flex-direction: column;
      padding-top: 40px;
      color: white;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      padding: 15px 25px;
      display: block;
      font-weight: bold;
      transition: background 0.3s ease;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #34c99a;
      border-left: 5px solid white;
    }

    /* Main content */
    .main {
      flex: 1;
      display: flex;
      flex-direction: column;
      padding: 20px;
      overflow-y: auto;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background-color: #3ce0aa;
      padding: 20px;
      border-radius: 16px;
      color: white;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }

    .greeting {
      font-size: 1.4em;
      font-weight: bold;
    }

    .profile-pic {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid white;
    }

    .recent-services {
      background: white;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .recent-services h3 {
      margin-top: 0;
      color: #333;
    }

    .service-item {
      padding: 12px 0;
      border-bottom: 1px solid #eee;
    }

    .service-item:last-child {
      border-bottom: none;
    }

    .service-item strong {
      display: block;
      color: #222;
    }

    .service-item span {
      color: #666;
      font-size: 0.9em;
    }

    /* Popup Alert (floating Uber-style) */
    .popup {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: white;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
      display: none;
      z-index: 1000;
      width: 300px;
    }

    .popup h3 {
      margin-top: 0;
      color: #333;
      margin-bottom: 15px;
    }

    .popup-buttons {
      display: flex;
      justify-content: space-between;
    }

    .popup-buttons button {
      padding: 10px 15px;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      font-size: 0.9em;
    }

    .btn-allow {
      background-color: #3ce0aa;
      color: white;
    }

    .btn-reject {
      background-color: #ccc;
      color: #333;
    }

    .btn-allow:hover {
      background-color: #34c99a;
    }

    .btn-reject:hover {
      background-color: #bbb;
    }

    ::-webkit-scrollbar {
      width: 6px;
    }

    ::-webkit-scrollbar-thumb {
      background-color: #ccc;
      border-radius: 6px;
    }
  </style>
</head>
<body>

  <!-- Sidebar Navigation -->
  <div class="sidebar">
    <a href="MechHomePage.php" class="active">🏠 Home</a>
    <a href="Withdraw.php">💳 Withdraw</a>
    <a href="History.php">📜 History</a>
    <form action="logout.php" method="post" style="margin-top:30px; padding:0 25px;">
      <button type="submit" style="width:100%; background:#fff; color:#3ce0aa; border:none; border-radius:8px; padding:12px; font-weight:bold; cursor:pointer; margin-top:10px;">
        🚪 Logout
      </button>
    </form>
  </div>

  <!-- Main content -->
  <div class="main">
    <div class="header">
      <div class="greeting">Hello, <?php echo htmlspecialchars($mech_name); ?></div>
      <img src="profile.jpg" alt="Profile Picture" class="profile-pic">
    </div>

    <div class="recent-services">
      <h3>Recent Services</h3>
      <div class="service-item">
        <strong>Brake Repair</strong>
        <span>15 June 2025 – Kilimani</span>
      </div>
      <div class="service-item">
        <strong>Oil Change</strong>
        <span>13 June 2025 – Nairobi CBD</span>
      </div>
      <div class="service-item">
        <strong>Flat Tire Replacement</strong>
        <span>10 June 2025 – South B</span>
      </div>
    </div>
  </div>

  <!-- Request Popup -->
  <div class="popup" id="request-popup">
    <h3>Incoming Service Request!</h3>
    <div class="popup-buttons">
      <button class="btn-allow" onclick="acceptRequest()">Accept</button>
      <button class="btn-reject" onclick="rejectRequest()">Dismiss</button>
    </div>
  </div>

  <script>
    setTimeout(() => {
      document.getElementById('request-popup').style.display = 'block';
    }, 10000);

    function acceptRequest() {
      window.location.href = 'request.html';
    }

    function rejectRequest() {
      document.getElementById('request-popup').style.display = 'none';
    }
  </script>

</body>
</html>
