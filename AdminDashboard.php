<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: AdminLogin.html');
    exit;
}
require 'connection.php';

// Fetch pending services
$pending = $conn->query("SELECT s.*, u.name AS user_name, m.name AS mech_name FROM service s JOIN user u ON s.user_id = u.user_id LEFT JOIN mechanic m ON s.mech_id = m.mech_id WHERE s.status = 'assigned' ORDER BY s.time_served DESC");

// Fetch running services
$running = $conn->query("SELECT s.*, u.name AS user_name, m.name AS mech_name FROM service s JOIN user u ON s.user_id = u.user_id LEFT JOIN mechanic m ON s.mech_id = m.mech_id WHERE s.status = 'in_progress' ORDER BY s.time_served DESC");

// Fetch completed services
$completed = $conn->query("SELECT s.*, u.name AS user_name, m.name AS mech_name FROM service s JOIN user u ON s.user_id = u.user_id LEFT JOIN mechanic m ON s.mech_id = m.mech_id WHERE s.status = 'completed' ORDER BY s.time_served DESC");

// Mechanics for User Management
$mechs = $conn->query("SELECT m.*, (SELECT AVG(rating) FROM service WHERE mech_id = m.mech_id AND rating IS NOT NULL) AS avg_rating, (SELECT COUNT(*) FROM service WHERE mech_id = m.mech_id) AS total_services FROM mechanic m");

// Users for User Management
$users = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM service WHERE user_id = u.user_id) AS total_services FROM user u");

