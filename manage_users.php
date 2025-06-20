<?php
// manage_users.php
require_once 'db_connect.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only administrators may manage users
if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== 'Administrator') {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

// Fetch all users and their roles
$sql = "
  SELECT 
    u.user_id,
    u.name,
    u.email,
    u.phone,
    r.role_name
  FROM User u
  JOIN Role r ON u.role_id = r.role_id
  ORDER BY u.user_id
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #f5f5f5;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16px;
    }
    .header a {
      text-decoration: none;
      color: #007bff;
    }
    .header a:hover {
      text-decoration: underline;
    }
    .table-container {
      background: #fff;
      padding: 16px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
    }
    th, td {
      padding: 12px 8px;
      border: 1px solid #ddd;
      text-align: left;
    }
    th {
      background: #f0f0f0;
    }
    .actions a, .actions form {
      display: inline-block;
      margin-right: 4px;
    }
    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      font-size: 0.9rem;
      cursor: pointer;
      text-decoration: none;
      color: #fff;
    }
    .btn.edit {
      background: #28a745;
    }
    .btn.delete {
      background: #dc3545;
    }
    .btn.edit:hover {
      background: #218838;
    }
    .btn.delete:hover {
      background: #c82333;
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Manage Users</h1>
    <a href="admin_dashboard.php">← Back to Dashboard</a>
  </div>

  <div class="table-container">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Role</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($user = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $user['user_id'] ?></td>
              <td><?= htmlspecialchars($user['name']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= htmlspecialchars($user['phone']) ?></td>
              <td><?= htmlspecialchars($user['role_name']) ?></td>
              <td class="actions">
                <a href="edit_user.php?user_id=<?= $user['user_id'] ?>" class="btn edit">Edit</a>
                <form method="POST" action="delete_user.php" style="display:inline">
                  <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                  <button type="submit" class="btn delete" onclick="return confirm('Delete this user?')">
                    Delete
                  </button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align:center;">No users found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
