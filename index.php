<?php
session_start();
require_once 'includes/db_connection.php';

// Fetch all products
$products = [];
$res = $conn->query('SELECT * FROM products ORDER BY id DESC');
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}

// Determine user state for nav
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? null;
$user_name = $_SESSION['user_name'] ?? '';

$page_title = 'Grocery Shop - Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Grocery Shop</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <?php if (!$is_logged_in): ?>
          <li class="nav-item"><a class="nav-link" href="auth/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="auth/register.php">Register</a></li>
        <?php else: ?>
          <?php if ($user_role === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="admin/admin_dashboard.php">Admin Dashboard</a></li>
          <?php elseif ($user_role === 'customer'): ?>
            <li class="nav-item"><a class="nav-link" href="customer/customer_dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="customer/cart.php">Cart</a></li>
          <?php endif; ?>
          <li class="nav-item"><span class="nav-link">Hi, <?php echo htmlspecialchars($user_name); ?></span></li>
          <li class="nav-item"><a class="nav-link text-danger" href="auth/logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<header class="bg-light py-5 mb-4 shadow-sm">
  <div class="container text-center">
    <h1 class="display-4 fw-bold mb-3">Welcome to Grocery Shop</h1>
    <p class="lead mb-4">Your one-stop shop for fresh groceries, delivered fast and easy.</p>
    <?php if (!$is_logged_in): ?>
      <a href="auth/register.php" class="btn btn-success btn-lg me-2">Get Started</a>
      <a href="auth/login.php" class="btn btn-outline-primary btn-lg">Login</a>
    <?php endif; ?>
  </div>
</header>
<main class="container">
  <h2 class="mb-4 text-center">Our Groceries</h2>
  <div class="row g-4">
    <?php if (empty($products)): ?>
      <div class="col-12"><div class="alert alert-warning text-center">No groceries available at the moment.</div></div>
    <?php endif; ?>
    <?php foreach ($products as $p): ?>
      <div class="col-md-4 col-lg-3">
        <div class="card h-100 shadow-sm product-card">
          <?php if($p['image']): ?>
            <img src="assets/images/<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top" alt="Product Image" style="max-height:180px;object-fit:contain;">
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title fw-semibold"><?php echo htmlspecialchars($p['name']); ?></h5>
            <p class="card-text text-success fs-5 fw-bold mb-2">₹<?php echo number_format($p['price'],2); ?></p>
            <p class="card-text small text-muted mb-3"><?php echo htmlspecialchars($p['description']); ?></p>
            <div class="mt-auto">
              <?php if ($is_logged_in && $user_role === 'customer'): ?>
                <form method="post" action="customer/customer_dashboard.php">
                  <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                  <input type="hidden" name="quantity" value="1">
                  <button type="submit" name="add_to_cart" class="btn btn-success w-100" <?php if($p['quantity']<=0) echo 'disabled'; ?>>Add to Cart</button>
                </form>
              <?php else: ?>
                <span class="badge bg-secondary">Login to buy</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</main>
<footer class="footer mt-5 py-4 bg-primary text-white text-center">
  <div class="container">
    <span>&copy; <?php echo date('Y'); ?> Grocery Shop. All rights reserved.</span>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>
