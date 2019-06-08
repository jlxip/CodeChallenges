<?php
session_start();
if(!isset($_SESSION['CC'])) {
	header('Location: /');
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

if($umode != '2') {
	echo 'Only administrators can access the panel.';
	exit();
}

if(!isset($_SESSION['CC_csrf'])) { $_SESSION['CC_csrf'] = 0; }

if(isset($_GET['id'])) {
	if($_GET['csrf'] != $_SESSION['CC_csrf']) {
		echo 'CSRF token does not match.<br>';
		echo 'It was close...<br>';
		exit();
	}

	$other = $pdo->prepare("SELECT USERNAME FROM USERS WHERE ID=:id");
	$other->bindParam(':id', $_GET['id']);
	$other->execute();
	$other = $other->fetchAll()[0][0];

	$toWrite = $uname.' made '.$other.' ';

	$update = 0;
	if(isset($_GET['r'])) {
		// Make regular.
		$update = $pdo->prepare("UPDATE USERS SET MODE=0 WHERE ID=:id");
		$toWrite .= 'regular';
	} elseif(isset($_GET['s'])) {
		// Make staff.
		$update = $pdo->prepare("UPDATE USERS SET MODE=1 WHERE ID=:id");
		$toWrite .= 'staff';
	} elseif(isset($_GET['a'])) {
		// Make admin.
		$update = $pdo->prepare("UPDATE USERS SET MODE=2 WHERE ID=:id");
		$toWrite .= 'admin';
	} else {
		echo 'Whooops!';
		exit();
	}
	$update->bindParam(':id', $_GET['id']);
	$update->execute();

	$log = $pdo->prepare("INSERT INTO LOG (DATE, CONTENT) VALUES (:d, :c)");
	$log->bindParam(':d', cc_time());
	$log->bindParam(':c', $toWrite);
	$log->execute();
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
		<title>Manager Users</title>
	</head>
	<body>
		<p><a href='admin.php'>[Go back]</a></p>

		<form action method='GET'>
			<p class='center'>
				Search by part of the name.<br>
				Leave in blank to show all users.
			</p><br>
			<input type='text' name='uname'><br>
			<input type='submit' value='Search'>
		</form>
		<br>

		<?php
		if(isset($_GET['uname'])) {
			$like = '%'.$_GET['uname'].'%';
			$query = $pdo->prepare("SELECT * FROM USERS WHERE USERNAME LIKE :u");
			$query->bindParam(':u', $like);
			$query->execute();
			if($query = $query->fetchAll()) {
				echo '<table>';
				echo '<tr><th>Username</th><th>Points</th><th>Mode</th><th>Actions</th></tr>';
				foreach($query as $row) {
					echo '<tr>';
					echo '<td>'.$row['USERNAME'].'</td>';
					echo '<td>'.$row['POINTS'].'</td>';
					switch($row['MODE']) {
						case '0':
							echo '<td>Regular</td>';
							echo '<td>';
							echo '<a href=\'?id='.$row['ID'].'&s&csrf='.$_SESSION['CC_csrf'].'&uname='.$_GET['uname'].'\'>[Make staff]</a> ';
							echo '<a href=\'?id='.$row['ID'].'&a&csrf='.$_SESSION['CC_csrf'].'&uname='.$_GET['uname'].'\'>[Make admin]</a>';
							echo '</td>';
							break;
						case '1':
							echo '<td>Staff</td>';
							echo '<td>';
							echo '<a href=\'?id='.$row['ID'].'&r&csrf='.$_SESSION['CC_csrf'].'&uname='.$_GET['uname'].'\'>[Make regular]</a> ';
							echo '<a href=\'?id='.$row['ID'].'&a&csrf='.$_SESSION['CC_csrf'].'&uname='.$_GET['uname'].'\'>[Make admin]</a>';
							echo '</td>';
							break;
						case '2':
							echo '<td>Admin</td>';
							echo '<td>';
							echo '<a href=\'?id='.$row['ID'].'&r&csrf='.$_SESSION['CC_csrf'].'&uname='.$_GET['uname'].'\'>[Make regular]</a> ';
							echo '<a href=\'?id='.$row['ID'].'&s&csrf='.$_SESSION['CC_csrf'].'&uname='.$_GET['uname'].'\'>[Make staff]</a>';
							echo '</td>';
							break;
					}
					echo '</tr>';
				}
			} else {
				echo '<p class=\'center\'>No results found.</p>';
			}
		}
		?>
	</body>
</HTML>
