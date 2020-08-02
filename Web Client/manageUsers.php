<?php
require "authCheck.php";
require "commonTop.php";
require "config.php";

session_start();
if ($_SESSION['user_role'] !== "administrator") {
	header("Location: /");
}
	
$conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);

$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
$pass = array(); 
$alphaLength = strlen($alphabet) - 1;
for ($i = 0; $i < 8; $i++) {
	$n = rand(0, $alphaLength);
	$pass[] = $alphabet[$n];
}
$genPass = implode($pass);

if (isset($_GET["resetPassword"])){
	$name = mysqli_real_escape_string($conn, $_GET["resetPassword"]);
	$nameclean = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	$pass = password_hash($genPass, PASSWORD_DEFAULT);
	$query = "UPDATE users SET PASSWORD=\"$pass\" WHERE USERNAME=\"$nameclean\"";
	$result = $conn->query($query);
}

if ($_POST["action"] === "removeUser"){
	$name = mysqli_real_escape_string($conn, $_POST["username"]);
	$nameclean = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
	$query = "DELETE FROM users WHERE USERNAME = \"$nameclean\"";
	$result = $conn->query($query);
}
$conn->close();

?>
<?php if (isset($_GET["resetPassword"])): ?>
<div class="alert alert-success" role="alert">
  La nuova password per <?php echo $_GET["resetPassword"]; ?> &egrave; <b><?php echo $genPass; ?></b>
</div>
<?php 
endif; 
if ($_POST["action"] === "removeUser"): ?>
<div class="alert alert-success" role="alert">
  L'utente <b><?php echo $_POST["username"]; ?> </b>&egrave; stato eliminato</b>
</div>
<?php endif; ?>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item active mr-auto  mt-auto mb-auto" aria-current="page">Gestione utenti</li>
    <a type="button" class="btn btn-primary" href="/addUser.php" data-toggle="tooltip" data-placement="left" title="Ricarica dispositivo"><i class="fas fa-plus"></i><span class="ml-2">Aggiungi utente</span></a>
	
  </ol>
</nav>

<ul class="list-group">
	<?php
	$conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);
	if ($conn->connect_error) {
	    die("Connessione fallita: " . $conn->connect_error);
	}

	$sql = "SELECT * FROM users";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()):
	    ?>
	       <li class="list-group-item d-flex justify-content-between align-items-center">
		  	<span class="mr-auto"><?php echo $row["FULLNAME"]; ?> - (<?php echo $row["USERNAME"]; ?> - <?php echo $row["ROLE"]; ?>)</span>
		  	<button type="button" class="btn btn-danger mr-2" onClick="removeDevice('<?php echo $row["USERNAME"];?>')">
		  	  <i class="fas fa-trash-alt"></i>
			 <span class="ml-2">Rimuovi</span>
			</button>
		  	<a type="button" class="btn btn-primary" href="/manageUsers.php?resetPassword=<?php echo $row["USERNAME"];?>"><i class="fas fa-stethoscope"></i><span class="ml-2">Resetta password</span></a>
		  </li>
	    <?php
	    endwhile;
	} else {
	    echo "<center>Nessun paziente disponibile</center>";
	}
	$conn->close();
	?>
</ul>

<div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Conferma azione</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="POST">
	  <div class="modal-body">
	  	L'eliminazione di un utente &egrave; irreversibile, una volta eliminato l'utente questo non ha pi&ugrave; accesso alla console.<br>
      	Continuare?
	  	<input type="hidden" name="action" value="removeUser">
	  	<input type="hidden" name="username" id="username">
      </div>
	  <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
        <button type="submit" class="btn btn-danger">Procedi con la rimozione</button>
      </div>
	</form>
    </div>
  </div>
</div>

<script>
	function removeDevice(device){
		console.log(device);
		document.getElementById("username").value = device;
		$('#removeModal').modal();
	}
</script>
