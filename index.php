<?php
session_start();

// Set default style.
include 'aux.php';

if(isset($_SESSION['CC'])) {
	header('Location: main.php');
	exit();
}

if(isset($_GET['login'])) {
	// Change this if you host it!
	// Needs scopes: identify guilds.
	header('Location: https://discordapp.com/api/oauth2/authorize?client_id=586548803126034437&redirect_uri=https%3A%2F%2Fcc.jlxip.net&response_type=code&scope=identify%20guilds');
	exit();
}

/* DISCORD STUFF */
function apiRequest($url, $post=false, $headers=array()) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

	if($post) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	}

	$headers[] = 'Accept: application/json';
	if(isset($_SESSION['CC_token'])) {
		$headers[] = 'Authorization: Bearer '.$_SESSION['CC_token'];
	}
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$response = curl_exec($ch);
	return json_decode($response);
}

function getUID() {
	$user = apiRequest('https://discordapp.com/api/users/@me');
	return $user->id;
}

function getUsername() {
	$user = apiRequest('https://discordapp.com/api/users/@me');
	return htmlspecialchars($user->username);
}

function checkServer() {
	$guilds = apiRequest('https://discordapp.com/api/users/@me/guilds');
	foreach($guilds as $key => $value) {
		// That below is the server ID.
		if($value->id == '341831238484623362') {
			return true;
		}
	}
	return false;
}
/* END DISCORD STUFF */

if(isset($_GET['code'])) {
	include 'db_connect.php';
	include 'secret.php';

	$token = apiRequest('https://discordapp.com/api/oauth2/token', array(
		'grant_type' => 'authorization_code',
		'client_id' => DISCORD_CLIENT,
		'client_secret' => DISCORD_SECRET,
		'redirect_uri' => 'https://cc.jlxip.net',
		'code' => $_GET['code']
	));
	$_SESSION['CC_token'] = $token->access_token;

	$uid = getUID();
	$uname = getUsername();

	// I should check that it's in the server.
	if(!checkServer()) {
		echo 'Looks like you\'re not in the server...<br>';
		echo 'You have to be in order to access the challenges.';
		exit();
	}

	// Login or register.
	$login = $pdo->prepare("SELECT USERNAME FROM USERS WHERE ID=:id");
	$login->bindParam(':id', $uid);
	$login->execute();
	if($login = $login->fetchAll()) {
		// Update username.
		if($login[0][0] != $uname) {
			$update = $pdo->prepare("UPDATE USERS SET USERNAME=:un WHERE ID=:id");
			$update->bindParam(':un', $uname);
			$update->bindParam(':id', $uid);
			$update->execute();
		}
	} else {
		// The user does not exist. Register.
		$register = $pdo->prepare("INSERT INTO USERS (ID, USERNAME) VALUES (:id, :uname)");
		$register->bindParam(':id', $uid);
		$register->bindParam(':uname', $uname);
		$register->execute();
	}

	// Redirect.
	$_SESSION['CC'] = $uid;
	header('Location: main.php');
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
		<title>Login</title>
	</head>
	<body>
		<h1>Michael Reeves' Discord<br>Coding Challenges</h1>
		<hr>

		<div id='fuckyou'>
			<a href='?login' id='discord'>
				<div id='discord'>Login with Discord</div>
			</a>
		</div>
	</body>
</HTML>
