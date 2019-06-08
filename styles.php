<?php
session_start();

if(!isset($_SESSION['CC'])) {
	header('Location: /');
	exit();
}

include 'aux.php';

if(isset($_POST['s'])) {
	setStyle($_POST['s']);
	header('Location: styles.php');
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
		<title>Code Challenges</title>
	</head>
	<body>
		<p><a href='main.php'>[Back]</a></p>

		<h1>STYLES</h1>
		<hr>

		<form action method='POST'>
			<?php
			echo '<label><input type=\'radio\' name=\'s\' value=\'default\'';
			if($_COOKIE['style'] == 'default') echo ' checked';
			echo '> Default</label><br>';

			echo '<label><input type=\'radio\' name=\'s\' value=\'sky\'';
			if($_COOKIE['style'] == 'sky') echo ' checked';
			echo '> Sky</label><br>'
			?>
			<input type='submit' value='Change'>
		</form>
	</body>
</HTML>
