<?php
require "authCheck.php";
require "commonTop.php";
require "config.php";

session_start();
if ($_SESSION['user_role'] !== "administrator") {
	header("Location: /");
}

if (!empty($_POST)) {
    if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['fullname'])) {
    	$conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);

    	$name = mysqli_real_escape_string($conn, $_POST['username']);
		$nameclean = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$namefull = mysqli_real_escape_string($conn, $_POST['fullname']);
		$namefullclean = filter_var($namefull, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$pass = password_hash(mysqli_real_escape_string($conn, $_POST['password']), PASSWORD_DEFAULT);

		$query = "INSERT INTO users (USERNAME, PASSWORD, FULLNAME, ROLE) VALUES (\"$nameclean\", \"$pass\", \"$namefullclean\", \"medico\")";
		$result = $conn->query($query);
		$conn->close();

		header("Location: /manageUsers.php");
    }
}

$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
$pass = array(); 
$alphaLength = strlen($alphabet) - 1;
for ($i = 0; $i < 8; $i++) {
	$n = rand(0, $alphaLength);
	$pass[] = $alphabet[$n];
}
$genPass = implode($pass);
?>

<form method="POST">
  <div class="form-group">
    <label for="name">Nome</label>
    <input type="text" class="form-control" id="name" required>
  </div>
  <div class="form-group">
    <label for="surname">Cognome</label>
    <input type="text" class="form-control" id="surname" required>
  </div>
  <input type="hidden" name="fullname" id="fullname">
  <div class="form-group">
    <label for="username">Nome utente</label>
    <input type="text" class="form-control" id="username" name="username" aria-describedby="userNameDesc" readonly value="">
    <small id="userNameDesc" class="form-text text-muted">Il nome utente &egrave; generato automaticamente e non pu&ograve; essere cambiato.</small>
  </div>
  <div class="form-group">
    <label for="password">Password</label>
    <input type="text" class="form-control" id="password" aria-describedby="passwordDesc" name="password" readonly value="<?php echo $genPass; ?>">
    <small id="passwordDesc" class="form-text text-muted">La password &egrave; stata generata automaticamente, potr&agrave; essere cambiata dall'utente finale dalle Impostazioni Account.</small>
  </div>
  
  <button type="submit" class="btn btn-primary">Registra utente</button>
</form>

<script>
	document.getElementById("name").addEventListener('change', generateUsername);
	document.getElementById("surname").addEventListener('change', generateUsername);

	function generateUsername(){
		var name = document.getElementById("name").value;
		var sur = document.getElementById("surname").value;
		var username = sur + name.substr(0, 3)
		username = username.replace(/\W/g, '').replace(" ", "").toLowerCase();

		document.getElementById("fullname").value = name + " " + sur;
		document.getElementById("username").value = username;
	}
</script>