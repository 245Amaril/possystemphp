<?php
// home.php
// Display summary sold item and total price.
$query = "SELECT COUNT(*) as total_carts, SUM(total) as total_sales FROM carts WHERE status='completed'";
$result = $conn->query($query);
$data = $result->fetch_assoc();
?>
<h2>Sales Summary</h2>
<p>Total Completed Transactions: <strong><?php echo $data['total_carts'] ?: 0; ?></strong></p>
<p>Total Sales Amount: <strong><?php echo number_format($data['total_sales'] ?: 0, 2); ?></strong></p>
<?php
// home.php – Sales Summary section at the top of Home
require_once('config.php'); // Ensure that the DB connection and session are active

// Process deletion of a bill if requested.
if (isset($_GET['delete_bill'])) {
    $bill_id = intval($_GET['delete_bill']);
    // Delete associated child rows first (from cart_items, and optionally from sales)
    $conn->query("DELETE FROM cart_items WHERE cart_id='$bill_id'");
    // Uncomment here if you maintain a sales table tied to carts:
    // $conn->query("DELETE FROM sales WHERE cart_id='$bill_id'");
    // Now delete the bill from the carts table
    $conn->query("DELETE FROM carts WHERE id='$bill_id'");
    header("Location: index.php?page=home");
    exit;
}
?>

<div class="home-summary mt-20 mb-20">
  <h2>Recent Bills Summary</h2>
  <table class="table">
    <thead>
      <tr>
        <th>Bill ID</th>
        <th>Date</th>
        <th>Total Price</th>
        <th>Sold Items Count</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php
      // Query completed bills, ordering by creation date (most recent first)
      $result = $conn->query("SELECT * FROM carts WHERE status='completed' ORDER BY created_at DESC");
      while ($bill = $result->fetch_assoc()):
          $bill_id = $bill['id'];
          // Get sold items count by summing the quantity from cart_items related to this bill.
          $queryItems = $conn->query("SELECT SUM(quantity) AS total_items FROM cart_items WHERE cart_id='$bill_id'");
          $data = $queryItems->fetch_assoc();
          $sold_items = $data['total_items'] ?? 0;
    ?>
      <tr>
        <td><?php echo $bill_id; ?></td>
        <td><?php echo $bill['created_at']; ?></td>
        <td><?php echo number_format($bill['total'], 2); ?></td>
        <td><?php echo $sold_items; ?></td>
        <td>
          <!-- View Bill button navigates to the detailed bill view -->
          <a href="index.php?page=bill&bill_id=<?php echo $bill_id; ?>" class="btn">
            View Bill
          </a>
          <!-- Delete Bill button allows removal of the bill record -->
          <a href="index.php?page=home&delete_bill=<?php echo $bill_id; ?>" class="btn" 
             onclick="return confirm('Are you sure you want to delete this bill?');">
            Delete Bill
          </a>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
