<?php
require "authCheck.php";
require "commonTop.php";
require "config.php";
require "awsiot.php";

$patientID = $_GET["destination"];
$finalSPO2Topic = "breathsense/$patientID/spo2";
$finalBPMTopic = "breathsense/$patientID/bpm";
$finalStatusTopic = "breathsense/$patientID/device";
$finalPPGTopic = "breathsense/$patientID/ppg";
?>

<style>
  .paused::after{
    position: absolute;
    top: 0px;
    bottom: 0px;
    content: "";
    width: 100%;
    height: 100%;
    display: flex;
    -ms-flex-direction: column;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: rgba(255,255,255,0.7);
    background-clip: border-box;
    align-items: center;
    justify-content: center;
    background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAgAAAAIACAMAAADDpiTIAAAAA3NCSVQICAjb4U/gAAAACXBIWXMAABoPAAAaDwFOtF2YAAAAGXRFWHRTb2Z0d2FyZQB3d3cuaW5rc2NhcGUub3Jnm+48GgAAAtlQTFRF////AQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACAQACsX3ZHQAAAPJ0Uk5TAAECAwQFBgcICQoLDA0ODxAREhMUFRYXGBkaGxweHyAhIiMkJSYnKCkqKy0u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);
    background-size: 150px 150px;
    background-repeat: no-repeat;
    background-position: center;
  }
}
</style>
  

  <div class="d-flex align-items-center mt-3">
  	<div class="mr-auto mt-3">
      <?php
      $conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);
      $device = mysqli_real_escape_string($conn,  $_GET["destination"]);
      $deviceClean = filter_var($device, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
  
      $query = "SELECT * FROM pazienti WHERE DISPOSITIVO=\"$deviceClean\"";
      $result = $conn->query($query);
      $row = $result->fetch_assoc();
      if ($result->num_rows > 0):?>
        <h3>Monitorazione <?php echo $row["NOME"]; ?></h3>
        <?php else: ?>
        <h3>Monitorazione Paziente non registrato</h3>
      <?php endif;
      $conn->close();
      ?>
  	</div>
  	
	<h1 id="deviceStatus" class="badge badge-pill badge-secondary mb-0 mr-2" style="font-size: 1em">Stato: non disponibile</h1>
  <button type="button" class="btn btn-primary" onclick="javascript:location.href='/startMonitoring.php?destination=<?php echo $patientID;?>'" data-toggle="tooltip" data-placement="left" title="Ricarica dispositivo"><i class="fas fa-sync"></i></button>
	</div>

  <div class="toast fade hide" style="position: absolute; top: 70px; right: 20px; z-index: 1100" id="toast" data-delay="10000">
    <div class="toast-header">
      <i class="fas fa-exclamation-triangle mr-2"></i>
      <strong class="mr-auto">Presta attenzione</strong>
      <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class="toast-body">
      I valori di SpO2 e BPM si devono stabilizzare, i valori registrati nei primi istanti in cui si è appoggiato il dito al sensore non sono attendibili
    </div>
  </div>


  
<div class="card mt-3" id="spo2Card">
  <div class="card-body">
    <h5 class="card-title text-success"><i class="fas fa-lungs mr-2"></i>Monitorazione SpO2</h5>
    <h1 id="currentSPO2">SpO2 attuale: N/D</h1>
    <div id="graph_spo2" style=" height:250px;"></div>
  </div>
</div>

<div class="card mt-3" id="bpmCard">
  <div class="card-body">
    <h5 class="card-title text-danger"><i class="fas fa-heartbeat mr-2"></i>Monitorazione BPM</h5>
    <h1 id="currentBPM">Battiti attuali: N/D</h1>
    <div id="graph_bpm" style=" height:250px;"></div>
  </div>
</div>

<div class="card mt-3" id="ppgCard">
  <div class="card-body">
    <h5 class="card-title text-danger"><i class="fas fa-wave-square mr-2"></i>Monitorazione PPG<button type="button" id="ppgToggleBtn" class="float-right btn btn-danger" onclick="togglePPG()">Abilita PPG</button>
</h5>
    <div id="graph_ppg" style=" height:250px;" class="mt-4"></div>
  </div>
</div>

<script src="http://dygraphs.com/dygraph.js"></script>
<script>
	$(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	})
	var endpoint = "<?php echo $finalEndpoint; ?>";
	var clientIdentifier = "webclient-<?php echo session_id(); ?>";
	var destinationIdentifier = "<?php echo $patientID;?>";

	var spo2Topic = "<?php echo $finalSPO2Topic;?>";
  var bpmTopic = "<?php echo $finalBPMTopic;?>";
  var ppgTopic = "<?php echo $finalPPGTopic;?>";
  var statusTopic = "<?php echo $finalStatusTopic;?>";

    var client = new Paho.MQTT.Client(endpoint, clientIdentifier);
    var connectOptions = {
      useSSL: true,
      timeout: 3,
      mqttVersion: 4,
      onSuccess: subscribe
    };
    client.connect(connectOptions);
    client.onMessageArrived = onMessage;
    client.onConnectionLost = function(e) { console.log(e) };
 
    function subscribe() {
      client.subscribe(spo2Topic);
      client.subscribe(bpmTopic);
      client.subscribe(statusTopic);
      client.subscribe(ppgTopic);
    }

    var oldTime = new Date();

    var data = [];
    var heartData = [];
    var ppgData = [];
    var t = new Date();
    for (var i = 100; i >= 0; i--) {
    	var x = new Date(t.getTime() - i * 1000);
        data.push([x, 0]);
        heartData.push([x, 0]);
        ppgData.push([x, 0]);
    }

    var g = new Dygraph(document.getElementById("graph_spo2"), data,
    {
    	drawPoints: true,
    	labels: ['Tempo', 'SpO2'],
    	color: 'green'
    });

    $(".toast").toast();
    $('#toast').toast('dispose');


    var gHeartbeat = new Dygraph(document.getElementById("graph_bpm"), data,
    {
    	drawPoints: true,
    	labels: ['Tempo', 'BPM'],
    	color: 'red'
    });

    var gPPG = new Dygraph(document.getElementById("graph_ppg"), data,
    {
      drawPoints: true,
      labels: ['Tempo', 'PPG'],
      color: 'red'
    });
    
    var fingerAttached = <?php echo $_GET["initState"]; ?>;
    if (fingerAttached) $("#toast").toast('show');
    document.getElementById("deviceStatus").innerHTML = "Stato: " + (fingerAttached ? "dito attaccato" : "dito staccato");
    document.getElementById("deviceStatus").className = "badge badge-pill mb-0 mr-2 badge-" + (fingerAttached ? "success" : "danger");
    document.getElementById("spo2Card").className = "card mt-3" + (fingerAttached ? "" : " paused");
    document.getElementById("bpmCard").className = "card mt-3" + (fingerAttached ? "" : " paused");
 
    var ppgEnabled = false;

    function togglePPG(){
      if (fingerAttached){
        ppgEnabled ^= 1;
        var ppgCMD = {"command": (ppgEnabled ? "enablePPG" : "disablePPG")};
        message = new Paho.MQTT.Message(JSON.stringify(ppgCMD));
        message.destinationName = statusTopic;
        document.getElementById("ppgToggleBtn").innerHTML = ppgEnabled ? "Disabilita PPG" : "Abilita PPG"
        client.send(message);
      }
      
    }

    function onMessage(message) {
    	var response = JSON.parse(message.payloadString);

      var x = new Date();

      if (message.destinationName === spo2Topic){
          document.getElementById("currentSPO2").innerHTML = "SpO2 attuale: " + parseFloat(response.spo2).toFixed(2) + "%";
          data.push([x, response.spo2]);
          data.shift();
          g.updateOptions( { 'file': data } );
      }

      if (message.destinationName === ppgTopic){
          ppgData.push([x, response.ppg]);
          ppgData.shift();
          gPPG.updateOptions( { 'file': ppgData } );
      }
    	
      if (message.destinationName === statusTopic){
        if (response.command.includes("finger")){
          fingerAttached = response.command.includes("fingerAttached") ? true : false;
          if (fingerAttached) $("#toast").toast('show');
          document.getElementById("deviceStatus").innerHTML = "Stato: " + (fingerAttached ? "dito attaccato" : "dito staccato");
          document.getElementById("deviceStatus").className = "badge badge-pill mb-0 mr-2 badge-" + (fingerAttached ? "success" : "danger");
          document.getElementById("spo2Card").className = "card mt-3" + (fingerAttached ? "" : " paused");
          document.getElementById("bpmCard").className = "card mt-3" + (fingerAttached ? "" : " paused");
          }
       }

       if (message.destinationName === bpmTopic){
          document.getElementById("currentBPM").innerHTML = "Battiti attuali: " + parseFloat(response.bpm).toFixed(2)+ " bpm";
          heartData.push([x, response.bpm]);
          heartData.shift();
          gHeartbeat.updateOptions( { 'file': heartData } );
        }
    }
    
</script>
