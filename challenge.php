<?php
session_start();

if(!isset($_SESSION['CC'])) {
	header('Location: /');
	exit();
}

include 'db_connect.php';
include 'aux.php';

if(!isset($_GET['id'])) { echo 'No ID?'; exit(); }

// Get the challenge.
$chall = $pdo->prepare("SELECT * FROM CHALLENGES WHERE ID=:id");
$chall->bindParam(':id', $_GET['id']);
$chall->execute();
if(!($chall = $chall->fetchAll())) {
	// It doesn't exist. Redirect.
	header('Location: challenges.php?page='.$_SESSION['CC_page']);
	exit();
}
$chall = $chall[0];

$creator = $pdo->prepare("SELECT USERNAME FROM USERS WHERE ID=:id");
$creator->bindParam(':id', $chall['CREATOR']);
$creator->execute();
if($creator = $creator->fetchAll()) {
	$creator = $creator[0][0];
} else {
	$creator = '[deleted]';
}

// Check if solved and shit.
$already = $pdo->prepare("SELECT * FROM SUBMITS WHERE USER_ID=:uid AND CHALLENGE_ID=:cid");
$already->bindParam(':uid', $_SESSION['CC']);
$already->bindParam(':cid', $_GET['id']);
$already->execute();
if($already = $already->fetchAll()) {
	// Submitted.
	$already = $already[0];
} else {
	// Not submitted.
	$already = false;
}

$subsent = 0;
if(isset($_POST['flag']) && !$already) {
	include 'secret.php';
	$recaptcha = $_POST["g-recaptcha-response"];

	$url = 'https://www.google.com/recaptcha/api/siteverify';
	$data = array(
		'secret' => CC_SUBMIT_CAPTCHA_SECRET,
		'response' => $recaptcha
	);
	$options = array(
		'http' => array (
			'method' => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context  = stream_context_create($options);
	$verify = file_get_contents($url, false, $context);
	$captcha_success = json_decode($verify);
	if($captcha_success->success) {
		// Fix the URL.
		$aux = $_POST['flag'];
		if(!(strpos($aux, 'http') === 0)) {
			$aux = 'http://'.$aux;
		}
		$aux = htmlspecialchars($aux);

		// Create submission.
		$sub = $pdo->prepare("INSERT INTO SUBMITS (USER_ID, CHALLENGE_ID, URL, DATE, STATE) VALUES (:uid, :cid, :u, :d, 0)");
		$sub->bindParam(':uid', $_SESSION['CC']);
		$sub->bindParam(':cid', $_GET['id']);
		$sub->bindParam(':u', $aux);
		$sub->bindParam(':d', cc_time());
		$sub->execute();

		$subsent = 1;
	} else {
		echo 'There\'s been a problem with the captcha.<br>';
		echo '<a href=\'challenge.php?id='.$_GET['id'].'\'>Try again.</a>';
		exit();
	}
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
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<title>Code Challenges</title>
	</head>
	<body>
		<p><a href='challenges.php?page=<?php echo $_SESSION['CC_page']; ?>'>[Go back]</a></p><br>
		<h1><?php echo $chall['NAME']; ?></h1>
		<h3>
		<?php
		echo $chall['POINTS'].' point';
		if($chall['POINTS'] != '1') echo 's';
		?>
		</h3>

		<div id='info'>
			<?php
			$desc = $chall['DESC'];
			$desc = str_replace("\n", '<br>', $desc);
			echo '<p>'.$desc.'</p>';
			?>
			<hr>
			<p>By <?php echo $creator; ?>. <?php echo timeFormat($chall['DATE']); ?>.</p>
			<hr>

			<?php
			if($chall['CREATOR'] == $_SESSION['CC']) {
				echo '<p class=\'center\'>You are the creator of this challenge so you cannot solve it.</p>';
			} elseif($already) {
				switch($already['STATE']) {
					case '0':
						echo '<p class=\'center\'>You have already submitted your code ('.timeFormat($already['DATE']).').<br>';
						echo 'Be patient. <a href=\''.$already['URL'].'\'>Your code</a>.</p>';
						break;
					case '1':
						echo '<p class=\'center\'>You have solved this challenge.<br>';
						echo '<a href=\''.$already['URL'].'\'>Your code.</a></p>';
						break;
				}
			} elseif($subsent == 1) {
				echo '<p class=\'center\'>Submission sent.</p>';
			} else {
				echo '<form action=\'?id='.$_GET['id'].'\' method=\'POST\'>';
				echo '<input type=\'text\' name=\'flag\' placeholder=\'Link to code\' autocomplete=\'off\'><br>';
				echo '<div class=\'g-recaptcha\' data-sitekey=\'6Le6fqcUAAAAAObTSEtcrc09PlDe3N5jmHNlvet3\'></div>';
				echo '<input type=\'submit\' value=\'Go!\'>';
				echo '</form><br>';
			}
		echo '</div>';

		$staff = $pdo->prepare("SELECT 1 FROM USERS WHERE ID=:id AND MODE > 0");
		$staff->bindParam(':id', $_SESSION['CC']);
		$staff->execute();
		if($staff = $staff->fetchAll()) {
			echo '<br><br>';
			echo '<p class=\'center\'>';
			echo 'You\'re part of the staff, so you can <a href=\'dchall.php?id='.$_GET['id'].'\'>delete this challenge</a>.';
			echo '</p>';
		}
		?>
	</body>
</HTML>
