<?php
session_start();

if(!isset($_SESSION['CC'])) {
	header('Location: /');
	exit();
}

include 'db_connect.php';
include 'aux.php';

// Get some user info (aka ui).
$ui = $pdo->prepare("SELECT USERNAME, MODE, POINTS FROM USERS WHERE ID=:id");
$ui->bindParam(':id', $_SESSION['CC']);
$ui->execute();
if(!($ui = $ui->fetchAll())) {
	session_destroy();
	header('Location: /');
	exit();
}
$ui = $ui[0];

// Remove news.
if(isset($_GET['ok'])) {
	// Make sure the new is linked to the user.
	$delete = $pdo->prepare("DELETE FROM NEWS WHERE ID=:id AND USER_ID=:uid");
	$delete->bindParam(':id', $_GET['ok']);
	$delete->bindParam(':uid', $_SESSION['CC']);
	$delete->execute();
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
		<title>Code Challenges</title>
	</head>
	<body>
		<p><a href='logout.php'>[Log out]</a> <a href='styles.php'>[Styles]</a>
		<?php
		if($ui['MODE'] != '0') {
			echo ' <a href=\'newchallenge.php\'>[New challenge]</a> ';
			echo '<a href=\'review.php\'>[Review]</a> ';
		}
		if($ui['MODE'] == '2') {
			echo '<a href=\'admin.php\'>[Admin panel]</a>';
		}
		?>
		</p>

		<h3>
			Welcome, <?php echo $ui['USERNAME']; ?>.<br>
			You currently have <?php echo $ui['POINTS']; ?> points.
		</h3>

		<div id='news'>
			<h3>News</h3>
			<hr>

			<?php
			$news = $pdo->prepare("SELECT * FROM NEWS WHERE USER_ID=:id");
			$news->bindParam(':id', $_SESSION['CC']);
			$news->execute();
			if($news = $news->fetchAll()) {
				echo '<ul>';
				foreach($news as $new) {
					echo '<li>';
					$cname = $pdo->prepare("SELECT NAME FROM CHALLENGES WHERE ID=:id");
					$cname->bindParam(':id', $new['CHALLENGE_ID']);
					$cname->execute();
					if($cname = $cname->fetchAll()) {
						$cname = $cname[0][0];
						switch($new['TYPE']) {
							case '0':
								// Rejected.
								echo 'Your solution to "'.$cname.'" was rejected. ';
								break;
							case '1':
								// Accepted.
								echo 'Your solution to "'.$cname.'" was accepted! ';
								break;
						}
						echo timeFormat($new['DATE']);
						echo ' <a href=\'?ok='.$new['ID'].'\'>[OK]</a>';
					} else {
						// This challenge has been removed.
						// Remove ALL the news from ALL the users regarding that challenge.
						$delete = $pdo->prepare("DELETE FROM NEWS WHERE CHALLENGE_ID=:id");
						$delete->bindParam(':id', $new['CHALLENGE_ID']);
						$delete->execute();
					}
					echo '</li>';
				}
				echo '</ul>';
			} else {
				echo '<p class=\'center\'>No news.</p>';
			}
			?>
		</div>

		<h1><a href='challenges.php'>Go to the challenges</a></h1>

		<h1>TOP 10 SOLVERS</h1>
		<table>
			<tr>
				<th>Name</th>
				<th>Points</th>
			</tr>
			<?php
			$top = $pdo->prepare("SELECT USERNAME, POINTS FROM USERS WHERE POINTS != 0 ORDER BY POINTS DESC LIMIT 10");
			$top->execute();
			if($top = $top->fetchAll()) {
				foreach($top as $u) {
					echo '<tr>';
					echo '<td>'.$u['USERNAME'].'</td>';
					echo '<td>'.$u['POINTS'].'</td>';
					echo '</tr>';
				}
			}
			?>
		</table>
	</body>
</HTML>
