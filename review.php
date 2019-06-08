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

if(isset($_GET['id'])) {
	// Check CSRF.
	if($_SESSION['CC_csrf'] != $_GET['csrf']) {
		echo 'Invalid CSRF token.<br>';
		echo '<a href=\'review.php\'>Go back.</a>';
		exit();
	}

	// Make sure that the challenge exists and it's not from the user.
	$check = $pdo->prepare("SELECT USER_ID, CHALLENGE_ID FROM SUBMITS WHERE ID=:id AND STATE=0");
	$check->bindParam(':id', $_GET['id']);
	$check->execute();
	if(!($check = $check->fetchAll())) {
		echo 'This submission does not exist.<br>';
		echo '<a href=\'review.php\'>Go back.</a>';
		exit();
	}
	$check = $check[0];

	if($check['USER_ID'] == $_SESSION['CC']) {
		echo 'Don\'t validate or reject your own submission. Dickhead.<br>';
		echo '<a href=\'review.php\'>Go back.</a>';
		exit();
	}

	if(isset($_GET['v'])) {
		// Get the previous user points.
		$p = $pdo->prepare("SELECT POINTS FROM USERS WHERE ID=:id");
		$p->bindParam(':id', $check['USER_ID']);
		$p->execute();
		$p = intval($p->fetchAll()[0][0]);

		// Get the challenge points.
		$cp = $pdo->prepare("SELECT POINTS FROM CHALLENGES WHERE ID=:id");
		$cp->bindParam(':id', $check['CHALLENGE_ID']);
		$cp->execute();
		$cp = intval($cp->fetchAll()[0][0]);
		$p += $cp;	// Add.

		// Update.
		$update = $pdo->prepare("UPDATE USERS SET POINTS=:p WHERE ID=:id");
		$update->bindParam(':p', $p);
		$update->bindParam(':id', $check['USER_ID']);
		$update->execute();

		// Set the submission as validated.
		$validate = $pdo->prepare("UPDATE SUBMITS SET STATE=1 WHERE ID=:id");
		$validate->bindParam(':id', $_GET['id']);
		$validate->execute();

		// Make the new.
		$newv = $pdo->prepare("INSERT INTO NEWS (USER_ID, CHALLENGE_ID, TYPE, DATE) VALUES (:uid, :cid, :t, :d)");
		$newv->bindParam(':uid', $check['USER_ID']);
		$newv->bindParam(':cid', $check['CHALLENGE_ID']);
		$newv->bindParam(':t', 1);
		$newv->bindParam(':d', cc_time());
		$newv->execute();
	} elseif(isset($_GET['r'])) {
		// Deny. Remove the submission.
		$reject = $pdo->prepare("DELETE FROM SUBMITS WHERE ID=:id");
		$reject->bindParam(':id', $_GET['id']);
		$reject->execute();

		// Make the new.
		$newv = $pdo->prepare("INSERT INTO NEWS (USER_ID, CHALLENGE_ID, TYPE, DATE) VALUES (:uid, :cid, :t, :d)");
		$newv->bindParam(':uid', $check['USER_ID']);
		$newv->bindParam(':cid', $check['CHALLENGE_ID']);
		$newv->bindParam(':t', 0);
		$newv->bindParam(':d', cc_time());
		$newv->execute();
	}
	header('Location: review.php');
}

$_SESSION['CC_csrf'] = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));
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
		<p><a href='/'>[Go back]</a></p>

		<h1>REVIEW</h1>
		<hr>

		<p class='center'>Showing only the oldest 25 submissions.</p>
		<table>
			<tr>
				<th>Challenge</th>
				<th>User</th>
				<th>Date</th>
				<th>Actions</th>
			</tr>

			<?php
			$subs = $pdo->prepare("SELECT * FROM SUBMITS WHERE STATE=0 ORDER BY ID ASC LIMIT 25");
			$subs->execute();
			foreach($subs as $sub) {
				echo '<tr>';

				$cname = $pdo->prepare("SELECT NAME FROM CHALLENGES WHERE ID=:id");
				$cname->bindParam(':id', $sub['CHALLENGE_ID']);
				$cname->execute();
				$cname = $cname->fetchAll()[0][0];
				echo '<td>'.$cname.'</td>';

				$uid = $pdo->prepare("SELECT USERNAME FROM USERS WHERE ID=:id");
				$uid->bindParam(':id', $sub['USER_ID']);
				$uid->execute();
				$uid = $uid->fetchAll()[0][0];
				echo '<td>'.$uid.'</td>';

				echo '<td>'.timeFormat($sub['DATE']).'</td>';

				echo '<td>';
				echo '<a href=\''.$sub['URL'].'\'>[URL]</a> ';
				if($sub['USER_ID'] != $_SESSION['CC']) {
					echo '<a href=\'?id='.$sub['ID'].'&v&csrf='.$_SESSION['CC_csrf'].'\'>[Validate]</a> ';
					echo '<a href=\'?id='.$sub['ID'].'&r&csrf='.$_SESSION['CC_csrf'].'\'>[Reject]</a>';
				}
				echo '</td>';

				echo '</tr>';
			}
			?>
		</table>
	</body>
</HTML>
