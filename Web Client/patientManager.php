<?php
require "authCheck.php";
require "config.php";

if (isset($_GET["action"]) && isset($_GET["destination"])) {
	$conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);
	$device = mysqli_real_escape_string($conn,  $_GET["destination"]);
	$deviceClean = filter_var($device, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	if ($_GET["action"] === "addPatient"){
		$conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);

    	$name = mysqli_real_escape_string($conn, $_GET["name"]);
		$nameclean = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

		$query = "INSERT INTO pazienti (NOME, DISPOSITIVO) VALUES (\"$nameclean\", \"$deviceClean\")";
		$result = $conn->query($query);
		

	}
	if ($_GET["action"] === "removePatient"){
		$conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);

		$query = "DELETE FROM pazienti WHERE DISPOSITIVO = \"$deviceClean\"";
		$result = $conn->query($query);
	}
	$conn->close();
}
header("Location: /");
?>