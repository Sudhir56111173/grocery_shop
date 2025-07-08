<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

// Get cart
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Get user info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT name, email, phone, address FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $address);
$stmt->fetch();
$stmt->close();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Start transaction
    $conn->begin_transaction();
    try {
        // Insert order
        $stmt = $conn->prepare('INSERT INTO orders (user_id, order_date, status) VALUES (?, NOW(), "pending")');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();
        // Insert order items and update stock
        foreach ($cart as $item) {
            // Check stock
            $stmt = $conn->prepare('SELECT quantity FROM products WHERE id = ? FOR UPDATE');
            $stmt->bind_param('i', $item['id']);
            $stmt->execute();
            $stmt->bind_result($stock);
            $stmt->fetch();
            $stmt->close();
            if ($stock < $item['quantity']) {
                throw new Exception('Not enough stock for ' . htmlspecialchars($item['name']));
            }
            // Insert order item
            $stmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('iiid', $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
            $stmt->close();
            // Update stock
            $stmt = $conn->prepare('UPDATE products SET quantity = quantity - ? WHERE id = ?');
            $stmt->bind_param('ii', $item['quantity'], $item['id']);
            $stmt->execute();
            $stmt->close();
        }
        $conn->commit();
        unset($_SESSION['cart']);
        $success = 'Order placed successfully! <a href="order_history.php">View your orders</a>.';
    } catch (Exception $e) {
        $conn->rollback();
        $error = 'Order failed: ' . $e->getMessage();
    }
}

// Calculate total
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$page_title = 'Checkout';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12">
    <h2>Checkout</h2>
  </div>
</div>
<?php if ($success): ?>
  <div class="alert alert-success"><?php echo $success; ?></div>
<?php elseif ($error): ?>
  <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if (!$success): ?>
<div class="row mb-4">
  <div class="col-md-6">
    <h5>Shipping Information</h5>
    <ul class="list-group mb-3">
      <li class="list-group-item"><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></li>
      <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></li>
      <li class="list-group-item"><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></li>
      <li class="list-group-item"><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></li>
    </ul>
  </div>
  <div class="col-md-6">
    <h5>Order Summary</h5>
    <table class="table table-bordered align-middle">
      <thead class="table-light">
        <tr>
          <th>Product</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cart as $item): ?>
        <tr>
          <td><?php echo htmlspecialchars($item['name']); ?></td>
          <td><?php echo $item['quantity']; ?></td>
          <td>₹<?php echo number_format($item['price'],2); ?></td>
          <td>₹<?php echo number_format($item['price'] * $item['quantity'],2); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <th colspan="3" class="text-end">Total</th>
          <th>₹<?php echo number_format($total,2); ?></th>
        </tr>
      </tfoot>
    </table>
    <form method="post">
      <button type="submit" name="place_order" class="btn btn-success w-100">Place Order</button>
      <a href="cart.php" class="btn btn-secondary w-100 mt-2">Back to Cart</a>
    </form>
  </div>
</div>
<?php endif; ?>
<?php include '../includes/footer.php'; ?>
