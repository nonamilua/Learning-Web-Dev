<?php
session_start();

if ( ! isset($_SESSION["account"]) ) {
    die("ACCESS DENIED");
}

if ( isset($_POST['cancel1']) ) {
    header("Location: index.php");
    return;
}

require_once "pdo.php";

if ( ! isset($_GET['autos_id']) || ! is_numeric($_GET['autos_id']) ) {
    $_SESSION["error"] = "Missing or invalid autos_id";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare("SELECT * FROM autos WHERE autos_id = :id");
$stmt->execute(array(
    ':id' => $_GET['autos_id']
));

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ( $row === false ) {
    $_SESSION["error"] = "Bad value for autos_id";
    header("Location: index.php");
    return;
}

if ( isset($_POST['del']) ) {
    
    $sql = "DELETE FROM autos
            WHERE autos_id = :autos_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute(array(
        ':autos_id' => $_POST['autos_id']
    ));

    $_SESSION["success"] = "Record deleted";
    header("Location: index.php");
    return;
    }

?>

<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Lua Nardi Quito</title>
</head>
<body>
<div class="container">

<strong>Confirm: Deleting <?= $row['make'] ?></strong>
<form method="post">
<input type="hidden" name="autos_id"
value="<?= $row['autos_id'] ?>">

<p>
<input type="submit" name ="del" value="Delete">
<input type="submit" name="cancel1" value="Cancel">
</p>

</form>
</div>
</body>
</html>