<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart = $_SESSION['cart'];
$cart_success = '';

// Handle quantity update
if (isset($_POST['update_qty'], $_POST['product_id'])) {
    $pid = intval($_POST['product_id']);
    $qty = max(1, intval($_POST['quantity']));
    if (isset($cart[$pid])) {
        // Check stock
        $stmt = $conn->prepare('SELECT quantity FROM products WHERE id = ?');
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $stmt->bind_result($stock);
        $stmt->fetch();
        $stmt->close();
        if ($qty <= $stock) {
            $_SESSION['cart'][$pid]['quantity'] = $qty;
            $cart_success = 'Quantity updated.';
        } else {
            $cart_success = 'Not enough stock available.';
        }
    }
    $cart = $_SESSION['cart'];
}

// Handle remove item
if (isset($_POST['remove'], $_POST['product_id'])) {
    $pid = intval($_POST['product_id']);
    if (isset($_SESSION['cart'][$pid])) {
        unset($_SESSION['cart'][$pid]);
        $cart_success = 'Item removed from cart.';
    }
    $cart = $_SESSION['cart'];
}

// Calculate total
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

$page_title = 'Your Cart';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <h2>Your Cart</h2>
    <a href="customer_dashboard.php" class="btn btn-secondary">Continue Shopping</a>
  </div>
</div>
<?php if ($cart_success): ?>
  <div class="alert alert-info"><?php echo htmlspecialchars($cart_success); ?></div>
<?php endif; ?>
<div class="row">
  <div class="col-12">
    <?php if (empty($cart)): ?>
      <div class="alert alert-warning">Your cart is empty.</div>
    <?php else: ?>
      <form method="post">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr>
              <th>Product</th>
              <th>Image</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Subtotal</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['name']); ?></td>
              <td><?php if($item['image']): ?><img src="../assets/images/<?php echo htmlspecialchars($item['image']); ?>" style="max-width:60px;max-height:60px;" alt="Product Image"><?php endif; ?></td>
              <td>₹<?php echo number_format($item['price'],2); ?></td>
              <td>
                <form method="post" class="d-flex align-items-center gap-2">
                  <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                  <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" class="form-control form-control-sm" style="width:80px;">
                  <button type="submit" name="update_qty" class="btn btn-sm btn-primary">Update</button>
                </form>
              </td>
              <td>₹<?php echo number_format($item['price'] * $item['quantity'],2); ?></td>
              <td>
                <form method="post">
                  <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                  <button type="submit" name="remove" class="btn btn-sm btn-danger" onclick="return confirm('Remove this item?');">Remove</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-end">Total</th>
              <th colspan="2">₹<?php echo number_format($total,2); ?></th>
            </tr>
          </tfoot>
        </table>
      </form>
      <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    <?php endif; ?>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
