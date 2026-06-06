<?php
session_start();
if ( ! isset($_SESSION["account"]) ) {
    die("ACCESS DENIED");

}
if ( isset($_POST['cancel1'] ) ) {

    header("Location: index.php");
    return;
}
require_once "pdo.php";

if (
    isset($_POST['make']) &&
    isset($_POST['model']) &&
    isset($_POST['year']) &&
    isset($_POST['mileage'])
) {

    if (
        strlen(trim($_POST['make'])) < 1 ||
        strlen(trim($_POST['year'])) < 1 ||
        strlen(trim($_POST['model'])) < 1 ||
        strlen(trim($_POST['mileage'])) < 1
    ) {

        $_SESSION["error"] = "All fields are required";
        header("Location: add.php");
        return;

    }
    else if (
        !is_numeric($_POST['year']) ||
        !is_numeric($_POST['mileage'])
    ) {

        $_SESSION["error"] = "Year must be an integer";
        header("Location: add.php");
        return;

    }
    else {

        $sql = "INSERT INTO autos (make, model, year, mileage)
                VALUES (:make, :model, :year, :mileage)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute(array(
            ':make' => $_POST['make'],
            ':model' => $_POST['model'],
            ':year' => $_POST['year'],
            ':mileage' => $_POST['mileage']
        ));

        $_SESSION["success"] = "Record added";
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
<h1>Tracking Autos for <?= htmlentities($_SESSION["account"])?></h1>
<?php
if ( isset($_SESSION["error"])) {
    echo('<p style="color: red;">'.$_SESSION["error"]."</p>\n");
    unset($_SESSION["error"]);
}
?>
<form method="post">
<p>
<strong>Make:</strong>
<input type="text" name="make" size="40">
</p>
<p>
<strong>Model:</strong>
<input type="text" name="model" size="40">
</p>
<p>
<strong>Year:</strong>
<input type="text" name="year">
</p>
<strong> Mileage: </strong>
<input type="text" name="mileage">
<p>
<input type="submit" value="Add"/>
<input type="submit" name="cancel1" value="Cancel">
</p>
</form>

</div>
</body>
</html>
