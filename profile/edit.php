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
    "SELECT *
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

$stm = $pdo->prepare(
    "SELECT * FROM position
    WHERE profile_id = :pid
    ORDER BY rank"
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

$educations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (
    isset($_POST['first_name']) &&
    isset($_POST['last_name']) &&
    isset($_POST['email']) &&
    isset($_POST['headline']) &&
    isset($_POST['summary']) &&
    isset($_POST['profile_id'])
) {

    if (
        strlen(trim($_POST['first_name'])) < 1 ||
        strlen(trim($_POST['last_name'])) < 1 ||
        strlen(trim($_POST['email'])) < 1 ||
        strlen(trim($_POST['headline'])) < 1 ||
        strlen(trim($_POST['summary'])) < 1
    ) {

        $_SESSION["error"] = "All fields are required";

        header(
            "Location: edit.php?profile_id=".
            urlencode($_POST['profile_id'])
        );

        return;
    }
    else if ( strpos($_POST['email'], '@') === false ) {

        $_SESSION["error"] = "Email must contain @";

        header(
            "Location: edit.php?profile_id=".
            urlencode($_POST['profile_id'])
        );

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

                header("Location: edit.php?profile_id=".
                        urlencode($_POST['profile_id']));     
                return;
            }

            if ( ! is_numeric($_POST['year'.$i]) ) {

                $_SESSION["error"] =
                    "Position year must be numeric";

                header("Location: edit.php?profile_id=".
                        urlencode($_POST['profile_id'])); 
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

                header("Location: edit.php?profile_id=".
                        urlencode($_POST['profile_id']));
                return;
            }

            if ( ! is_numeric($_POST['edu_year'.$i]) ) {

                $_SESSION["error"] =
                    "Education year must be numeric";

                header("Location: edit.php?profile_id=".
                        urlencode($_POST['profile_id']));
                return;
            }
        }

        $sql = "UPDATE profile
                SET first_name = :fn,
                    last_name = :ln,
                    email = :em,
                    headline = :he,
                    summary = :su
                WHERE profile_id = :pid
                AND user_id = :uid";

        $stmt = $pdo->prepare($sql);

        $stmt->execute(array(
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary'],
            ':pid' => $row['profile_id'],
            ':uid' => $_SESSION['user_id']
        ));

        $stmt = $pdo->prepare(
            "DELETE FROM position
            WHERE profile_id = :pid"
        );

        $stmt->execute(array(
            ':pid' => $row['profile_id']
        ));

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
                ':pid' => $row['profile_id'],
                ':rank' => $rank,
                ':year' => $_POST['year'.$i],
                ':desc' => $_POST['desc'.$i]
            ));

            $rank++;

        }

        $stmt = $pdo->prepare(
        "DELETE FROM education
        WHERE profile_id = :pid"
        );

        $stmt->execute(array(
            ':pid' => $row['profile_id']
        ));

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

            $institution = $stmt->fetch(PDO::FETCH_ASSOC);

            if ( $institution === false ) {

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
                
                $institution_id = $institution['institution_id'];
            }

            $stmt = $pdo->prepare(
                'INSERT INTO education
                (profile_id, institution_id, rank, year)
                VALUES (:pid, :iid, :rank, :year)'
            );

            $stmt->execute(array(
                ':pid' => $row['profile_id'],
                ':iid' => $institution_id,
                ':rank' => $rank,
                ':year' => $_POST['edu_year'.$i]
            ));

            $rank++;

        }
        
        $_SESSION["success"] = "Profile updated";
        header("Location: index.php");
        return;
    }
}
?>

<html>
<head>
<?php require_once "bootstrap.php"; ?>
<title>Edit Profile</title>
</head>

<body>
<div class="container">

<h1>Editing Profile for <?= htmlentities($_SESSION["name"]) ?></h1>

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
<input type="text" name="first_name" size="60"
value="<?= htmlentities($row['first_name']) ?>">
</p>

<p>
Last Name:
<input type="text" name="last_name" size="60"
value="<?= htmlentities($row['last_name']) ?>">
</p>

<p>
Email:
<input type="text" name="email" size="30"
value="<?= htmlentities($row['email']) ?>">
</p>

<p>
Headline:<br/>
<input type="text" name="headline" size="80"
value="<?= htmlentities($row['headline']) ?>">
</p>

<p>
Summary:<br/>
<textarea name="summary" rows="8" cols="80"><?= htmlentities($row['summary']) ?></textarea>
</p>

<input type="hidden"
name="profile_id"
value="<?= $row['profile_id'] ?>">

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

<?php
$countPos = 0;
$countEdu = 0;

foreach ($educations as $edu) {
    $countEdu++;

    echo('<div id="edu'.$countEdu.'">');
    echo('<p>Year: ');
    echo('<input type="text" name="edu_year'.$countEdu.'" value="'.
        htmlentities($edu['year']).'"/>');

    echo('<input type="button" value="-"
        onclick="$(\'#edu'.$countEdu.'\').remove();return false;">');

    echo('</p>');

    echo('<input type="text"
        class="school"
        name="edu_school'.$countEdu.
        '" value="'.htmlentities($edu['name']).'">');

    echo('</div>');
}

foreach ($positions as $position) {
    $countPos++;

    echo('<div id="position'.$countPos.'">');
    echo('<p>Year: ');
    echo('<input type="text" name="year'.$countPos.'" value="'.
        htmlentities($position['year']).'"/>');

    echo('<input type="button" value="-"
        onclick="$(\'#position'.$countPos.'\').remove();return false;">');

    echo('</p>');

    echo('<textarea name="desc'.$countPos.'" rows="8" cols="80">');
    echo(htmlentities($position['description']));
    echo('</textarea>');

    echo('</div>');
}
?>


<p>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</div>

</form>
<script>
countPos = <?= $countPos ?>;
countEdu = <?= $countEdu ?>;
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

    $('.school').autocomplete({
    source: "school.php"
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

        $('.school:last').autocomplete({
        source: "school.php"
        });

    });

});
</script>
</body>
</html>