<?php
session_start();
require_once "pdo.php";

$stmt = $pdo->query(
    "SELECT profile_id, user_id, first_name, last_name, headline FROM profile"
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

<h1>Resume Registry Application</h1>

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
    if ( count($rows) < 1 ) {

    echo('<p>No rows found</p>');
    } 
    else {

    echo('<table border="1">');

        echo('<tr>');
        echo('<th>Name</th>');
        echo('<th>Headline</th>');
        echo('</tr>');

        foreach ( $rows as $row ) {

            echo('<tr><td>');
            echo('<a href="view.php?profile_id='.
            urlencode($row['profile_id']).'">'.
            htmlentities($row['first_name'].' '.$row['last_name']).'</a>');
            echo('</td><td>');
            echo(htmlentities($row['headline']));
            echo('</td><tr>');
        }

        echo('</table>');
    }

}
else {

    if ( count($rows) < 1 ) {

        echo('<p>No rows found</p>');

    }
    else {

        echo('<table border="1">');

        echo('<tr>');
        echo('<th>Name</th>');
        echo('<th>Headline</th>');
        echo('<th>Action</th>');
        echo('</tr>');

        foreach ( $rows as $row ) {

            echo('<tr><td>');
            echo('<a href="view.php?profile_id='.
            urlencode($row['profile_id']).'">'.
            htmlentities($row['first_name'].' '.$row['last_name']).'</a>');
            echo('</td><td>');
            echo(htmlentities($row['headline']));
            echo('</td><td>');

            if ( $_SESSION['user_id'] == $row['user_id'] ) {

                echo('<a href="edit.php?profile_id='.
                    urlencode($row['profile_id']).'">Edit</a> | ');
                echo('<a href="delete.php?profile_id='.
                    urlencode($row['profile_id']).'">Delete</a>');
            
                }
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

