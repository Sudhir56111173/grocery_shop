<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_connection.php';

// Get total sales and orders
$res = $conn->query('SELECT SUM(oi.price * oi.quantity) AS total_sales, COUNT(DISTINCT o.id) AS total_orders FROM orders o JOIN order_items oi ON o.id = oi.order_id');
$stats = $res->fetch_assoc();
$total_sales = $stats['total_sales'] ?? 0;
$total_orders = $stats['total_orders'] ?? 0;

// Get sales for last 7 days
$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $conn->prepare('SELECT SUM(oi.price * oi.quantity) AS sales FROM orders o JOIN order_items oi ON o.id = oi.order_id WHERE DATE(o.order_date) = ?');
    $stmt->bind_param('s', $date);
    $stmt->execute();
    $stmt->bind_result($sales);
    $stmt->fetch();
    $sales_data[] = $sales ? floatval($sales) : 0;
    $stmt->close();
}
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $labels[] = date('D', strtotime("-$i days"));
}

$page_title = 'Sales Reports';
include '../includes/header.php';
?>
<div class="row mb-4">
  <div class="col-12">
    <h2>Sales Reports</h2>
  </div>
</div>
<div class="row mb-4">
  <div class="col-md-4">
    <div class="card text-center mb-3">
      <div class="card-body">
        <h5 class="card-title">Total Sales</h5>
        <p class="card-text display-6">₹<?php echo number_format($total_sales,2); ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card text-center mb-3">
      <div class="card-body">
        <h5 class="card-title">Total Orders</h5>
        <p class="card-text display-6"><?php echo $total_orders; ?></p>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Sales (Last 7 Days)</h5>
        <canvas id="salesChart" height="100"></canvas>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
const salesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Sales (₹)',
            data: <?php echo json_encode($sales_data); ?>,
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
<?php include '../includes/footer.php'; ?>
