<?php
session_start();

if ( ! isset($_SESSION["account"]) ) {
    die("ACCESS DENIED");
}

if ( isset($_POST['cancel']) ) {
    header("Location: index.php");
    return;
}

require_once "pdo.php";

if (
    isset($_POST['first_name']) &&
    isset($_POST['last_name']) &&
    isset($_POST['email']) &&
    isset($_POST['headline']) &&
    isset($_POST['summary'])
) {

    if (
        strlen(trim($_POST['first_name'])) < 1 ||
        strlen(trim($_POST['last_name'])) < 1 ||
        strlen(trim($_POST['email'])) < 1 ||
        strlen(trim($_POST['headline'])) < 1 ||
        strlen(trim($_POST['summary'])) < 1
    ) {

        $_SESSION["error"] = "All fields are required";
        header("Location: add.php");
        return;

    }
    else if ( strpos($_POST['email'], '@') === false ) {

        $_SESSION["error"] = "Email must contain @";
        header("Location: add.php");
        return;

    }
    else {

        for ($i = 1; $i <= 9; $i++) {

        if ( ! isset($_POST['year'.$i]) ||
            ! isset($_POST['desc'.$i]) ) {
            continue;
        }

        if (
            strlen(trim($_POST['year'.$i])) < 1 ||
            strlen(trim($_POST['desc'.$i])) < 1
        ) {

            $_SESSION["error"] =
                "All fields are required";

            header("Location: add.php");
            return;
        }

        if ( ! is_numeric($_POST['year'.$i]) ) {

            $_SESSION["error"] =
                "Position year must be numeric";

            header("Location: add.php");
            return;
        }
    }

        for ($i = 1; $i <= 9; $i++) {

        if ( ! isset($_POST['edu_year'.$i]) ||
            ! isset($_POST['edu_school'.$i]) ) {
            continue;
        }

        if (
            strlen(trim($_POST['edu_year'.$i])) < 1 ||
            strlen(trim($_POST['edu_school'.$i])) < 1
        ) {

            $_SESSION["error"] =
                "All fields are required";

            header("Location: add.php");
            return;
        }

        if ( ! is_numeric($_POST['edu_year'.$i]) ) {

            $_SESSION["error"] =
                "Education year must be numeric";

            header("Location: add.php");
            return;
        }
    }

        $sql = "INSERT INTO profile
                (user_id, first_name, last_name, email, headline, summary)
                VALUES
                (:uid, :fn, :ln, :em, :he, :su)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute(array(
            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary']
        ));

        $profile_id = $pdo->lastInsertId();
        $rank = 1;

        for ($i = 1; $i <= 9; $i++) {

            if ( ! isset($_POST['year'.$i]) ) {
                continue;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO position
                (profile_id, rank, year, description)
                VALUES (:pid, :rank, :year, :desc)'
            );

            $stmt->execute(array(
                ':pid' => $profile_id,
                ':rank' => $rank,
                ':year' => $_POST['year'.$i],
                ':desc' => $_POST['desc'.$i]
            ));

            $rank++;
        }

        $rank = 1;

        for ($i = 1; $i <= 9; $i++) {

            if ( ! isset($_POST['edu_year'.$i]) ) {
                continue;
            }

            $stmt = $pdo->prepare(
                "SELECT * FROM institution WHERE name = :name"
            );

            $stmt->execute(array(
                ':name' => $_POST['edu_school'.$i]
            ));

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ( $row === false ) {

                $stmt = $pdo->prepare(
                'INSERT INTO institution
                (name)
                VALUES (:name)'
            );

            $stmt->execute(array(
                ':name' => $_POST['edu_school'.$i]
            ));

            $institution_id = $pdo->lastInsertId();

            } else {
                
                $institution_id = $row['institution_id'];
            }

            $stmt = $pdo->prepare(
                'INSERT INTO education
                (profile_id, institution_id, rank, year)
                VALUES (:pid, :iid, :rank, :year)'
            );

            $stmt->execute(array(
                ':pid' => $profile_id,
                ':iid' => $institution_id,
                ':rank' => $rank,
                ':year' => $_POST['edu_year'.$i]
            ));

            $rank++;
        }

        $_SESSION["success"] = "Profile added";
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

<h1>Adding Profile for <?= htmlentities($_SESSION["name"]) ?></h1>

<?php
if ( isset($_SESSION["error"]) ) {
    echo('<p style="color:red;">'.
        htmlentities($_SESSION["error"]).
        "</p>\n");

    unset($_SESSION["error"]);
}
?>

<form method="post">

<p>
First Name:
<input type="text" name="first_name" size="60">
</p>

<p>
Last Name:
<input type="text" name="last_name" size="60">
</p>

<p>
Email:
<input type="text" name="email" size="30">
</p>

<p>
Headline:<br/>
<input type="text" name="headline" size="80">
</p>

<p>
Summary:<br/>
<textarea name="summary" rows="8" cols="80"></textarea>
</p>

<p>
Education: <input type="submit" id="addEdu" value="+">
<div id="edu_fields">
</div>
</p>

<p>
Position: <input type="submit" id="addPos" value="+">
<div id="position_fields">
</div>
</p>
<p>
<input type="submit" value="Add">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
</div>
<script>

countPos = 0;
countEdu = 0;
$(document).ready(function(){
    window.console && console.log('Document ready called');

    $('#addPos').click(function(event){
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" onclick="$(\'#position'+countPos+'\').remove();return false;"><br>\
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });

    $('#addEdu').click(function(event){
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education "+countEdu);

        $('#edu_fields').append(
            '<div id="edu'+countEdu+'"> \
            <p>Year: <input type="text" name="edu_year'+countEdu+'" value="" /> \
            <input type="button" value="-" onclick="$(\'#edu'+countEdu+'\').remove();return false;"><br>\
            <p>School: <input type="text" size="80" name="edu_school'+countEdu+'" class="school" value="" />\
            </p></div>'
        );

        $('.school').autocomplete({
            source: "school.php"
        });

    });

});
</script>
</body>
</html>