<?php
require "authCheck.php";
require "commonTop.php";
require "config.php";


if (isset($_GET["resetPassword"])){
	
}

$passStatus;

if ($_POST["action"] === "updatePassword"){
	$name = mysqli_real_escape_string($conn, $_GET["resetPassword"]);
	$nameclean = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);


	$conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);
    $name = mysqli_real_escape_string($conn, $_SESSION["user_id"]);
	$nameclean = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	$pass = mysqli_real_escape_string($conn, $_POST['oldPassword']);
	
    $query = "SELECT * FROM users WHERE USERNAME=\"$nameclean\"";
  	$result = $conn->query($query);
  	$row = $result->fetch_assoc();
  	
	if ($result->num_rows > 0 && password_verify($pass, $row['PASSWORD']) ) {
		$pass = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
		$query = "UPDATE users SET PASSWORD=\"$pass\" WHERE USERNAME=\"$nameclean\"";
		$conn->query($query);
		$passStatus = 2;
	} else {
		$passStatus = 1;
	}
	$conn->close();
}
?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item active mr-auto  mt-auto mb-auto" aria-current="page">Cambia password</li>	
  </ol>
</nav>

<?php if ($passStatus === 1): ?>
<div class="alert alert-danger" role="alert">
  La vecchia password inserita è errata</b>
</div>
<?php 
endif; 
if ($passStatus === 2): ?>
<div class="alert alert-success" role="alert">
  La password &egrave; stata cambiata.</b>
</div>
<?php endif; ?>

<form method="POST">
  <input type="hidden" name="action" value="updatePassword">
  <div class="form-group">
    <label for="oldPassword">Password attuale</label>
    <input type="password" class="form-control" id="oldPassword" name="oldPassword" placeholder="Password" required>
  </div>
  <div class="form-group">
    <label for="newPassword">Nuova password</label>
    <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Password" required>
  </div>
  <div class="form-group">
    <label for="confirmPassword">Conferma password</label>
    <input type="password" class="form-control" id="confirmPassword" placeholder="Password" required>
  </div>
  <button type="submit" class="btn btn-primary">Cambia password</button>
</form>

<script>
var password = document.getElementById("newPassword"), confirm_password = document.getElementById("confirmPassword");

function validatePassword(){
  if(password.value != confirm_password.value) {
    confirm_password.setCustomValidity("Le password non corrispondono");
  } else {
    confirm_password.setCustomValidity('');
  }
}

password.onchange = validatePassword;
confirm_password.onkeyup = validatePassword;
</script>
