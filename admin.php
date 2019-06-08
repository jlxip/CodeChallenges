<?php
session_start();
if(!isset($_SESSION['CC'])) {
	echo 'You\'re not logged in.<br>';
	echo '<a href=\'/\'>Click here to go back.</a>';
	exit();
}

include 'db_connect.php';
include 'aux.php';

$umode = $pdo->prepare("SELECT MODE FROM USERS WHERE ID=:id");
$umode->bindParam(':id', $_SESSION['CC']);
$umode->execute();
if(!($umode = $umode->fetchAll())) {
	session_destroy();
	header('Location: /');
	exit();
}
$umode = $umode[0]['MODE'];

if($umode != '2') {
	echo 'Only administrators can access the panel.';
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
		<title>Admin panel</title>
	</head>
	<body>
		<p><a href='/'>[Go back]</a></p>

		<h1>ADMIN PANEL</h1>
		<hr>

		<h3>Welcome back, master.</h3><br>
		<h3><a href='useradmin.php'>Manage users</a></h3>
		<h3><a href='log.php'>Read log</a></h3>
	</body>
</HTML>
