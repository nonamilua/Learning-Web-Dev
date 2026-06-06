<?php
session_start();
require_once "pdo.php";

if ( ! isset($_GET['profile_id']) || ! is_numeric($_GET['profile_id']) ) {

    $_SESSION["error"] = "Missing or invalid profile_id";
    header("Location: index.php");
    return;
}

$stmt = $pdo->prepare(
    "SELECT * FROM profile WHERE profile_id = :pid"
);

$stmt->execute(array(
    ':pid' => $_GET['profile_id']
));

$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ( $row === false ) {

    $_SESSION["error"] = "Profile not found";
    header("Location: index.php");
    return;
}

$stm = $pdo->prepare(
    "SELECT * FROM position WHERE profile_id = :pid"
);

$stm->execute(array(
    ':pid' => $_GET['profile_id']
));

$positions = $stm->fetchAll(PDO::FETCH_ASSOC);


$stmt = $pdo->prepare(
    "SELECT education.year, institution.name
     FROM education
     JOIN institution
       ON education.institution_id = institution.institution_id
     WHERE education.profile_id = :pid
     ORDER BY education.rank"
);

$stmt->execute(array(
    ':pid' => $_GET['profile_id']
));

$education = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Lua Nardi Quito</title>
</head>

<body>
<div class="container">

<h1>Profile Information</h1>

<p>First Name:
<?= htmlentities($row['first_name']) ?></p>

<p>Last Name:
<?= htmlentities($row['last_name']) ?></p>

<p>Email:
<?= htmlentities($row['email']) ?></p>

<p>Headline:
<?= htmlentities($row['headline']) ?></p>

<p>Summary:
<?= htmlentities($row['summary']) ?></p>

<?php

if ( ! empty($education) ) {

    echo('Education');
    echo('<ul>');
    foreach ($education as $edu) {
        echo '<li>';
        echo htmlentities($edu['year']) . ': ';
        echo htmlentities($edu['name']);
        echo '</li>';
    }
    echo('</ul>');
}

if ( ! empty($positions) ) {

    echo('Position');
    echo('<ul>');
    foreach ($positions as $position) {
        echo('<li>' .
            htmlentities($position['year']) . ': ' .
            htmlentities($position['description']) .
            '</li>');
    }
    echo('</ul>'); 
}

?>

<p>
<a href="index.php">Done</a>
</p>

</div>
</body>
</html>