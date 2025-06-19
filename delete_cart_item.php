<?php
// delete_cart_item.php
include('config.php');

if (isset($_GET['id']) && isset($_GET['cart_id'])) {
    $id = $_GET['id'];
    $cart_id = $_GET['cart_id'];
    $conn->query("DELETE FROM cart_items WHERE id='$id'");
}

header("Location: index.php?page=cart");
exit;
?>
