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

if (
    isset($_POST['make']) &&
    isset($_POST['model']) &&
    isset($_POST['year']) &&
    isset($_POST['mileage']) &&
    isset($_POST['autos_id'])
) {

    if (
        strlen(trim($_POST['make'])) < 1 ||
        strlen(trim($_POST['model'])) < 1 ||
        strlen(trim($_POST['year'])) < 1 ||
        strlen(trim($_POST['mileage'])) < 1
    ) {

        $_SESSION["error"] = "All fields are required";
        header("Location: edit.php?autos_id=".$_POST['autos_id']);
        return;

    }
    else if (
        !is_numeric($_POST['year']) ||
        !is_numeric($_POST['mileage'])
    ) {

        $_SESSION["error"] = "Year and mileage must be numeric";
        header("Location: edit.php?autos_id=".$_POST['autos_id']);
        return;

    }
    else {

        $sql = "UPDATE autos
                SET make = :make,
                    model = :model,
                    year = :year,
                    mileage = :mileage
                WHERE autos_id = :autos_id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute(array(
            ':make' => $_POST['make'],
            ':model' => $_POST['model'],
            ':year' => $_POST['year'],
            ':mileage' => $_POST['mileage'],
            ':autos_id' => $_POST['autos_id']
        ));

        $_SESSION["success"] = "Record updated";
        header("Location: index.php");
        return;
    }
}
?>

<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Lua Nardi Quito</title>
</head>

<body>
<div class="container">

<h1>Editing Autos for <?= htmlentities($_SESSION["account"]) ?></h1>

<?php
if ( isset($_SESSION["error"]) ) {
    echo('<p style="color:red;">'.htmlentities($_SESSION["error"])."</p>\n");
    unset($_SESSION["error"]);
}
?>

<form method="post">

<p>
<strong>Make:</strong>
<input type="text" name="make" size="40"
value="<?= htmlentities($row['make']) ?>">
</p>

<p>
<strong>Model:</strong>
<input type="text" name="model" size="40"
value="<?= htmlentities($row['model']) ?>">
</p>

<p>
<strong>Year:</strong>
<input type="text" name="year"
value="<?= htmlentities($row['year']) ?>">
</p>

<p>
<strong>Mileage:</strong>
<input type="text" name="mileage"
value="<?= htmlentities($row['mileage']) ?>">
</p>

<input type="hidden" name="autos_id"
value="<?= $row['autos_id'] ?>">

<p>
<input type="submit" value="Save">
<input type="submit" name="cancel1" value="Cancel">
</p>

</form>

</div>
</body>
</html>