<?php
// bill.php
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['bill_id'])) {
    echo "Bill ID not provided.";
    exit;
}

$bill_id = intval($_GET['bill_id']);

// Retrieve the completed cart (bill)
$query = "SELECT * FROM carts WHERE id = '$bill_id' AND status = 'completed'";
$result = $conn->query($query);
if ($result->num_rows === 0) {
    echo "Invalid bill ID or bill not completed.";
    exit;
}
$bill = $result->fetch_assoc();

// Retrieve cart items with product details
$queryItems = "SELECT ci.*, p.name FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id = '$bill_id'";
$resultItems = $conn->query($queryItems);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill #<?php echo $bill['id']; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="bill-container">
    <div class="bill-header">
        <h1>Simple POS System</h1>
        <p>Bill #<?php echo $bill['id']; ?></p>
        <p>Date: <?php echo $bill['created_at']; ?></p>
    </div>
    <div class="bill-details">
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $total = 0;
            while ($item = $resultItems->fetch_assoc()) {
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
                echo "<tr>
                        <td>" . htmlspecialchars($item['name']) . "</td>
                        <td>" . number_format($item['price'], 2) . "</td>
                        <td>" . $item['quantity'] . "</td>
                        <td>" . number_format($subtotal, 2) . "</td>
                      </tr>";
            }
            ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong><?php echo number_format($total, 2); ?></strong></td>
            </tr>
            </tbody>
        </table>
    </div>
    
    <!-- Print and Back Buttons -->
    <a href="javascript:window.print()" class="btn-print">Print</a>
    <a href="index.php" class="btn">Back</a>
</div>
</body>
</html>
