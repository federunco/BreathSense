<?php
require "authCheck.php";
require "commonTop.php";
require "config.php";

?>

<div class="jumbotron">
  <h1 class="display-4">Ciao <?php echo $_SESSION['user_name']; ?></h1>
  <hr class="my-4">
  <p>Monitora dispositivo specifico</p>
  <form method="get" action="startMonitoring.php">
	  <div class="form-group">
	    <input type="text" class="form-control" id="exampleInputEmail1" name="destination" aria-describedby="emailHelp" placeholder="ID Dispositivo">
	    <small id="emailHelp" class="form-text text-muted">L'ID Dispositivo è una stringa alfanumerica lunga 12 caratteri</small>
	  </div>
	  <button type="submit" class="btn btn-primary">Monitora</button>
	</form>
</div>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item active mr-auto  mt-auto mb-auto" aria-current="page">Gestione pazienti</li>
	<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
	  <i class="fas fa-user-plus"></i>
	  <span class="ml-2">Aggiungi paziente</span>
	</button>
  </ol>
</nav>

<ul class="list-group">
	<?php
	$conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);
	if ($conn->connect_error) {
	    die("Connessione fallita: " . $conn->connect_error);
	}

	$sql = "SELECT * FROM pazienti";
	$result = $conn->query($sql);

	if ($result->num_rows > 0) {
	    while($row = $result->fetch_assoc()):
	    ?>
	       <li class="list-group-item d-flex justify-content-between align-items-center">
		  	<span class="mr-auto"><?php echo $row["NOME"]; ?></span>
		  	<button type="button" class="btn btn-danger mr-2" onClick="removeDevice('<?php echo $row["DISPOSITIVO"];?>')">
		  	  <i class="fas fa-trash-alt"></i>
			 <span class="ml-2">Rimuovi</span>
			</button>
		  	<a type="button" class="btn btn-primary" href="/startMonitoring.php?destination=<?php echo $row["DISPOSITIVO"];?>" data-toggle="tooltip" data-placement="left" title="Ricarica dispositivo"><i class="fas fa-stethoscope"></i><span class="ml-2">Inizia monitoraggio</span></a>
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
      <form action="/patientManager.php">
	  <div class="modal-body">
	  	L'eliminazione di un paziente &egrave; irreversibile, una volta eliminato si pu&ograve; collegare il dispositivo ad un altro paziente.<br>
      	Continuare?
	  	<input type="hidden" name="action" value="removePatient">
	  	<input type="hidden" name="destination" id="removeDeviceDetail">
      </div>
	  <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
        <button type="submit" class="btn btn-danger">Procedi con l'eliminazione</button>
      </div>
	</form>
    </div>
  </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Aggiungi paziente</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="/patientManager.php">
	  <div class="modal-body">
        <div class="form-group">
        	<input type="hidden" name="action" value="addPatient">
		    <label for="patientName">Nome paziente</label>
		    <input type="text" name="name" class="form-control" id="patientName" placeholder="Nome Cognome" required>
		  </div>
		  <div class="form-group">
		    <label for="patientDevice">ID Dispositivo</label>
		    <input type="text" class="form-control" name="destination" id="patientDevice" placeholder="AABBCCDDEEFF" required>
		  </div>
      </div>
	  <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>
        <button type="submit" class="btn btn-primary">Salva cambiamenti</button>
      </div>
	</form>
    </div>
  </div>
</div>

<script>
	function removeDevice(device){
		console.log(device);
		document.getElementById("removeDeviceDetail").value = device;
		$('#removeModal').modal();
	}
</script>