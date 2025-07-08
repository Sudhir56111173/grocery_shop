<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

$user_id = $_SESSION['user_id'];
$success = $error = '';

// Fetch user info
$stmt = $conn->prepare('SELECT name, email, phone, address FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $address);
$stmt->fetch();
$stmt->close();

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    $new_phone = trim($_POST['phone']);
    $new_address = trim($_POST['address']);
    if (empty($new_name) || empty($new_phone) || empty($new_address)) {
        $error = 'All fields are required.';
    } else {
        $stmt = $conn->prepare('UPDATE users SET name=?, phone=?, address=? WHERE id=?');
        $stmt->bind_param('sssi', $new_name, $new_phone, $new_address, $user_id);
        if ($stmt->execute()) {
            $success = 'Profile updated successfully.';
            $_SESSION['user_name'] = $new_name;
            $name = $new_name;
            $phone = $new_phone;
            $address = $new_address;
        } else {
            $error = 'Failed to update profile.';
        }
        $stmt->close();
    }
}

$page_title = 'My Profile';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12">
    <h2>My Profile</h2>
  </div>
</div>
<?php if ($success): ?>
  <div class="alert alert-success"><?php echo $success; ?></div>
<?php elseif ($error): ?>
  <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<div class="row">
  <div class="col-md-6">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email (cannot change)</label>
        <input type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" disabled>
      </div>
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Address</label>
        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($address); ?>" required>
      </div>
      <button type="submit" class="btn btn-primary">Update Profile</button>
      <a href="customer_dashboard.php" class="btn btn-secondary ms-2">Back</a>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
