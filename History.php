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
// Fetch all services for this mechanic, including payment amount
$stmt = $conn->prepare("SELECT s.service_name, s.time_served, s.locality, p.payment FROM service s JOIN payment p ON s.service_id = p.service_id WHERE s.mech_id = ? ORDER BY s.time_served DESC");
$stmt->bind_param("i", $mech_id);
$stmt->execute();
$stmt->bind_result($service_name, $time_served, $locality, $amount);
$history = [];
while ($stmt->fetch()) {
    $history[] = [
        'service_name' => $service_name,
        'amount' => $amount,
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
  <title>AutoAssist History</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f8f8f8;
      display: flex;
      height: 100vh;
      overflow: hidden;
    }

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

    .service-history {
      background: white;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }

    .service-history h3 {
      margin-top: 0;
      color: #333;
    }

    .history-item {
      padding: 12px 0;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
    }

    .history-item:last-child {
      border-bottom: none;
    }

    .service-name {
      font-weight: bold;
      color: #222;
    }

    .service-date {
      color: #666;
      font-size: 0.9em;
    }

    .service-amount {
      color: green;
      font-weight: bold;
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

  <div class="main">
    <div class="header">
      <div class="greeting">Hello, <?php echo htmlspecialchars($mech_name); ?></div>
      <div class="profile-pic initials-avatar"><?php
        $initials = '';
        foreach (explode(' ', $mech_name) as $n) { $initials .= strtoupper($n[0]); }
        echo htmlspecialchars($initials);
      ?></div>
    </div>

    <div class="service-history">
      <h3>Service History & Earnings</h3>
      <?php if (empty($history)): ?>
        <div class="history-item">No service history yet.</div>
      <?php else: ?>
        <?php foreach ($history as $item): ?>
          <div class="history-item">
            <div>
              <div class="service-name"><?php echo htmlspecialchars($item['service_name']); ?></div>
              <div class="service-date"><?php echo date('d M Y', strtotime($item['time_served'])); ?> – <?php echo htmlspecialchars($item['locality']); ?></div>
            </div>
            <div class="service-amount">KES <?php echo number_format($item['amount']); ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
