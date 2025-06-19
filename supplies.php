<?php
// supplies_content.php

// Process form submissions

// Create new supply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_supply'])) {
    $name  = $conn->real_escape_string($_POST['name']);
    $price = $conn->real_escape_string($_POST['price']);
    $stock = $conn->real_escape_string($_POST['stock']);

    // Handle image upload if provided
    $image = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $image = basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $image);
    }
    $conn->query("INSERT INTO products (name, price, stock, image) VALUES ('$name', '$price', '$stock', '$image')");
    header("Location: index.php?page=supplies");
    exit;
}

// Update existing supply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_supply'])) {
    $id    = intval($_POST['id']);
    $name  = $conn->real_escape_string($_POST['name']);
    $price = $conn->real_escape_string($_POST['price']);
    $stock = $conn->real_escape_string($_POST['stock']);

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $image = basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $image);
        $conn->query("UPDATE products SET name='$name', price='$price', stock='$stock', image='$image' WHERE id='$id'");
    } else {
        $conn->query("UPDATE products SET name='$name', price='$price', stock='$stock' WHERE id='$id'");
    }
    header("Location: index.php?page=supplies");
    exit;
}

// Delete a supply item
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id='$id'");
    header("Location: index.php?page=supplies");
    exit;
}
?>

<!-- Start of Supplies Content -->
<div class="supplies-header">
  <h2>Manage Supplies</h2>
</div>

<!-- Form to add a new supply -->
<div class="supplies-form">
  <form method="post" action="index.php?page=supplies" enctype="multipart/form-data">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" class="form-control" required>

    <label for="price">Price:</label>
    <input type="number" id="price" name="price" class="form-control" step="0.01" required>

    <label for="stock">Stock:</label>
    <input type="number" id="stock" name="stock" class="form-control" required>

    <label for="image">Image:</label>
    <input type="file" id="image" name="image" class="form-control">

    <input type="submit" name="create_supply" class="btn" value="Add Supply">
  </form>
</div>

<!-- Table listing existing supplies -->
<div class="supplies-table">
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Image</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $result = $conn->query("SELECT * FROM products");
    while ($row = $result->fetch_assoc()):
    ?>
      <tr>
        <form method="post" action="index.php?page=supplies" enctype="multipart/form-data">
          <td>
            <?php echo $row['id']; ?>
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
          </td>
          <td>
            <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" class="form-control">
          </td>
          <td>
            <input type="number" name="price" step="0.01" value="<?php echo $row['price']; ?>" class="form-control">
          </td>
          <td>
            <input type="number" name="stock" value="<?php echo $row['stock']; ?>" class="form-control">
          </td>
          <td>
            <?php if (!empty($row['image'])): ?>
              <img src="uploads/<?php echo $row['image']; ?>" width="50" alt="Image"><br>
            <?php endif; ?>
            <input type="file" name="image" class="form-control">
          </td>
          <td>
            <input type="submit" name="update_supply" class="btn" value="Update">
            <a href="index.php?page=supplies&delete=<?php echo $row['id']; ?>" class="btn" onclick="return confirm('Delete this item?');">Delete</a>
          </td>
        </form>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
<!-- End of Supplies Content -->
