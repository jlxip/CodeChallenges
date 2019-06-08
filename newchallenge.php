<?php
session_start();
if(!isset($_SESSION['CC'])) {
	echo 'You\'re not logged in.<br>';
	echo '<a href=\'/\'>Click here to go back.</a>';
	exit();
}

include 'db_connect.php';
include 'aux.php';

$umode = $pdo->prepare("SELECT USERNAME, MODE FROM USERS WHERE ID=:id");
$umode->bindParam(':id', $_SESSION['CC']);
$umode->execute();
if(!($umode = $umode->fetchAll())) {
	session_destroy();
	header('Location: /');
	exit();
}
$uname = $umode[0]['USERNAME'];
$umode = $umode[0]['MODE'];

if($umode == '0') {
	echo 'Only staff and administrators can access this.';
	exit();
}

if(isset($_POST['name'])) {
	if(!isset($_POST['desc']) || !isset($_POST['points'])) {
		echo 'You have to fill all the fields!';
		echo '<a href=\'newchallenge.php\'>Try again.</a>';
		exit();
	}

	if($_POST['name'] == '') {
		echo 'Bruh just write a name.';
		exit();
	}

	if(!is_numeric($_POST['points'])) {
		echo 'Bruh that\'s totally not a number.';
		exit();
	}

	$name = htmlspecialchars($_POST['name']);
	$desc = htmlspecialchars($_POST['desc']);

	// No further checks.
	$insert = $pdo->prepare("INSERT INTO CHALLENGES (NAME, DESC, POINTS, CREATOR, DATE) VALUES (:n, :d, :p, :c, :date)");
	$insert->bindParam(':n', $name);
	$insert->bindParam(':d', $desc);
	$insert->bindParam(':p', $_POST['points']);
	$insert->bindParam(':c', $_SESSION['CC']);
	$insert->bindParam(':date', cc_time());
	$insert->execute();

	// Log.
	$toWrite = $uname.' created "'.$_POST['name'].'".';
	$log = $pdo->prepare("INSERT INTO LOG (DATE, CONTENT) VALUES (:d, :c)");
	$log->bindParam(':d', cc_time());
	$log->bindParam(':c', $toWrite);
	$log->execute();

	echo 'Done!<br>';
	echo '<a href=\'/\'>Go back.</a>';
	exit();
}
?>

<!DOCTYPE HTML>

<HTML>
	<head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0' />
		<link href='https://fonts.googleapis.com/css?family=<?php echoFonts(); ?>&display=swap' rel='stylesheet'>
		<link rel='stylesheet' type='text/css' href='css/normalize.css'>
		<link rel='stylesheet' type='text/css' href='css/<?php echo $_COOKIE['style']; ?>.css'>
		<title>Create a challenge</title>
	</head>
	<body>
		<p><a href='main.php'>[Go back]</a></p>
		<h1>NEW CHALLENGE</h1>
		<hr>

		<form action method='POST' id='f'>
			<p>Challenge name</p><input type='text' name='name'><br>
			<p>Description</p>
			<textarea name='desc' form='f'></textarea><br>
			<p>Points (integer!)</p><input type='text' name='points'><br>
			<br>
			<input type='submit' value='CREATE'>
		</form>
	</body>
</HTML>
