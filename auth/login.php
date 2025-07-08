<?php
session_start();
require_once '../includes/db_connection.php';

$email = $password = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        $error = 'Please enter a valid email and password.';
    } else {
        $stmt = $conn->prepare('SELECT id, name, email, password, role FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/admin_dashboard.php');
                } else {
                    header('Location: ../customer/customer_dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
        $stmt->close();
    }
}
$page_title = 'Login';
include '../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <h2 class="mb-4">Login</h2>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" novalidate>
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
      <div class="mt-3 text-center">
        Don't have an account? <a href="register.php">Register</a>
      </div>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
