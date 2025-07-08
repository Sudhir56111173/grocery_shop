<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

// Handle search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$products = [];
if ($search) {
    $stmt = $conn->prepare('SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC');
    $like = "%$search%";
    $stmt->bind_param('s', $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
} else {
    $res = $conn->query('SELECT * FROM products ORDER BY id DESC');
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}

// Handle add to cart
$cart_success = '';
if (isset($_POST['add_to_cart'], $_POST['product_id'])) {
    $pid = intval($_POST['product_id']);
    $qty = max(1, intval($_POST['quantity'] ?? 1));
    // Fetch product
    $stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    if ($product && $product['quantity'] >= $qty) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$pid] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $qty
            ];
        }
        $cart_success = 'Product added to cart!';
    } else {
        $cart_success = 'Product not available in requested quantity.';
    }
}

$page_title = 'Customer Dashboard';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
    <div>
      <a href="cart.php" class="btn btn-primary">Cart</a>
      <a href="order_history.php" class="btn btn-secondary">Order History</a>
      <a href="profile.php" class="btn btn-outline-dark">Profile</a>
      <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>
</div>
<div class="row mb-3">
  <div class="col-md-6">
    <form class="d-flex" method="get">
      <input class="form-control me-2" type="search" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
      <button class="btn btn-outline-success" type="submit">Search</button>
    </form>
  </div>
</div>
<?php if ($cart_success): ?>
  <div class="alert alert-info"><?php echo htmlspecialchars($cart_success); ?></div>
<?php endif; ?>
<div class="row">
  <?php if (empty($products)): ?>
    <div class="col-12"><div class="alert alert-warning">No products found.</div></div>
  <?php endif; ?>
  <?php foreach ($products as $p): ?>
    <div class="col-md-4 mb-4">
      <div class="card h-100">
        <?php if($p['image']): ?>
          <img src="../assets/images/<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top" alt="Product Image" style="max-height:200px;object-fit:contain;">
        <?php endif; ?>
        <div class="card-body d-flex flex-column">
          <h5 class="card-title"><?php echo htmlspecialchars($p['name']); ?></h5>
          <p class="card-text">₹<?php echo number_format($p['price'],2); ?></p>
          <form method="post" class="mt-auto">
            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
            <div class="input-group mb-2">
              <input type="number" name="quantity" class="form-control" value="1" min="1" max="<?php echo $p['quantity']; ?>">
              <span class="input-group-text">in stock: <?php echo $p['quantity']; ?></span>
            </div>
            <button type="submit" name="add_to_cart" class="btn btn-success w-100" <?php if($p['quantity']<=0) echo 'disabled'; ?>>Add to Cart</button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include '../includes/footer.php'; ?>
