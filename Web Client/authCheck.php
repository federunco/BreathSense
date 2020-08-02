<?php
session_start();
if (!isset($_SESSION['user_id'])) {
	header("Location: login.php?redirect=" . $_SERVER[REQUEST_URI]);
}
?>