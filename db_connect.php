<?php
try {
	$pdo=new PDO('sqlite:/path/to/DB');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	ini_set('display_errors', '0');     // STFU
} catch(PDOException $e) {
	throw new Exception('Could not connect to database');
	exit();
}
?>
