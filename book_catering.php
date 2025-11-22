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

$action = $_POST['action'] ?? '';
$foundClient = null;

if ($action === 'lookup_client') {
    $clientFirst = trim($_POST['client_first'] ?? '');
    $clientLast  = trim($_POST['client_last'] ?? '');

    if ($clientFirst === '' || $clientLast === '') {
        echo "<script>alert('Please enter both client first and last name.');history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM client WHERE LOWER(first_name) = LOWER(:first) AND LOWER(last_name) = LOWER(:last) LIMIT 1");
    $stmt->execute([
        ':first' => $clientFirst,
        ':last'  => $clientLast
    ]);
    $foundClient = $stmt->fetch();

    if (!$foundClient) {
        $cid = urlencode($caterer_id);
        echo "<script>
            if (confirm('Client account cannot be found. Click OK to re-enter data, or Cancel to create a new client account.')) {
                history.back();
            } else {
                window.location.href = 'create_client.php?caterer_id={$cid}';
            }
        </script>";
        exit;
    }
}

if ($action === 'book_event') {
    $clientId   = intval($_POST['client_id'] ?? 0);
    $eventDate  = trim($_POST['event_date'] ?? '');
    $foodOrder  = trim($_POST['food_order'] ?? '');

    if ($clientId <= 0 || $eventDate === '' || $foodOrder === '') {
        echo "<script>alert('Please fill out all booking fields.');history.back();</script>";
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO catering_event (client_id, caterer_id, event_date, food_order) VALUES (:client_id, :caterer_id, :event_date, :food_order)");
    $stmt->execute([
        ':client_id'  => $clientId,
        ':caterer_id' => $caterer_id,
        ':event_date' => $eventDate,
        ':food_order' => $foodOrder
    ]);

    $newId = $pdo->lastInsertId();
    $cid = urlencode($caterer_id);
    echo "<script>alert('Catering Event Booked. Your Catering ID is: {$newId}');window.location.href='book_catering.php?caterer_id={$cid}';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Client Catering Event</title>
  <link rel="stylesheet" href="style.css">
  <script>
    function confirmBooking() {
      return confirm('Are you sure you want to book this catering event?');
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

    <h1>Book a Client's Catering Event</h1>
    <h2>
      Caterer:
      <?php echo htmlspecialchars($caterer['first_name'] . ' ' . $caterer['last_name']); ?>
      (ID: <?php echo htmlspecialchars($caterer['id']); ?>)
    </h2>

    <section>
      <h3>Step 1: Find Client Account</h3>
      <form method="post" action="book_catering.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>">
        <input type="hidden" name="action" value="lookup_client">
        <input type="hidden" name="caterer_id" value="<?php echo htmlspecialchars($caterer_id); ?>">

        <label for="client_first">Client First Name</label>
        <input type="text" id="client_first" name="client_first" required>

        <label for="client_last">Client Last Name</label>
        <input type="text" id="client_last" name="client_last" required>

        <button type="submit">Search Client</button>
      </form>
    </section>

    <?php if ($foundClient): ?>
      <section>
        <h3>Step 2: Book Catering Event</h3>
        <p>
          Booking for:
          <?php echo htmlspecialchars($foundClient['first_name'] . ' ' . $foundClient['last_name']); ?>
          (Client ID: <?php echo htmlspecialchars($foundClient['client_id']); ?>)
        </p>
        <form method="post" action="book_catering.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>" onsubmit="return confirmBooking();">
          <input type="hidden" name="action" value="book_event">
          <input type="hidden" name="caterer_id" value="<?php echo htmlspecialchars($caterer_id); ?>">
          <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($foundClient['client_id']); ?>">

          <label for="event_date">Event Date</label>
          <input type="date" id="event_date" name="event_date" required>

          <label for="food_order">Food Order</label>
          <textarea id="food_order" name="food_order" rows="4" required></textarea>

          <button type="submit">Book Event</button>
        </form>
      </section>
    <?php endif; ?>
  </div>
</body>
</html>
