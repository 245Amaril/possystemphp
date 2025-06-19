<?php
require_once('config.php'); // Loads the session and $conn
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Simple POS System</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="header">
    <h1>Kasir TSU</h1>
    <span class="welcome">
      Hallo, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?> 
      <a href="logout.php">Logout</a>
    </span>
  </header>
  <nav>
    <a href="index.php?page=home" class="<?= $page == 'home' ? 'active' : ''; ?>">Home</a>
    <a href="index.php?page=cart" class="<?= $page == 'cart' ? 'active' : ''; ?>">Cart</a>
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <a href="index.php?page=supplies" class="<?= $page == 'supplies' ? 'active' : ''; ?>">Supplies</a>
    <?php endif; ?>
  </nav>
  <div class="container">
    <?php
      if ($page == 'home') {
          include('home.php');
      } elseif ($page == 'cart') {
          include('cart.php');
      } elseif ($page == 'supplies') {
          include('supplies.php');
      } elseif ($page == 'bill') {
          include('bill.php');
      } else {
          echo "<p>Page not found.</p>";
      }
    ?>
  </div>
</body>
</html>
