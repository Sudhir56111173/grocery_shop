<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

// Handle add/edit/delete actions
$action = $_GET['action'] ?? '';
$edit_id = $_GET['edit'] ?? null;
$delete_id = $_GET['delete'] ?? null;
$errors = [];
$success = '';

// Show success if redirected from add_product.php
if (isset($_GET['added'])) {
    $success = 'Product added successfully!';
}

// Handle Delete
if ($delete_id) {
    // Get image filename to delete
    $stmt = $conn->prepare('SELECT image FROM products WHERE id = ?');
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    $stmt->bind_result($img);
    $stmt->fetch();
    $stmt->close();
    if ($img && file_exists("../assets/images/$img")) {
        unlink("../assets/images/$img");
    }
    $stmt = $conn->prepare('DELETE FROM products WHERE id = ?');
    $stmt->bind_param('i', $delete_id);
    if ($stmt->execute()) {
        $success = 'Product deleted successfully.';
    } else {
        $errors[] = 'Failed to delete product.';
    }
    $stmt->close();
}

// Handle Add/Edit
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

    // Add Product
    if (empty($errors) && !$edit_id) {
        $stmt = $conn->prepare('INSERT INTO products (name, price, quantity, description, image) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('sdiss', $name, $price, $quantity, $description, $image);
        if ($stmt->execute()) {
            $success = 'Product added successfully!';
        } else {
            $errors[] = 'Failed to add product.';
        }
        $stmt->close();
    }
    // Edit Product
    elseif (empty($errors) && $edit_id) {
        // If new image uploaded, get old image and delete
        if ($image) {
            $stmt = $conn->prepare('SELECT image FROM products WHERE id = ?');
            $stmt->bind_param('i', $edit_id);
            $stmt->execute();
            $stmt->bind_result($old_img);
            $stmt->fetch();
            $stmt->close();
            if ($old_img && file_exists("../assets/images/$old_img")) {
                unlink("../assets/images/$old_img");
            }
            $stmt = $conn->prepare('UPDATE products SET name=?, price=?, quantity=?, description=?, image=? WHERE id=?');
            $stmt->bind_param('sdissi', $name, $price, $quantity, $description, $image, $edit_id);
        } else {
            $stmt = $conn->prepare('UPDATE products SET name=?, price=?, quantity=?, description=? WHERE id=?');
            $stmt->bind_param('sdssi', $name, $price, $quantity, $description, $edit_id);
        }
        if ($stmt->execute()) {
            $success = 'Product updated successfully!';
        } else {
            $errors[] = 'Failed to update product.';
        }
        $stmt->close();
    }
}

// Fetch products
$products = [];
$res = $conn->query('SELECT * FROM products ORDER BY id DESC');
while ($row = $res->fetch_assoc()) {
    $products[] = $row;
}

// If editing, fetch product data
$edit_product = null;
if ($edit_id) {
    $stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_product = $result->fetch_assoc();
    $stmt->close();
}

$page_title = 'Manage Products';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12 d-flex justify-content-between align-items-center">
    <h2>Manage Products</h2>
    <a href="add_product.php" class="btn btn-success">Add Product</a>
  </div>
  <div class="col-12">
    <?php if ($errors): ?>
      <div class="alert alert-danger"><?php foreach ($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
  </div>
</div>
<div class="row">
  <div class="col-md-5">
    <h4><?php echo $edit_product ? 'Edit Product' : 'Add Product'; ?></h4>
    <form method="post" enctype="multipart/form-data">
      <div class="mb-2">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_product['name'] ?? ''); ?>" required>
      </div>
      <div class="mb-2">
        <label class="form-label">Price</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($edit_product['price'] ?? ''); ?>" required>
      </div>
      <div class="mb-2">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($edit_product['quantity'] ?? ''); ?>" required>
      </div>
      <div class="mb-2">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
      </div>
      <div class="mb-2">
        <label class="form-label">Image <?php if($edit_product && $edit_product['image']) echo '(leave blank to keep current)'; ?></label>
        <input type="file" name="image" class="form-control">
        <?php if($edit_product && $edit_product['image']): ?>
          <img src="../assets/images/<?php echo htmlspecialchars($edit_product['image']); ?>" alt="Product Image" class="img-thumbnail mt-2" style="max-width:100px;">
        <?php endif; ?>
      </div>
      <button type="submit" class="btn btn-success w-100"><?php echo $edit_product ? 'Update' : 'Add'; ?> Product</button>
      <?php if($edit_product): ?>
        <a href="manage_products.php" class="btn btn-secondary w-100 mt-2">Cancel Edit</a>
      <?php endif; ?>
    </form>
  </div>
  <div class="col-md-7">
    <h4>Product List</h4>
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Price</th>
          <th>Qty</th>
          <th>Image</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
        <tr>
          <td><?php echo $p['id']; ?></td>
          <td><?php echo htmlspecialchars($p['name']); ?></td>
          <td>₹<?php echo number_format($p['price'],2); ?></td>
          <td><?php echo $p['quantity']; ?></td>
          <td><?php if($p['image']): ?><img src="../assets/images/<?php echo htmlspecialchars($p['image']); ?>" alt="Image" style="max-width:60px;max-height:60px;"><?php endif; ?></td>
          <td>
            <a href="manage_products.php?edit=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
            <a href="manage_products.php?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?');">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
