<?php
session_start();
require_once "pdo.php";

$stmt = $pdo->query(
    "SELECT autos_id, make, model, year, mileage FROM autos"
);

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Lua Nardi Quito</title>
</head>

<body>
<div class="container">

<h1>Welcome to the Automobiles Database</h1>

<?php

if ( isset($_SESSION["success"]) ) {
    echo('<p style="color:green;">'.htmlentities($_SESSION["success"])."</p>\n");
    unset($_SESSION["success"]);
}

if ( isset($_SESSION["error"]) ) {
    echo('<p style="color:red;">'.htmlentities($_SESSION["error"])."</p>\n");
    unset($_SESSION["error"]);
}

if ( ! isset($_SESSION["account"]) ) {

    echo('<p><a href="login.php">Please log in</a></p>');
    echo('<p>Attempt to go to <a href="add.php">add data</a> without logging in - it should fail with an error message.</p>');

}
else {

    if ( count($rows) < 1 ) {

        echo('<p>No rows found</p>');

    }
    else {

        echo('<table border="1">');

        echo('<tr>');
        echo('<th>Make</th>');
        echo('<th>Model</th>');
        echo('<th>Year</th>');
        echo('<th>Mileage</th>');
        echo('<th>Action</th>');
        echo('</tr>');

        foreach ( $rows as $row ) {

            echo('<tr><td>');
            echo(htmlentities($row['make']));
            echo('</td><td>');
            echo(htmlentities($row['model']));
            echo('</td><td>');
            echo(htmlentities($row['year']));
            echo('</td><td>');
            echo(htmlentities($row['mileage']));
            echo('</td><td>');

            echo('<a href="edit.php?autos_id='.
                $row['autos_id'].'">Edit</a> / ');

            echo('<a href="delete.php?autos_id='.
                $row['autos_id'].'">Delete</a>');

            echo('</td></tr>');
        }

        echo('</table>');
    }

    echo('<p><a href="add.php">Add New Entry</a></p>');
    echo('<p><a href="logout.php">Logout</a></p>');
}

?>
</div>
</body>
</html>

