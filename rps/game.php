<?php
session_start();

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if (!isset($_SESSION['human_score'])) {
    $_SESSION['human_score'] = 0;
    $_SESSION['computer_score'] = 0;
}

// Demand a GET parameter
if ( ! isset($_GET['name']) || strlen($_GET['name']) < 1  ) {
    die('Se manca');
}


// Set up the values for the game...
// 0 is Rock, 1 is Paper, and 2 is Scissors
$names = array('Rock', 'Paper', 'Scissors');
$human = isset($_POST["human"]) ? $_POST['human']+0 : -1;

$computer = rand(0,2);

function check($computer, $human) {
    if ( $human === $computer ) {
        return "Tie";
    } else if (
        (($human === 0) && ($computer===2)) || 
        (($human === 1) && ($computer===0)) || 
        (($human === 2) && ($computer===1))
        ) {
        $_SESSION['human_score']++;
        return "You Win";
    } else if (
        (($human === 0) && ($computer===1)) || 
        (($human === 1) && ($computer===2)) || 
        (($human === 2) && ($computer===0))
        ) {
        $_SESSION['computer_score']++;
        return "You Lose";
    }
    return false;
}

// Check to see how the play happened
$result = check($computer, $human);

?>
<!DOCTYPE html>
<html>
<head>
<title>Saite da Lua</title>
<meta charset="UTF-8">
<link rel="icon" type="image/png" href="crewmate.png">
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Gameplay Engajante de Jokenpô</h1>
<?php
if ( isset($_REQUEST['name']) ) {
    echo "<h2>Boas Vindas, ";
    echo htmlentities($_REQUEST['name']);
    $pessoa = $_REQUEST['name'];
    echo "</h2>\n";
}
?>
<form method="post">
<select name="human">
<option value="-1">Select</option>
<option value="0">Rock</option>
<option value="1">Paper</option>
<option value="2">Scissors</option>
</select>
<input type="submit" value="Play">
<input type="submit" name="logout" value="Logout">
</form>
<div style="height:10px;"></div>
<pre>
<?php
if ( $human == -1 ) {
    print "Please select a play";
} 
else {
    print "$pessoa = $names[$human] | Clanker = $names[$computer] | $result";
}
?> 
</pre>

<?php
if 
    (
    ($_SESSION['computer_score'] < 3) && ($_SESSION['human_score'] < 3)
    ) {
    print "$pessoa: ".$_SESSION['human_score']." 🫵 Clanker: ".$_SESSION['computer_score']." 🤖";
}

else if ($_SESSION['computer_score'] == 3) {
    session_destroy();
?>
    
    <h2>Vc morreu D:</h2>
    <img src="03b018mwa2lg1.png" width="300">
    <p> Isso é oq aconteceu com a humanidade. Fim </p>
    
<?php
} else if ($_SESSION['human_score'] == 3) {
    session_destroy();
?>

    <h2>Vc salvou a humanidade :D</h2>
    <img src="1635812453012.png" width="300">
    <p> A sociedade é assi agora. Fim </p>

<?php
}
?>

</div>
</body>
</html>
