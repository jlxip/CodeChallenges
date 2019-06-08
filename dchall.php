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

if(!isset($_GET['id'])) {
	header('Location: /');
	exit();
}

$cname = $pdo->prepare("SELECT NAME, POINTS FROM CHALLENGES WHERE ID=:id");
$cname->bindParam(':id', $_GET['id']);
$cname->execute();
if(!($cname=$cname->fetchAll())) {
	echo 'This challenge does not exist!<br>';
	echo '<a href=\'/\'>Go back.</a>';
	exit();
} else {
	$cpoints = intval($cname[0]['POINTS']);
	$cname = htmlspecialchars($cname[0]['NAME']);
}

if(isset($_GET['csrf'])) {
	if($_GET['csrf'] != $_SESSION['CC_csrf']) {
		echo 'Invalid CSRF token. Could not delete the challenge.';
		exit();
	}

	// Log.
	$logcontent = $uname.' deleted "'.$cname.'".';
	$log = $pdo->prepare("INSERT INTO LOG (DATE, CONTENT) VALUES (:d, :c)");
	$log->bindParam(':d', cc_time());
	$log->bindParam(':c', $logcontent);
	$log->execute();

	// Substract points to users.
	$solves = $pdo->prepare("SELECT ID, USER_ID FROM SUBMITS WHERE CHALLENGE_ID=:id");
	$solves->bindParam(':id', $_GET['id']);
	$solves->execute();
	if($solves = $solves->fetchAll()) {
		foreach($solves as $solve) {
			// Substract the points.
			// First get 'em.
			$oldp = $pdo->prepare("SELECT POINTS FROM USERS WHERE ID=:id");
			$oldp->bindParam(':id', $solve['USER_ID']);
			$oldp->execute();
			$oldp = intval($oldp->fetchAll()[0][0]);

			// Substract.
			$oldp -= $cpoints;

			// Update.
			$update = $pdo->prepare("UPDATE USERS SET POINTS=:p WHERE ID=:id");
			$update->bindParam(':p', $oldp);
			$update->bindParam(':id', $solve['USER_ID']);
			$update->execute();

			// Delete the row.
			$dsolve = $pdo->prepare("DELETE FROM SUBMITS WHERE ID=:id");
			$dsolve->bindParam(':id', $solve['ID']);
			$dsolve->execute();
		}
	}

	// Delete.
	$delete = $pdo->prepare("DELETE FROM CHALLENGES WHERE ID=:id");
	$delete->bindParam(':id', $_GET['id']);
	$delete->execute();

	echo 'Deleted and logged. You\'re on your own.<br>';
	echo '<a href=\'main.php\'>Go back.</a>';
	exit();
}

$_SESSION['CC_csrf'] = bin2hex(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM));
?>

Are you sure you want to delete challenge "<?php echo $cname; ?>"?!?!?!?!?!?!<br>
The points will be substracted to those who solved it.<br>
Bear in mind that your action will be logged.<br>

<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='?id=<?php echo $_GET['id']; ?>&csrf=<?php echo $_SESSION['CC_csrf']; ?>'>Yes.</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
<a href='challenge.php?id=<?php echo $_GET['id']; ?>'>No! Take me back!</a><br>
