<?php
require 'db.php';

$caterer_id = $_GET['caterer_id'] ?? ($_POST['caterer_id'] ?? '');
if ($caterer_id === '') {
    echo "<script>alert('No caterer ID provided. Please log in again.');window.location.href='index.html';</script>";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM caterer WHERE id = :id");
$stmt->execute([':id' => $caterer_id]);
$caterer = $stmt->fetch();
if (!$caterer) {
    echo "<script>alert('Caterer not found.');window.location.href='index.html';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cateringId = intval($_POST['catering_id'] ?? 0);
    $supplyType = trim($_POST['supply_type'] ?? '');
    $quantity   = intval($_POST['quantity'] ?? 0);

    if ($cateringId <= 0 || $supplyType === '' || $quantity <= 0) {
        echo "<script>alert('Please fill out all fields for additional services.');history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT catering_id FROM catering_event WHERE catering_id = :cid AND caterer_id = :caterer_id");
    $stmt->execute([
        ':cid'        => $cateringId,
        ':caterer_id' => $caterer_id
    ]);
    $event = $stmt->fetch();

    if (!$event) {
        echo "<script>alert('Catering information cannot be found for that ID.');history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO additional_supplies (catering_id, supply_type, quantity) VALUES (:cid, :stype, :qty)");
    $stmt->execute([
        ':cid'   => $cateringId,
        ':stype' => $supplyType,
        ':qty'   => $quantity
    ]);

    $cid = urlencode($caterer_id);
    echo "<script>alert('Additional catering services have been requested for Catering ID {$cateringId}.');window.location.href='request_addon.php?caterer_id={$cid}';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Request Additional Catering Services</title>
  <link rel="stylesheet" href="style.css">
  <script>
    function confirmRequest() {
      return confirm('Are you sure you want to request these additional services?');
    }
  </script>
</head>
<body>
  <div class="container">
    <nav class="nav">
      <a href="search_caterer.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>">Search Accounts</a>
      <a href="book_catering.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>">Book Event</a>
      <a href="cancel_catering.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>">Cancel Event</a>
      <a href="request_addon.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>">Request AddOn</a>
      <a href="update_addon.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>">Update AddOn</a>
      <a href="create_client.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>">Create Client</a>
      <a href="index.html">Logout</a>
    </nav>

    <h1>Request Additional Catering Services</h1>
    <h2>
      Caterer:
      <?php echo htmlspecialchars($caterer['first_name'] . ' ' . $caterer['last_name']); ?>
      (ID: <?php echo htmlspecialchars($caterer['id']); ?>)
    </h2>

    <form method="post" action="request_addon.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>" onsubmit="return confirmRequest();">
      <input type="hidden" name="caterer_id" value="<?php echo htmlspecialchars($caterer_id); ?>">

      <label for="catering_id">Catering ID</label>
      <input type="number" id="catering_id" name="catering_id" required>

      <label for="supply_type">Supply Type</label>
      <input type="text" id="supply_type" name="supply_type" required>

      <label for="quantity">Quantity</label>
      <input type="number" id="quantity" name="quantity" required>

      <button type="submit">Request Services</button>
    </form>
  </div>
</body>
</html>
