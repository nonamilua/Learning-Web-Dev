<?php
session_start();

if ( ! isset($_SESSION["account"]) ) {
    die("ACCESS DENIED");
}

if ( ! isset($_GET['term'])) {
    die("Missing required parameter");
}

require_once "pdo.php";

$stmt = $pdo->prepare('SELECT name FROM Institution WHERE name LIKE :prefix');
$stmt->execute(array( ':prefix' => $_GET['term']."%"));
$retval = array();
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
  $retval[] = $row['name'];
}

echo(json_encode($retval, JSON_PRETTY_PRINT));
?>