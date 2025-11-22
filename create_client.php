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

$client_id_param = $_GET['client_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_client') {
        $first = trim($_POST['first_name'] ?? '');
        $last  = trim($_POST['last_name'] ?? '');

        if ($first === '' || $last === '') {
            echo "<script>alert('Please enter both first and last name.');history.back();</script>";
            exit;
        }

        $stmt = $pdo->prepare("SELECT client_id FROM client WHERE LOWER(first_name) = LOWER(:first) AND LOWER(last_name) = LOWER(:last) LIMIT 1");
        $stmt->execute([
            ':first' => $first,
            ':last'  => $last
        ]);
        $existing = $stmt->fetch();

        if ($existing) {
            echo "<script>alert('Account already exists for this client.');history.back();</script>";
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO client (first_name, last_name) VALUES (:first, :last)");
        $stmt->execute([
            ':first' => $first,
            ':last'  => $last
        ]);
        $newId = $pdo->lastInsertId();
        $cid = urlencode($caterer_id);
        $clientUrlId = urlencode($newId);
        echo "<script>alert('New client account created.');window.location.href='create_client.php?caterer_id={$cid}&client_id={$clientUrlId}';</script>";
        exit;
    }

    if ($action === 'create_info') {
        $clientId     = intval($_POST['client_id'] ?? 0);
        $streetNumber = trim($_POST['street_number'] ?? '');
        $streetName   = trim($_POST['street_name'] ?? '');
        $city         = trim($_POST['city'] ?? '');
        $state        = trim($_POST['state'] ?? '');
        $zip          = trim($_POST['zip_code'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');

        if ($clientId <= 0 || $streetNumber === '' || $streetName === '' || $city === '' || $state === '' || $zip === '' || $phone === '') {
            echo "<script>alert('Please fill out all client information fields.');history.back();</script>";
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO client_info (client_id, street_number, street_name, city, state, zip_code, phone) VALUES (:client_id, :snumber, :sname, :city, :state, :zip, :phone)");
        $stmt->execute([
            ':client_id' => $clientId,
            ':snumber'   => $streetNumber,
            ':sname'     => $streetName,
            ':city'      => $city,
            ':state'     => $state,
            ':zip'       => $zip,
            ':phone'     => $phone
        ]);

        $cid = urlencode($caterer_id);
        echo "<script>alert('Client Information Record Created.');window.location.href='create_client.php?caterer_id={$cid}';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create New Client Account</title>
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

    <h1>Create a New Client Account</h1>
    <h2>
      Caterer:
      <?php echo htmlspecialchars($caterer['first_name'] . ' ' . $caterer['last_name']); ?>
      (ID: <?php echo htmlspecialchars($caterer['id']); ?>)
    </h2>

    <section>
      <h3>Step 1: Client Account</h3>
      <form method="post" action="create_client.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>">
        <input type="hidden" name="action" value="create_client">
        <input type="hidden" name="caterer_id" value="<?php echo htmlspecialchars($caterer_id); ?>">

        <label for="first_name">Client First Name</label>
        <input type="text" id="first_name" name="first_name" required>

        <label for="last_name">Client Last Name</label>
        <input type="text" id="last_name" name="last_name" required>

        <button type="submit">Create Client Account</button>
      </form>
    </section>

    <?php if ($client_id_param !== ''): ?>
      <section>
        <h3>Step 2: Client Information</h3>
        <p>Client ID: <?php echo htmlspecialchars($client_id_param); ?></p>
        <form method="post" action="create_client.php?caterer_id=<?php echo htmlspecialchars($caterer_id); ?>">
          <input type="hidden" name="action" value="create_info">
          <input type="hidden" name="caterer_id" value="<?php echo htmlspecialchars($caterer_id); ?>">
          <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client_id_param); ?>">

          <label for="street_number">Street Number</label>
          <input type="text" id="street_number" name="street_number" required>

          <label for="street_name">Street Name</label>
          <input type="text" id="street_name" name="street_name" required>

          <label for="city">City</label>
          <input type="text" id="city" name="city" required>

          <label for="state">State (2 letters)</label>
          <input type="text" id="state" name="state" maxlength="2" required>

          <label for="zip_code">Zip Code</label>
          <input type="text" id="zip_code" name="zip_code" maxlength="5" required>

          <label for="phone">Phone</label>
          <input type="text" id="phone" name="phone" required>

          <button type="submit">Create Client Information</button>
        </form>
      </section>
    <?php endif; ?>
  </div>
</body>
</html>
