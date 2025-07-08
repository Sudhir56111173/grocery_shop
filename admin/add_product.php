<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

$name = $price = $quantity = $description = '';
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $description = trim($_POST['description']);
    $image = '';

    // Validate
    if (empty($name)) $errors[] = 'Product name is required.';
    if ($price <= 0) $errors[] = 'Price must be positive.';
    if ($quantity < 0) $errors[] = 'Quantity cannot be negative.';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== 4) {
        $img_name = $_FILES['image']['name'];
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_size = $_FILES['image']['size'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($img_ext, $allowed)) {
            $errors[] = 'Only JPG, JPEG, PNG, GIF images allowed.';
        } elseif ($img_size > 2*1024*1024) {
            $errors[] = 'Image size must be under 2MB.';
        } else {
            $image = uniqid('prod_', true) . "." . $img_ext;
            move_uploaded_file($img_tmp, "../assets/images/$image");
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('INSERT INTO products (name, price, quantity, description, image) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sdiss', $name, $price, $quantity, $description, $image);
        if ($stmt->execute()) {
            header('Location: manage_products.php?added=1');
            exit;
        } else {
            $errors[] = 'Failed to add product.';
        }
        $stmt->close();
    }
}
$page_title = 'Add Product';
include '../includes/header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <h2 class="mb-4">Add Product</h2>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach ($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Price</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($price); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($quantity); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($description); ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Image</label>
        <input type="file" name="image" class="form-control">
      </div>
      <button type="submit" class="btn btn-success w-100">Add Product</button>
      <a href="manage_products.php" class="btn btn-secondary w-100 mt-2">Back to Products</a>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
