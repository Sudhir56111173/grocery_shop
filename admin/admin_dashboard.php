<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
$page_title = 'Admin Dashboard';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Admin)</h2>
    <p class="lead">Manage your grocery shop from the dashboard below.</p>
  </div>
</div>
<div class="row g-4">
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body text-center">
        <h5 class="card-title">Products</h5>
        <p class="card-text">Add, edit, or delete products.</p>
        <a href="manage_products.php" class="btn btn-primary">Manage Products</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body text-center">
        <h5 class="card-title">Orders</h5>
        <p class="card-text">View and manage customer orders.</p>
        <a href="orders.php" class="btn btn-primary">View Orders</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body text-center">
        <h5 class="card-title">Inventory</h5>
        <p class="card-text">Monitor and update stock levels.</p>
        <a href="inventory.php" class="btn btn-primary">Inventory</a>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body text-center">
        <h5 class="card-title">Reports</h5>
        <p class="card-text">View sales analytics and export data.</p>
        <a href="reports.php" class="btn btn-primary">Reports</a>
      </div>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
