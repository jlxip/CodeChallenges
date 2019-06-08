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


echo '<a href=\'admin.php\'>[Go back]</a><br><br>';

$log = $pdo->prepare("SELECT DATE, CONTENT FROM LOG ORDER BY ID DESC");
$log->execute();
$log = $log->fetchAll();
foreach($log as $entry) {
	echo '['.timeFormat($entry['DATE']).'] '.$entry['CONTENT'];
	echo '<br>';
}
?>
