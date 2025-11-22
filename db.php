<?php
$DB_HOST = 'sql1.njit.edu';
$DB_NAME = 'sae47';
$DB_USER = 'sae47';
$DB_PASS = 'Eghadesuwa2005@';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

$pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
