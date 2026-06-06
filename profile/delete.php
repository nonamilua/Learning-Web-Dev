<?php
session_start();

if ( ! isset($_SESSION["account"]) ) {
    die("ACCESS DENIED");
}

require_once "pdo.php";

if ( isset($_POST['cancel']) ) {
    header("Location: index.php");
    return;
}

if ( ! isset($_GET['profile_id']) || ! is_numeric($_GET['profile_id']) ) {

    $_SESSION["error"] = "Missing or invalid profile_id";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare(
    "SELECT first_name, last_name, profile_id
    FROM profile
    WHERE profile_id = :pid
    AND user_id = :uid"
);

$stmt->execute(array(
    ':pid' => $_GET['profile_id'],
    ':uid' => $_SESSION['user_id']
));

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ( $row === false ) {

    $_SESSION["error"] = "Profile not found";
    header("Location: index.php");
    return;
}

if ( isset($_POST['profile_id']) ) {

    $stmt = $pdo->prepare(
        "DELETE FROM profile
        WHERE profile_id = :pid
        AND user_id = :uid"
    );

    $stmt->execute(array(
        ':pid' => $_POST['profile_id'],
        ':uid' => $_SESSION['user_id']
    ));

    $_SESSION["success"] = "Profile deleted";
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

<h1>Deleting Profile</h1>

<p>
First Name:
<?= htmlentities($row['first_name']) ?>
</p>

<p>
Last Name:
<?= htmlentities($row['last_name']) ?>
</p>

<form method="post">

<input type="hidden"
name="profile_id"
value="<?= $row['profile_id'] ?>">

<input type="submit" value="Delete">
<input type="submit" name="cancel" value="Cancel">

</form>

</div>
</body>
</html>