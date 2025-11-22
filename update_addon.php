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
    $cateringId     = intval($_POST['catering_id'] ?? 0);
    $supplyType     = trim($_POST['supply_type'] ?? '');
    $newSupplyType  = trim($_POST['new_supply_type'] ?? '');
    $newQuantity    = intval($_POST['new_quantity'] ?? 0);

    if ($cateringId <= 0 || $supplyType === '' || $newSupplyType === '' || $newQuantity <= 0) {
        echo "<script>alert('Please fill out all fields to update additional services.');history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT catering_id FROM catering_event WHERE catering_id = :cid AND caterer_id = :caterer_id");
    $stmt->execute([
        ':cid'        => $cateringId,
        ':caterer_id' => $caterer_id
    ]);
    $event = $stmt->fetch();

    if (!$event) {
        echo "<script>alert('Catering ID not found for this caterer.');history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM additional_supplies WHERE catering_id = :cid AND supply_type = :stype");
    $stmt->execute([
        ':cid'   => $cateringId,
        ':stype' => $supplyType
    ]);
    $supply = $stmt->fetch();

    if (!$supply) {
        echo "<script>alert('No matching additional service found for this Catering ID and Supply Type.');history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("UPDATE additional_supplies SET supply_type = :new_type, quantity = :new_qty WHERE catering_id = :cid AND supply_type = :stype");
    $stmt->execute([
        ':new_type' => $newSupplyType,
        ':new_qty'  => $newQuantity,
        ':cid'      => $cateringId,
        ':stype'    => $supplyType
    ]);

    $cid = urlencode($caterer_id);
    echo "<script>alert('Additional catering services have been updated for Catering ID {$cateringId}.');window.location.href='update_addon.php?caterer_id={$cid}';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Additional Catering Services</title>
  <link rel="stylesheet" href="style.css">
  <script>
    function confirmUpdate() {
      return confirm('Are you sure you want to update these additional services?');
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

    <h1>Update Additional Catering Services</h1>
    <h2>
      Caterer:
      <?php echo htmlspecialchars($caterer['first_name'] . ' ' . $caterer['last_name']); ?>
      (ID: <?php echo htmlspecialchars($caterer['id']); ?>)
    </h2>

    <form method="post" action="update_addon.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>" onsubmit="return confirmUpdate();">
      <input type="hidden" name="caterer_id" value="<?php echo htmlspecialchars($caterer_id); ?>">

      <label for="catering_id">Catering ID</label>
      <input type="number" id="catering_id" name="catering_id" required>

      <label for="supply_type">Current Supply Type</label>
      <input type="text" id="supply_type" name="supply_type" required>

      <label for="new_supply_type">New Supply Type</label>
      <input type="text" id="new_supply_type" name="new_supply_type" required>

      <label for="new_quantity">New Quantity</label>
      <input type="number" id="new_quantity" name="new_quantity" required>

      <button type="submit">Update Services</button>
    </form>
  </div>
</body>
</html>
