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
    $clientId    = intval($_POST['client_id'] ?? 0);
    $cateringId  = intval($_POST['catering_id'] ?? 0);

    if ($clientId <= 0 || $cateringId <= 0) {
        echo "<script>alert('Please enter both Client ID and Catering ID.');history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT catering_id FROM catering_event WHERE catering_id = :cid AND client_id = :client_id AND caterer_id = :caterer_id");
    $stmt->execute([
        ':cid'        => $cateringId,
        ':client_id'  => $clientId,
        ':caterer_id' => $caterer_id
    ]);
    $event = $stmt->fetch();

    if (!$event) {
        echo "<script>alert('Catering ID does not exist for this client and caterer.');history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM additional_supplies WHERE catering_id = :cid");
    $stmt->execute([':cid' => $cateringId]);

    $stmt = $pdo->prepare("DELETE FROM catering_event WHERE catering_id = :cid");
    $stmt->execute([':cid' => $cateringId]);

    $cid = urlencode($caterer_id);
    echo "<script>alert('Catering Event {$cateringId} for Client {$clientId} has been cancelled.');window.location.href='cancel_catering.php?caterer_id={$cid}';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cancel Client Catering Event</title>
  <link rel="stylesheet" href="style.css">
  <script>
    function confirmCancel() {
      return confirm('Are you sure you want to cancel this catering event?');
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

    <h1>Cancel a Client's Catering Event</h1>
    <h2>
      Caterer:
      <?php echo htmlspecialchars($caterer['first_name'] . ' ' . $caterer['last_name']); ?>
      (ID: <?php echo htmlspecialchars($caterer['id']); ?>)
    </h2>

    <form method="post" action="cancel_catering.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>" onsubmit="return confirmCancel();">
      <input type="hidden" name="caterer_id" value="<?php echo htmlspecialchars($caterer_id); ?>">

      <label for="client_id">Client ID</label>
      <input type="number" id="client_id" name="client_id" required>

      <label for="catering_id">Catering ID</label>
      <input type="number" id="catering_id" name="catering_id" required>

      <button type="submit">Cancel Event</button>
    </form>
  </div>
</body>
</html>