// Withdrawals for Payments
$withdrawals = $conn->query("SELECT w.*, m.name AS mech_name FROM withdrawal w JOIN mechanic m ON w.mech_id = m.mech_id ORDER BY w.date DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Services</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f8f8; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 30px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #3ce0aa; color: #fff; }
        h1 { color: #3ce0aa; }
        h2 { margin-top: 40px; color: #222; }
        .status-assigned { color: #e67e22; font-weight: bold; }
        .status-in_progress { color: #2980b9; font-weight: bold; }
        .status-completed { color: #27ae60; font-weight: bold; }
        .tabs { display: flex; gap: 20px; margin-bottom: 30px; }
        .tab-btn { background: #eee; border: none; padding: 12px 24px; border-radius: 8px 8px 0 0; font-size: 1.1em; cursor: pointer; color: #333; }
        .tab-btn.active { background: #3ce0aa; color: #fff; font-weight: bold; }
        .tab-section { display: none; }
        .tab-section.active { display: block; }
    </style>
    <script>
    function showTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-section').forEach(sec => sec.classList.remove('active'));
        document.getElementById(tab+'-tab').classList.add('active');
        document.getElementById(tab+'-section').classList.add('active');
    }
    window.onload = function() { showTab('services'); };
    </script>
</head>
<body>
    <div class="container">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px;">
      <h1>Admin Dashboard</h1>
      <div style="display:flex; align-items:center; gap:18px;">
        <div class="profile-pic" style="width:48px; height:48px; border-radius:50%; background:#3ce0aa; color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.5em; font-weight:bold;">
          <?php
            $adminInitials = '';
            if (isset($_SESSION['admin_name']) && $_SESSION['admin_name']) {
              $parts = explode(' ', $_SESSION['admin_name']);
              foreach ($parts as $p) $adminInitials .= strtoupper($p[0]);
            } else if (isset($_SESSION['admin_email'])) {
              $adminInitials = strtoupper(substr($_SESSION['admin_email'],0,1));
            }
            echo htmlspecialchars($adminInitials);
          ?>
        </div>
        <form action="admin_logout.php" method="post" style="margin:0;">
          <button type="submit" style="background:#e74c3c; color:#fff; border:none; border-radius:8px; padding:10px 18px; font-weight:bold; cursor:pointer;">Logout</button>
        </form>
      </div>
    </div>
    <div class="tabs">
        <button class="tab-btn" id="services-tab" onclick="showTab('services')">Services</button>
        <button class="tab-btn" id="users-tab" onclick="showTab('users')">User Management</button>
        <button class="tab-btn" id="payments-tab" onclick="showTab('payments')">Payments</button>
    </div>

    <!-- Services Tab -->
    <div class="tab-section" id="services-section">
    <h2>Pending Services</h2>
    <table>
        <tr>
            <th>ID</th><th>User</th><th>Mechanic</th><th>Car</th><th>Service</th><th>Date</th><th>Locality</th><th>Status</th><th>Actions</th>
        </tr>
        <?php while($row = $pending->fetch_assoc()): ?>
        <tr data-service-id="<?= $row['service_id'] ?>" data-user-id="<?= $row['user_id'] ?>" data-mech-id="<?= $row['mech_id'] ?>" data-car-id="<?= $row['car_id'] ?>" data-service-name="<?= htmlspecialchars($row['service_name'], ENT_QUOTES) ?>" data-time-served="<?= $row['time_served'] ?>" data-locality="<?= htmlspecialchars($row['locality'], ENT_QUOTES) ?>" data-status="<?= $row['status'] ?>" data-rating="<?= $row['rating'] ?>">
            <td><?= $row['service_id'] ?></td>
            <td><?= htmlspecialchars($row['user_name']) ?></td>
            <td><?= htmlspecialchars($row['mech_name'] ?? '-') ?></td>
            <td><?= $row['car_id'] ?></td>
            <td><?= htmlspecialchars($row['service_name']) ?></td>
            <td><?= $row['time_served'] ?></td>
            <td><?= htmlspecialchars($row['locality']) ?></td>
            <td class="status-<?= htmlspecialchars($row['status']) ?>"><?= $row['status'] ?></td>
            <td>
                <button onclick="showEditServiceModal(this)">Edit</button>
                <button onclick="deleteService(<?= $row['service_id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <h2>Running Services</h2>
    <table>
        <tr>
            <th>ID</th><th>User</th><th>Mechanic</th><th>Car</th><th>Service</th><th>Date</th><th>Locality</th><th>Status</th><th>Actions</th>
        </tr>
        <?php while($row = $running->fetch_assoc()): ?>
        <tr data-service-id="<?= $row['service_id'] ?>" data-user-id="<?= $row['user_id'] ?>" data-mech-id="<?= $row['mech_id'] ?>" data-car-id="<?= $row['car_id'] ?>" data-service-name="<?= htmlspecialchars($row['service_name'], ENT_QUOTES) ?>" data-time-served="<?= $row['time_served'] ?>" data-locality="<?= htmlspecialchars($row['locality'], ENT_QUOTES) ?>" data-status="<?= $row['status'] ?>" data-rating="<?= $row['rating'] ?>">
            <td><?= $row['service_id'] ?></td>
            <td><?= htmlspecialchars($row['user_name']) ?></td>
            <td><?= htmlspecialchars($row['mech_name'] ?? '-') ?></td>
            <td><?= $row['car_id'] ?></td>
            <td><?= htmlspecialchars($row['service_name']) ?></td>
            <td><?= $row['time_served'] ?></td>
            <td><?= htmlspecialchars($row['locality']) ?></td>
            <td class="status-<?= htmlspecialchars($row['status']) ?>"><?= $row['status'] ?></td>
            <td>
                <button onclick="showEditServiceModal(this)">Edit</button>
                <button onclick="deleteService(<?= $row['service_id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <h2>Completed Services</h2>
    <table>
        <tr>
            <th>ID</th><th>User</th><th>Mechanic</th><th>Car</th><th>Service</th><th>Date</th><th>Locality</th><th>Status</th><th>Rating</th><th>Actions</th>
        </tr>
        <?php while($row = $completed->fetch_assoc()): ?>
        <tr data-service-id="<?= $row['service_id'] ?>" data-user-id="<?= $row['user_id'] ?>" data-mech-id="<?= $row['mech_id'] ?>" data-car-id="<?= $row['car_id'] ?>" data-service-name="<?= htmlspecialchars($row['service_name'], ENT_QUOTES) ?>" data-time-served="<?= $row['time_served'] ?>" data-locality="<?= htmlspecialchars($row['locality'], ENT_QUOTES) ?>" data-status="<?= $row['status'] ?>" data-rating="<?= $row['rating'] ?>">
            <td><?= $row['service_id'] ?></td>
            <td><?= htmlspecialchars($row['user_name']) ?></td>
            <td><?= htmlspecialchars($row['mech_name'] ?? '-') ?></td>
            <td><?= $row['car_id'] ?></td>
            <td><?= htmlspecialchars($row['service_name']) ?></td>
            <td><?= $row['time_served'] ?></td>
            <td><?= htmlspecialchars($row['locality']) ?></td>
            <td class="status-<?= htmlspecialchars($row['status']) ?>"><?= $row['status'] ?></td>
            <td><?= $row['rating'] !== null ? htmlspecialchars($row['rating']) : 'N/A' ?></td>
            <td>
                <button onclick="showEditServiceModal(this)">Edit</button>
                <button onclick="deleteService(<?= $row['service_id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>

    <!-- User Management Tab -->
    <div class="tab-section" id="users-section">
    <h2>Mechanics</h2>
    <button onclick="showAddMechModal()" style="margin-bottom:10px;">+ Add Mechanic</button>
    <table>
        <tr>
            <th>Name</th><th>Phone</th><th>Email</th><th>Locality</th><th>Avg Rating</th><th>Total Services</th><th>Actions</th>
        </tr>
        <?php while($mech = $mechs->fetch_assoc()): ?>
        <tr data-mech-id="<?= $mech['mech_id'] ?>" data-name="<?= htmlspecialchars($mech['name'], ENT_QUOTES) ?>" data-phone="<?= htmlspecialchars($mech['phone'], ENT_QUOTES) ?>" data-email="<?= htmlspecialchars($mech['email'], ENT_QUOTES) ?>" data-locality="<?= htmlspecialchars($mech['locality'], ENT_QUOTES) ?>">
            <td><?= htmlspecialchars($mech['name']) ?></td>
            <td><?= htmlspecialchars($mech['phone']) ?></td>
            <td><?= htmlspecialchars($mech['email']) ?></td>
            <td><?= htmlspecialchars($mech['locality']) ?></td>
            <td><?= $mech['avg_rating'] !== null ? number_format($mech['avg_rating'], 2) : 'N/A' ?></td>
            <td><?= $mech['total_services'] ?></td>
            <td>
                <button onclick="showEditMechModal(this)">Edit</button>
                <button onclick="deleteMech(<?= $mech['mech_id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <h2>Users</h2>
    <button onclick="showAddUserModal()" style="margin-bottom:10px;">+ Add User</button>
    <table>
        <tr>
            <th>Name</th><th>Phone</th><th>Email</th><th>Total Services</th><th>Actions</th>
        </tr>
        <?php while($user = $users->fetch_assoc()): ?>
        <tr data-user-id="<?= $user['user_id'] ?>" data-name="<?= htmlspecialchars($user['name'], ENT_QUOTES) ?>" data-phone="<?= htmlspecialchars($user['phone'], ENT_QUOTES) ?>" data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>">
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['phone']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= $user['total_services'] ?></td>
            <td>
                <button onclick="showEditUserModal(this)">Edit</button>
                <button onclick="deleteUser(<?= $user['user_id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <!-- Add/Edit User Modal -->
    <div id="user-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); align-items:center; justify-content:center; z-index:1000;">
      <div style="background:#fff; padding:30px; border-radius:12px; min-width:320px; max-width:90vw;">
        <h3 id="user-modal-title">Add User</h3>
        <form id="user-form">
          <input type="hidden" id="user_id" name="user_id">
          <div style="margin-bottom:10px;"><input type="text" id="user_name" name="name" placeholder="Name" required style="width:100%; padding:8px;"></div>
          <div style="margin-bottom:10px;"><input type="text" id="user_phone" name="phone" placeholder="Phone" required style="width:100%; padding:8px;"></div>
          <div style="margin-bottom:10px;"><input type="email" id="user_email" name="email" placeholder="Email" required style="width:100%; padding:8px;"></div>
          <div id="user_password_field" style="margin-bottom:10px;"><input type="password" id="user_password" name="password" placeholder="Password" style="width:100%; padding:8px;"></div>
          <button type="submit" style="margin-right:10px;">Save</button>
          <button type="button" onclick="closeUserModal()">Cancel</button>
        </form>
      </div>
    </div>
    <!-- Add/Edit Mechanic Modal -->
    <div id="mech-modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); align-items:center; justify-content:center; z-index:1000;">
      <div style="background:#fff; padding:30px; border-radius:12px; min-width:320px; max-width:90vw;">
        <h3 id="mech-modal-title">Add Mechanic</h3>
        <form id="mech-form">
          <input type="hidden" id="mech_id" name="mech_id">
          <div style="margin-bottom:10px;"><input type="text" id="mech_name" name="name" placeholder="Name" required style="width:100%; padding:8px;"></div>
          <div style="margin-bottom:10px;"><input type="text" id="mech_phone" name="phone" placeholder="Phone" required style="width:100%; padding:8px;"></div>
          <div style="margin-bottom:10px;"><input type="email" id="mech_email" name="email" placeholder="Email" required style="width:100%; padding:8px;"></div>
          <div style="margin-bottom:10px;"><input type="text" id="mech_locality" name="locality" placeholder="Locality" required style="width:100%; padding:8px;"></div>
          <div id="mech_password_field" style="margin-bottom:10px;"><input type="password" id="mech_password" name="password" placeholder="Password" style="width:100%; padding:8px;"></div>
          <button type="submit" style="margin-right:10px;">Save</button>
          <button type="button" onclick="closeMechModal()">Cancel</button>
        </form>
      </div>
    </div>
    <script>
    function showAddUserModal() {
      document.getElementById('user-modal-title').textContent = 'Add User';
      document.getElementById('user_id').value = '';
      document.getElementById('user_name').value = '';
      document.getElementById('user_phone').value = '';
      document.getElementById('user_email').value = '';
      document.getElementById('user_password').value = '';
      document.getElementById('user_password_field').style.display = 'block';
      document.getElementById('user-modal').style.display = 'flex';
    }
    function showEditUserModal(btn) {
      var tr = btn.closest('tr');
      document.getElementById('user-modal-title').textContent = 'Edit User';
      document.getElementById('user_id').value = tr.getAttribute('data-user-id');
      document.getElementById('user_name').value = tr.getAttribute('data-name');
      document.getElementById('user_phone').value = tr.getAttribute('data-phone');
      document.getElementById('user_email').value = tr.getAttribute('data-email');
      document.getElementById('user_password').value = '';
      document.getElementById('user_password_field').style.display = 'none';
      document.getElementById('user-modal').style.display = 'flex';
    }
    function closeUserModal() {
      document.getElementById('user-modal').style.display = 'none';
    }
    document.getElementById('user-form').onsubmit = function(e) {
      e.preventDefault();
      var user_id = document.getElementById('user_id').value;
      var name = document.getElementById('user_name').value;
      var phone = document.getElementById('user_phone').value;
      var email = document.getElementById('user_email').value;
      var password = document.getElementById('user_password').value;
      var url = user_id ? 'admin_edit_user.php' : 'admin_add_user.php';
      var formData = new FormData();
      if (user_id) {
        formData.append('user_id', user_id);
      }
      formData.append('name', name);
      formData.append('phone', phone);
      formData.append('email', email);
      if (!user_id) formData.append('password', password);
      fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert('User saved successfully!');
            location.reload();
          } else {
            alert(data.message || 'Failed to save user.');
          }
        });
    };
    function deleteUser(user_id) {
      if (!confirm('Are you sure you want to delete this user?')) return;
      fetch('admin_delete_user.php', {
        method: 'POST',
        body: new URLSearchParams({user_id})
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('User deleted.');
          location.reload();
        } else {
          alert(data.message || 'Failed to delete user.');
        }
      });
    }
    function showAddMechModal() {
      document.getElementById('mech-modal-title').textContent = 'Add Mechanic';
      document.getElementById('mech_id').value = '';
      document.getElementById('mech_name').value = '';
      document.getElementById('mech_phone').value = '';
      document.getElementById('mech_email').value = '';
      document.getElementById('mech_locality').value = '';
      document.getElementById('mech_password').value = '';
      document.getElementById('mech_password_field').style.display = 'block';
      document.getElementById('mech-modal').style.display = 'flex';
    }
    function showEditMechModal(btn) {
      var tr = btn.closest('tr');
      document.getElementById('mech-modal-title').textContent = 'Edit Mechanic';
      document.getElementById('mech_id').value = tr.getAttribute('data-mech-id');
      document.getElementById('mech_name').value = tr.getAttribute('data-name');
      document.getElementById('mech_phone').value = tr.getAttribute('data-phone');
      document.getElementById('mech_email').value = tr.getAttribute('data-email');
      document.getElementById('mech_locality').value = tr.getAttribute('data-locality');
      document.getElementById('mech_password').value = '';
      document.getElementById('mech_password_field').style.display = 'none';
      document.getElementById('mech-modal').style.display = 'flex';
    }
    function closeMechModal() {
      document.getElementById('mech-modal').style.display = 'none';
    }
    document.getElementById('mech-form').onsubmit = function(e) {
      e.preventDefault();
      var mech_id = document.getElementById('mech_id').value;
      var name = document.getElementById('mech_name').value;
      var phone = document.getElementById('mech_phone').value;
      var email = document.getElementById('mech_email').value;
      var locality = document.getElementById('mech_locality').value;
      var password = document.getElementById('mech_password').value;
      var url = mech_id ? 'admin_edit_mechanic.php' : 'admin_add_mechanic.php';
      var formData = new FormData();
      if (mech_id) {
        formData.append('mech_id', mech_id);
      }
      formData.append('name', name);
      formData.append('phone', phone);
      formData.append('email', email);
      formData.append('locality', locality);
      if (!mech_id) formData.append('password', password);
      fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert('Mechanic saved successfully!');
            location.reload();
          } else {
            alert(data.message || 'Failed to save mechanic.');
          }
        });
    };
    function deleteMech(mech_id) {
      if (!confirm('Are you sure you want to delete this mechanic?')) return;
      fetch('admin_delete_mechanic.php', {
        method: 'POST',
        body: new URLSearchParams({mech_id})
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('Mechanic deleted.');
          location.reload();
        } else {
          alert(data.message || 'Failed to delete mechanic.');
        }
      });
    }
    </script>
    </div>

    <!-- Payments Tab -->
    <div class="tab-section" id="payments-section">
    <h2>Withdrawals</h2>
    <table>
        <tr>
            <th>Mechanic</th><th>Amount</th><th>Method</th><th>Date</th><th>Actions</th>
        </tr>
        <?php while($w = $withdrawals->fetch_assoc()): ?>
        <tr data-withdrawal-id="<?= $w['withdrawal_id'] ?>" data-mech-id="<?= $w['mech_id'] ?>" data-mech-name="<?= htmlspecialchars($w['mech_name'], ENT_QUOTES) ?>" data-amount="<?= $w['withdrawal_amt'] ?>" data-method="<?= htmlspecialchars($w['withdrawal_method'], ENT_QUOTES) ?>" data-date="<?= $w['date'] ?>">
            <td><?= htmlspecialchars($w['mech_name']) ?></td>
            <td>KES <?= number_format($w['withdrawal_amt']) ?></td>
            <td><?= htmlspecialchars($w['withdrawal_method']) ?></td>
            <td><?= $w['date'] ?></td>
            <td>
                <button onclick="showEditWithdrawalModal(this)">Edit</button>
                <button onclick="deleteWithdrawal(<?= $w['withdrawal_id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    </div>
    </div>
</body>
</html> 