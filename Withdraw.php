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
// Calculate balance: sum of all service earnings - sum of all withdrawals
$balance = 0;
$transactions = [];
// Get all services for this mechanic
$stmt = $conn->prepare("SELECT service_name, time_served, locality FROM service WHERE mech_id = ? ORDER BY time_served DESC");
$stmt->bind_param("i", $mech_id);
$stmt->execute();
$stmt->bind_result($service_name, $time_served, $locality);
while ($stmt->fetch()) {
    $amount = getServicePrice($service_name);
    $balance += $amount;
    $transactions[] = [
        'type' => 'service',
        'desc' => $service_name,
        'amount' => $amount,
        'date' => $time_served,
        'locality' => $locality
    ];
}
$stmt->close();
// Get all withdrawals for this mechanic
$stmt = $conn->prepare("SELECT withdrawal_amt, withdrawal_method, date FROM withdrawal WHERE mech_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $mech_id);
$stmt->execute();
$stmt->bind_result($withdrawal_amt, $withdrawal_method, $withdrawal_date);
while ($stmt->fetch()) {
    $balance -= $withdrawal_amt;
    $transactions[] = [
        'type' => 'withdrawal',
        'desc' => $withdrawal_method,
        'amount' => $withdrawal_amt,
        'date' => $withdrawal_date
    ];
}
$stmt->close();
$conn->close();
// Sort transactions by date descending
usort($transactions, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Withdraw - AutoAssist</title>
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
      background-color: #f4f4f4;
      overflow: hidden;
    }

    .sidebar {
      width: 220px;
      background-color: #3ce0aa;
      display: flex;
      flex-direction: column;
      padding-top: 40px;
      color: white;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      padding: 15px 25px;
      font-weight: bold;
      display: block;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background-color: #34c99a;
      border-left: 5px solid white;
    }

    .main {
      flex: 1;
      padding: 30px;
      overflow-y: auto;
    }

    .balance-section {
      background: white;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .balance-section h2 {
      margin: 0;
      color: #333;
    }

    .withdraw-btn {
      padding: 10px 20px;
      background-color: #3ce0aa;
      color: white;
      border: none;
      border-radius: 10px;
      font-weight: bold;
      cursor: pointer;
    }

    .transaction-list {
      background: white;
      padding: 20px;
      border-radius: 16px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .transaction-item {
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    .transaction-item:last-child {
      border-bottom: none;
    }

    .transaction-item strong {
      display: block;
      color: #222;
    }

    .popup, .success-popup {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .popup-content, .success-content {
      background: white;
      padding: 30px;
      border-radius: 16px;
      text-align: center;
      width: 320px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .popup-content select, .popup-content input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 8px;
      border: 1px solid #ccc;
    }

    .popup-content button {
      padding: 10px 20px;
      border: none;
      border-radius: 10px;
      background-color: #3ce0aa;
      color: white;
      font-weight: bold;
      cursor: pointer;
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

  <!-- Main Content -->
  <div class="main">
    <div class="header">
      <div class="greeting">Hello, <?php echo htmlspecialchars($mech_name); ?></div>
      <div class="profile-pic initials-avatar"><?php
        $initials = '';
        foreach (explode(' ', $mech_name) as $n) { $initials .= strtoupper($n[0]); }
        echo htmlspecialchars($initials);
      ?></div>
    <div class="balance-section">
      <h2>Current Balance: KES <?php echo number_format(max(0, $balance)); ?></h2>
      <button class="withdraw-btn" onclick="showWithdrawPopup()">Withdraw</button>
    </div>

    <div class="transaction-list">
      <h3>Recent Transactions</h3>
      <?php if (empty($transactions)): ?>
        <div class="transaction-item">No transactions yet.</div>
      <?php else: ?>
        <?php foreach ($transactions as $txn): ?>
          <div class="transaction-item">
            <?php if ($txn['type'] === 'service'): ?>
              <strong>Service - <?php echo htmlspecialchars($txn['desc']); ?> (KES <?php echo number_format($txn['amount']); ?>)</strong>
              <span><?php echo htmlspecialchars($txn['locality']); ?> • <?php echo date('d M Y', strtotime($txn['date'])); ?></span>
            <?php else: ?>
              <strong>Withdrawal - KES <?php echo number_format($txn['amount']); ?></strong>
              <span><?php echo htmlspecialchars($txn['desc']); ?> • <?php echo date('d M Y', strtotime($txn['date'])); ?></span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Withdraw Popup -->
  <div class="popup" id="withdraw-popup">
    <div class="popup-content">
      <h3>Withdraw Funds</h3>
      <input type="number" placeholder="Enter amount (KES)" id="amount">
      <select id="method" onchange="toggleInputs()">
        <option value="">Select Method</option>
        <option value="mpesa">📱 MPesa</option>
        <option value="bank">🏦 Bank Account</option>
      </select>
      <input type="text" id="mpesa" placeholder="Enter MPesa Number" style="display:none;">
      <input type="text" id="bank" placeholder="Enter Bank Account Details" style="display:none;">
      <button onclick="confirmWithdraw()">Confirm</button>
    </div>
  </div>

  <!-- Success Popup -->
  <div class="success-popup" id="success-popup">
    <div class="success-content">
      <h1>🎉</h1>
      <h3>Payment will be processed within 5 working hours</h3>
    </div>
  </div>

  <script>
    function showWithdrawPopup() {
      document.getElementById('withdraw-popup').style.display = 'flex';
    }

    function toggleInputs() {
      const method = document.getElementById('method').value;
      document.getElementById('mpesa').style.display = method === 'mpesa' ? 'block' : 'none';
      document.getElementById('bank').style.display = method === 'bank' ? 'block' : 'none';
    }

    function confirmWithdraw() {
      const amount = parseFloat(document.getElementById('amount').value);
      const method = document.getElementById('method').value;
      if (!amount || !method) {
        alert('Please enter amount and select method.');
        return;
      }
      document.getElementById('withdraw-popup').style.display = 'none';
      fetch('process_withdrawal.php', {
        method: 'POST',
        body: new URLSearchParams({ amount, method })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          document.getElementById('success-popup').style.display = 'flex';
          setTimeout(() => {
            window.location.href = 'MechHomePage.php';
          }, 4000);
        } else {
          alert(data.message || 'Withdrawal failed.');
        }
      });
    }
  </script>
</body>
</html>
