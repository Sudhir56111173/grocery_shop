<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

$user_id = $_SESSION['user_id'];

// Fetch all orders for this customer
$sql = 'SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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

$page_title = 'Order History';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12">
    <h2>Order History</h2>
  </div>
</div>
<div class="row">
  <div class="col-md-7">
    <h4>Your Orders</h4>
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
          <td><?php echo $o['id']; ?></td>
          <td><?php echo htmlspecialchars($o['order_date']); ?></td>
          <td><?php echo ucfirst($o['status']); ?></td>
          <td>
            <a href="order_history.php?view=<?php echo $o['id']; ?>" class="btn btn-sm btn-info">View</a>
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
      <a href="order_history.php" class="btn btn-secondary">Back to Orders</a>
    <?php endif; ?>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
