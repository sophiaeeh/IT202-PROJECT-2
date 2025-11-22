<?php
require 'db.php';

$caterer_id = $_GET['caterer_id'] ?? '';

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

$sql = "
SELECT
  ce.catering_id,
  c.client_id,
  c.first_name AS client_first,
  c.last_name  AS client_last,
  ci.street_number,
  ci.street_name,
  ci.city,
  ci.state,
  ci.zip_code,
  ci.phone      AS client_phone,
  ce.event_date,
  ce.food_order,
  s.supply_type,
  s.quantity
FROM catering_event ce
JOIN client       c  ON ce.client_id = c.client_id
JOIN client_info  ci ON c.client_id = ci.client_id
LEFT JOIN additional_supplies s ON ce.catering_id = s.catering_id
WHERE ce.caterer_id = :cid
ORDER BY ce.catering_id, c.client_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':cid' => $caterer_id]);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Caterer Accounts</title>
  <link rel="stylesheet" href="style.css">
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

    <h1>Caterer Accounts</h1>
    <h2>
      <?php echo htmlspecialchars($caterer['first_name'] . ' ' . $caterer['last_name']); ?>
      (ID: <?php echo htmlspecialchars($caterer['id']); ?>)
    </h2>

    <?php if (empty($rows)): ?>
      <p>No client accounts found for this caterer yet.</p>
    <?php else: ?>
      <table class="accounts-table">
        <thead>
          <tr>
            <th>Catering ID</th>
            <th>Client ID</th>
            <th>Client Name</th>
            <th>Address</th>
            <th>City</th>
            <th>State</th>
            <th>Zip</th>
            <th>Phone</th>
            <th>Event Date</th>
            <th>Food Order</th>
            <th>Supply Type</th>
            <th>Quantity</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?php echo htmlspecialchars($r['catering_id']); ?></td>
            <td><?php echo htmlspecialchars($r['client_id']); ?></td>
            <td><?php echo htmlspecialchars($r['client_first'] . ' ' . $r['client_last']); ?></td>
            <td><?php echo htmlspecialchars($r['street_number'] . ' ' . $r['street_name']); ?></td>
            <td><?php echo htmlspecialchars($r['city']); ?></td>
            <td><?php echo htmlspecialchars($r['state']); ?></td>
            <td><?php echo htmlspecialchars($r['zip_code']); ?></td>
            <td><?php echo htmlspecialchars($r['client_phone']); ?></td>
            <td><?php echo htmlspecialchars($r['event_date']); ?></td>
            <td><?php echo htmlspecialchars($r['food_order']); ?></td>
            <td><?php echo htmlspecialchars($r['supply_type'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($r['quantity'] ?? ''); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
