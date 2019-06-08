<?php
session_start();

if(!isset($_SESSION['CC'])) {
	header('Location: /');
	exit();
}

include 'db_connect.php';
include 'aux.php';

$page = 0;
if(isset($_GET['page'])) {
	$page = intval($_GET['page']);
}

// I'll need this in 'challenge.php' in order to go back.
$_SESSION['CC_page'] = $page;
?>

<!DOCTYPE HTML>

<HTML>
	<head>
		<meta charset='UTF-8'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0' />
		<link href='https://fonts.googleapis.com/css?family=<?php echoFonts(); ?>&display=swap' rel='stylesheet'>
		<link rel='stylesheet' type='text/css' href='css/normalize.css'>
		<link rel='stylesheet' type='text/css' href='css/<?php echo $_COOKIE['style']; ?>.css'>
		<link rel='stylesheet' type='text/css' href='css/main.css'>
		<title>Challenges</title>
	</head>
	<body>
		<p><a href='/'>[Go back]</a></p><br>

		<h1>CHALLENGES</h1>
		<hr>

		<?php
		echo '<p class=\'center\'><a href=\'challenges.php';
		if(isset($_GET['short'])) {
			echo '\'>[Show all]';
		} else {
			echo '?short\'>[Show only unsolved]';
		}
		echo '</a></p>';
		?>
		<table>
			<tr>
				<th>Name</th>
				<th>Date</th>
				<th>Creator</th>
				<th>Points</th>
			</tr>

			<?php
			$start = $page*25;
			$challs = $pdo->prepare("SELECT ID, NAME, POINTS, CREATOR, DATE FROM CHALLENGES WHERE ID > :m ORDER BY ID ASC LIMIT 25");
			$challs->bindParam(':m', $start);
			$challs->execute();
			$challs = $challs->fetchAll();
			$k = 0;
			foreach($challs as $chall) {
				if(isset($_GET['short'])) {
					// Check whether the user has solved it.
					$solved = $pdo->prepare("SELECT 1 FROM SUBMITS WHERE USER_ID=:uid AND CHALLENGE_ID=:cid");
					$solved->bindParam(':uid', $_SESSION['CC']);
					$solved->bindParam(':cid', $chall['ID']);
					$solved->execute();
					if($solved = $solved->fetchAll()) {
						// It is solved. Skip.
						continue;
					}
				}

				$creator = $pdo->prepare("SELECT USERNAME FROM USERS WHERE ID=:id");
				$creator->bindParam(':id', $chall['CREATOR']);
				$creator->execute();
				if($creator = $creator->fetchAll()) {
					$creator = $creator[0][0];
				} else {
					$creator = '[deleted]';
				}

				echo '<tr>';
				echo '<td><a href=\'challenge.php?id='.$chall['ID'].'\'>'.$chall['NAME'].'</a></td>';
				echo '<td>'.timeFormat(intval($chall['DATE'])).'</td>';
				echo '<td>'.$creator.'</td>';
				echo '<td>'.$chall['POINTS'].'</td>';
				echo '</tr>';
				$k++;
			}
			?>
		</table>

		<?php
		if($page != 0) {
			// A previous page is available.
			echo '<a href=\'challenges.php?page='.($page-1).'\'>[Previous page]</a>';
		}
		if($k == 25) {
			// Another page must show as available.
			echo '<a href=\'challenges.php?page='.($page+1).'\'>[Next page]</a>';
		}
		?>
	</body>
</HTML>
