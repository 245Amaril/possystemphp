<?php
// cart.php
include('config.php');

$worker_id = $_SESSION['user_id'];
$cart_id = 0;

// Check if there is an open (pending) cart for the worker
$result = $conn->query("SELECT id FROM carts WHERE worker_id='$worker_id' AND status='pending'");
if ($result->num_rows == 0) {
    $conn->query("INSERT INTO carts (worker_id, total, status) VALUES ('$worker_id', 0, 'pending')");
    $cart_id = $conn->insert_id;
} else {
    $cart = $result->fetch_assoc();
    $cart_id = $cart['id'];
}

// Add new item to the cart
if (isset($_POST['add_item'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Get product details
    $pquery = $conn->query("SELECT price FROM products WHERE id='$product_id'");
    $product = $pquery->fetch_assoc();
    $price = $product['price'];
    
    // If the item already exists in this cart, update the quantity; otherwise, insert new
    $check = $conn->query("SELECT id, quantity FROM cart_items WHERE cart_id='$cart_id' AND product_id='$product_id'");
    if ($check->num_rows > 0) {
        $item = $check->fetch_assoc();
        $new_quantity = $item['quantity'] + $quantity;
        $conn->query("UPDATE cart_items SET quantity='$new_quantity' WHERE id='{$item['id']}'");
    } else {
        $conn->query("INSERT INTO cart_items (cart_id, product_id, quantity, price) VALUES ('$cart_id', '$product_id', '$quantity', '$price')");
    }
    header("Location: index.php?page=cart");
    exit;
}

// Update an item’s quantity (editable)
if (isset($_POST['update_item'])) {
    $cart_item_id = intval($_POST['cart_item_id']);
    $new_quantity = intval($_POST['new_quantity']);
    $conn->query("UPDATE cart_items SET quantity='$new_quantity' WHERE id='$cart_item_id'");
    header("Location: index.php?page=cart");
    exit;
}

// Finalize cart and redirect to bill
if (isset($_POST['finalize_cart'])) {
    // Calculate the total for the cart
    $resultTotal = $conn->query("SELECT SUM(quantity * price) as total FROM cart_items WHERE cart_id='$cart_id'");
    $dataTotal = $resultTotal->fetch_assoc();
    $total = $dataTotal['total'] ?: 0;
    
    // Mark the cart as completed and update total
    $conn->query("UPDATE carts SET total='$total', status='completed' WHERE id='$cart_id'");
    
    // Process each cart item: record sale and update product stock safely
    $items = $conn->query("SELECT * FROM cart_items WHERE cart_id='$cart_id'");
    while ($item = $items->fetch_assoc()) {
        $prod_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        // Insert sale record
        $conn->query("INSERT INTO sales (product_id, quantity, sale_price) VALUES ('$prod_id', '$quantity', '$price')");
        
        // Update product stock so that it never goes below zero.
        $conn->query("UPDATE products SET stock = GREATEST(stock - $quantity, 0) WHERE id='$prod_id'");
    }
    
    // Redirect to the bill page
    header("Location: bill.php?bill_id=" . $cart_id);
    exit;
}
?>

<h2>Cart (Cart ID: <?php echo $cart_id; ?>)</h2>

<!-- Form to add item -->
<form method="post">
    <label>Select Product:</label>
    <select name="product_id" class="form-control" required>
        <option value="">-- Select Product --</option>
        <?php
        $prods = $conn->query("SELECT id, name, price, stock FROM products");
        while ($row = $prods->fetch_assoc()) {
            // Check if stock is 0
            $disabled = ($row['stock'] == 0) ? 'disabled' : '';
            // Optionally, add a label to indicate out-of-stock items.
            $name = ($row['stock'] == 0) ? $row['name'] . " (Out of Stock)" : $row['name'];
            echo "<option value='{$row['id']}' $disabled>{$name} (\${$row['price']}) - Stock: {$row['stock']}</option>";
        }
        ?>
    </select>
    <label>Quantity:</label>
    <input type="number" name="quantity" class="form-control" value="1" min="1" required>
    <input type="submit" name="add_item" class="btn" value="Add to Cart">
</form>

<!-- Display current cart items with inline editable quantity -->
<table class="table">
    <tr>
        <th>Item</th>
        <th>Price</th>
        <th>Quantity (Editable)</th>
        <th>Subtotal</th>
        <th>Action</th>
    </tr>
    <?php
    $cart_items = $conn->query("SELECT ci.id as cart_item_id, p.name, p.price, ci.quantity FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.cart_id='$cart_id'");
    $total = 0;
    while($item = $cart_items->fetch_assoc()){
        $subtotal = $item['price'] * $item['quantity'];
        $total += $subtotal;
        echo "<tr>
               <td>" . htmlspecialchars($item['name']) . "</td>
               <td>" . number_format($item['price'],2) . "</td>
               <td>
                   <form method='post' style='display:inline-block;'>
                       <input type='hidden' name='cart_item_id' value='{$item['cart_item_id']}'>
                       <input type='number' name='new_quantity' value='{$item['quantity']}' min='1' class='form-control' style='width:80px; display:inline-block;'>
                       <input type='submit' name='update_item' class='btn' value='Edit'>
                   </form>
               </td>
               <td>" . number_format($subtotal,2) . "</td>
               <td><a class='btn' href='delete_cart_item.php?id={$item['cart_item_id']}&cart_id={$cart_id}' onclick=\"return confirm('Delete this item?');\">Delete</a></td>
              </tr>";
    }
    ?>
    <tr>
        <td colspan="3" align="right"><strong>Total:</strong></td>
        <td colspan="2"><strong><?php echo number_format($total,2); ?></strong></td>
    </tr>
</table>

<!-- Form to finalize the cart -->
<form method="post">
    <input type="submit" name="finalize_cart" class="btn" value="Finalize & Save Cart">
</form>
