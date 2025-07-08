<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

$success = $error = '';
$low_stock_threshold = 5;

// Handle stock update
if (isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    if ($quantity < 0) {
        $error = 'Quantity cannot be negative.';
    } else {
        $stmt = $conn->prepare('UPDATE products SET quantity=? WHERE id=?');
        $stmt->bind_param('ii', $quantity, $product_id);
        if ($stmt->execute()) {
            $success = 'Stock updated successfully.';
        } else {
            $error = 'Failed to update stock.';
        }
        $stmt->close();
    }
}

// Fetch all products
$res = $conn->query('SELECT * FROM products ORDER BY id DESC');
$products = [];
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}

$page_title = 'Inventory Management';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12">
    <h2>Inventory Management</h2>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
  </div>
</div>
<div class="row">
  <div class="col-12">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Current Stock</th>
          <th>Update Stock</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <tr<?php if ($p['quantity'] <= $low_stock_threshold) echo ' class="table-warning"'; ?>>
          <td><?php echo $p['id']; ?></td>
          <td><?php echo htmlspecialchars($p['name']); ?></td>
          <td><?php echo $p['quantity']; ?><?php if ($p['quantity'] <= $low_stock_threshold) echo ' <span class="badge bg-danger">Low</span>'; ?></td>
          <td>
            <form method="post" class="d-flex align-items-center gap-2">
              <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
              <input type="number" name="quantity" class="form-control form-control-sm" value="<?php echo $p['quantity']; ?>" min="0" style="width:100px;">
              <button type="submit" class="btn btn-sm btn-primary">Update</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
