<?php
session_start();
session_destroy();

include 'aux.php';
?>

<!DOCTYPE HTML>

<HTML>
	<head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0' />
		<link href='https://fonts.googleapis.com/css?family=<?php echoFonts(); ?>&display=swap' rel='stylesheet'>
		<link rel='stylesheet' type='text/css' href='css/normalize.css'>
		<link rel='stylesheet' type='text/css' href='css/<?php echo $_COOKIE['style']; ?>.css'>
		<title>See you!</title>
	</head>
	<body>
		<h1>Logged out.</h1>
		<p class='center'>Redirecting to the login page...</p>
		<meta http-equiv='refresh' content='3; url=/' />
	</body>
</HTML>
