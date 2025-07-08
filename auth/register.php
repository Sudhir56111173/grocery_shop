<?php
session_start();
require_once '../includes/db_connection.php';

$name = $email = $password = $phone = $address = $role = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = ($_POST['role'] === 'admin') ? 'admin' : 'customer';

    // Validate
    if (empty($name)) $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if (empty($phone)) $errors[] = 'Phone is required.';
    if (empty($address)) $errors[] = 'Address is required.';

    // Check if email exists
    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered.';
        }
        $stmt->close();
    }

    // Insert user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, password, role, phone, address) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $name, $email, $hashed_password, $role, $phone, $address);
        if ($stmt->execute()) {
            $success = 'Registration successful! You can now <a href="login.php">login</a>.';
            $name = $email = $password = $phone = $address = '';
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
}
$page_title = 'Register';
include '../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <h2 class="mb-4">User Registration</h2>
    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <?php foreach ($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?>
      </div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="post" novalidate>
      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required minlength="6">
      </div>
      <div class="mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
      </div>
      <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
      </div>
      <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-select" id="role" name="role">
          <option value="customer" <?php if($role==='customer') echo 'selected'; ?>>Customer</option>
          <option value="admin" <?php if($role==='admin') echo 'selected'; ?>>Admin</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100">Register</button>
      <div class="mt-3 text-center">
        Already have an account? <a href="login.php">Login</a>
      </div>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
