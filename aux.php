<?php
/* DATES */
function cc_time() {
	date_default_timezone_set('UTC');
	return time();
}
function timeFormat($t) {
	date_default_timezone_set('UTC');
	return date('G:i - d M Y \(\U\T\C\)', $t);
}
/* END DATES */

/* STYLES */
function setStyle($name) {
	setcookie('style', $name, cc_time()+60*60*24*360);
}
function getStyle() {
	return $_COOKIE['style'];
}

if(!isset($_COOKIE['style'])) {
	setStyle('default');
	header('Location: .');
}

function echoFonts() {
	switch($_COOKIE['style']) {
		case 'default':
			echo 'Roboto+Mono|VT323';
			break;
		case 'sky':
			echo 'Roboto';
			break;
	}
}
/* END STYLES */
?>
