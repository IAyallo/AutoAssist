<?php
session_start();
if (!isset($_SESSION['mech_name']) || !isset($_SESSION['mech_id'])) {
    header("Location: MechLogIn.html");
    exit;
}
$mech_name = $_SESSION['mech_name'];
$mech_id = $_SESSION['mech_id'];
$profile_pic = $_SESSION['mech_profile_pic'] ?? 'profile.jpg';

// Helper function for service price
function getServicePrice($service_name) {
    $prices = [
        'Breakdown Assistance' => 2000,
        'Tire Change' => 1500,
        'Battery Jumpstart' => 1800,
        'Fuel Delivery' => 1200,
        'Other' => 1000
    ];
    foreach ($prices as $key => $val) {
        if (stripos($service_name, $key) !== false) return $val;
    }
    return 1000; // default for unknown
}

require 'connection.php';
// Fetch recent services for this mechanic
$stmt = $conn->prepare("SELECT service_name, time_served, locality FROM service WHERE mech_id = ? ORDER BY time_served DESC LIMIT 5");
$stmt->bind_param("i", $mech_id);
$stmt->execute();
$stmt->bind_result($service_name, $time_served, $locality);
$recent_services = [];
while ($stmt->fetch()) {
    $recent_services[] = [
        'service_name' => $service_name,
        'time_served' => $time_served,
        'locality' => $locality
    ];
}
$stmt->close();
$conn->close();
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

    .profile-pic.initials-avatar {
      background: #34c99a;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5em;
      font-weight: bold;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      border: 2px solid #fff;
      object-fit: cover;
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
      <div class="profile-pic initials-avatar"><?php
        $initials = '';
        foreach (explode(' ', $mech_name) as $n) { $initials .= strtoupper($n[0]); }
        echo htmlspecialchars($initials);
      ?></div>
    </div>

    <div class="recent-services">
      <h3>Recent Services</h3>
      <?php if (empty($recent_services)): ?>
        <div class="service-item">No recent services.</div>
      <?php else: ?>
        <?php foreach ($recent_services as $srv): ?>
          <div class="service-item">
            <strong><?php echo htmlspecialchars($srv['service_name']); ?></strong>
            <span><?php echo date('d M Y', strtotime($srv['time_served'])); ?> – <?php echo htmlspecialchars($srv['locality']); ?></span>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div id="pending-requests"></div>
  </div>

  <script>
    function fetchPendingRequests() {
      fetch('get_pending_requests.php')
        .then(res => res.json())
        .then(requests => {
          const container = document.getElementById('pending-requests');
          container.innerHTML = '<h3>Unassigned Service Requests</h3>';
          if (requests.length === 0) {
            container.innerHTML += '<p>No unassigned requests at the moment.</p>';
            return;
          }
          requests.forEach(req => {
            const div = document.createElement('div');
            div.style.marginBottom = '16px';
            div.innerHTML = `
              <strong>${req.user_name}</strong> - ${req.car_type} ${req.car_model} (${req.number_plate})<br>
              Service: ${req.service_name} | Location: ${req.locality}
              <button onclick=\"acceptRequest(${req.service_id})\">Accept</button>
            `;
            container.appendChild(div);
          });
        });
    }

    // On page load, fetch pending requests
    fetchPendingRequests();
  </script>

</body>
</html>
