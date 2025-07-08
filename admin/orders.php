<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

// Handle status update
$success = $error = '';
if (isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $allowed = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare('UPDATE orders SET status=? WHERE id=?');
        $stmt->bind_param('si', $status, $order_id);
        if ($stmt->execute()) {
            $success = 'Order status updated.';
        } else {
            $error = 'Failed to update status.';
        }
        $stmt->close();
    }
}

// Fetch all orders with user info
$sql = 'SELECT o.*, u.name AS customer_name, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.id DESC';
$res = $conn->query($sql);
$orders = [];
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}

// Fetch order details if requested
$order_details = [];
if (isset($_GET['view'])) {
    $order_id = intval($_GET['view']);
    $sql = 'SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $order_details[] = $row;
    }
    $stmt->close();
}

$page_title = 'Orders Management';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12">
    <h2>Orders Management</h2>
    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
  </div>
</div>
<div class="row">
  <div class="col-md-7">
    <h4>All Orders</h4>
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Email</th>
          <th>Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td><?php echo $o['id']; ?></td>
          <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
          <td><?php echo htmlspecialchars($o['email']); ?></td>
          <td><?php echo htmlspecialchars($o['order_date']); ?></td>
          <td>
            <form method="post" class="d-flex align-items-center gap-2">
              <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
              <select name="status" class="form-select form-select-sm">
                <?php foreach(['pending','processing','completed','cancelled'] as $s): ?>
                  <option value="<?php echo $s; ?>" <?php if($o['status']===$s) echo 'selected'; ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-sm btn-primary">Update</button>
            </form>
          </td>
          <td>
            <a href="orders.php?view=<?php echo $o['id']; ?>" class="btn btn-sm btn-info">View</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="col-md-5">
    <?php if ($order_details): ?>
      <h4>Order Details (Order #<?php echo htmlspecialchars($_GET['view']); ?>)</h4>
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>Product</th>
            <th>Image</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php $total = 0; foreach ($order_details as $item): $subtotal = $item['quantity'] * $item['price']; $total += $subtotal; ?>
          <tr>
            <td><?php echo htmlspecialchars($item['name']); ?></td>
            <td><?php if($item['image']): ?><img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" style="max-width:50px;max-height:50px;" alt="Product Image"><?php endif; ?></td>
            <td><?php echo $item['quantity']; ?></td>
            <td>₹<?php echo number_format($item['price'],2); ?></td>
            <td>₹<?php echo number_format($subtotal,2); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="4" class="text-end">Total</th>
            <th>₹<?php echo number_format($total,2); ?></th>
          </tr>
        </tfoot>
      </table>
      <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
    <?php endif; ?>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
