<?php
require 'db.php';

function normalizePhone($input) {
    if (preg_match(
        '/^(\d{3})[- ](\d{3})[- ](\d{4})\s*(?:x|ext\.?|extension)\s*(\d+)$/i',
        $input,
        $m
    )) {
        return "{$m[1]}-{$m[2]}-{$m[3]} x{$m[4]}";
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName   = trim($_POST['firstName']  ?? '');
    $lastName    = trim($_POST['lastName']   ?? '');
    $phoneInput  = trim($_POST['phone']      ?? '');
    $catererId   = trim($_POST['catererId']  ?? '');
    $email       = trim($_POST['email']      ?? '');
    $wantsEmail  = isset($_POST['wantsEmail']);
    $password    = $_POST['password'] ?? '';
    $transaction = $_POST['transaction'] ?? '';

    $phone = normalizePhone($phoneInput);
    if (!$phone) {
        echo "<script>alert('Phone formatting issue during verification. Please fix and try again.');history.back();</script>";
        exit;
    }

    $sql = "SELECT * FROM caterer
            WHERE LOWER(first_name) = LOWER(:first)
              AND LOWER(last_name)  = LOWER(:last)
              AND id       = :id
              AND password = :pwd
              AND phone    = :phone";

    if ($wantsEmail) {
        $sql .= " AND LOWER(email) = LOWER(:email)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':first', $firstName);
    $stmt->bindValue(':last',  $lastName);
    $stmt->bindValue(':id',    $catererId);
    $stmt->bindValue(':pwd',   $password);
    $stmt->bindValue(':phone', $phone);
    if ($wantsEmail) {
        $stmt->bindValue(':email', strtolower($email));
    }

    $stmt->execute();
    $caterer = $stmt->fetch();

    if (!$caterer) {
        echo "<script>alert('Account for {$firstName} {$lastName} cannot be found.');history.back();</script>";
        exit;
    }

    $cid = urlencode($caterer['id']);

    switch ($transaction) {
        case 'search':
            header("Location: search_caterer.php?caterer_id={$cid}");
            break;
        case 'book':
            header("Location: book_catering.php?caterer_id={$cid}");
            break;
        case 'cancel':
            header("Location: cancel_catering.php?caterer_id={$cid}");
            break;
        case 'request_addon':
            header("Location: request_addon.php?caterer_id={$cid}");
            break;
        case 'update_addon':
            header("Location: update_addon.php?caterer_id={$cid}");
            break;
        case 'create_client':
            header("Location: create_client.php?caterer_id={$cid}");
            break;
        default:
            echo "<script>alert('Please choose a transaction from the dropdown.');history.back();</script>";
            break;
    }

    exit;
}
