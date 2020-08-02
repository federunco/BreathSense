<?php
require "authCheck.php";
require "commonTop.php";
require "config.php";
require "awsiot.php";
?>

<div id="loading">
  <div id="loadingImage"></div>
  <center><h1 id="status">Connessione al server in corso</h1></center>
</div>
<div id="unreachable" class="invisible">
  <center>
    <img src="/images/alert.jpg" width=300>
  	<h1 class="mt-4">Dispositivo non raggiungibile!</h1>
    <h3 class="mb-4">Assicurati che il dispositivo esista, che sia acceso e connesso alla rete</h3>
    <a type="button" class="btn btn-danger" href="/startMonitoring.php?destination=<?php echo $_GET["destination"];?>"><i class="fas fa-sync mr-3"></i>Riprova</a>
  </center>
</div>

<script>
	var endpoint = "<?php echo $finalEndpoint; ?>";
	var clientIdentifier = "webclient-<?php echo session_id(); ?>";
	var destinationIdentifier = "<?php echo $_GET["destination"];?>";
	var timeout = 5000;

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
      document.getElementById("status").innerHTML = "Interrogando il dispositivo di destinazione...";
      client.subscribe("breathsense/" + destinationIdentifier + "/device");
      console.log("Iscritto al topic di servizio, interrogo il dispositivo di destinazione");
      var pingJSON = {"command":"pingRequest"};
      message = new Paho.MQTT.Message(JSON.stringify(pingJSON));
      message.destinationName = "breathsense/" + destinationIdentifier + "/device";
      client.send(message);
      setTimeout(destinazioneOffline, 5000);
    }

    function destinazioneOffline(){
    	console.log("destinazione non raggiungibile");
    	document.getElementById("loading").classList.toggle("invisible");
    	document.getElementById("unreachable").classList.toggle("invisible");
    	client.disconnect();
    }
 
    function onMessage(message) {
    	var response = JSON.parse(message.payloadString);
    	if (response.command == "acknowledgeOkFinger"){
    		console.log("Destinazione raggiunta - redirect");
    		window.location.href = '/patientView.php?destination=' + destinationIdentifier + "&initState=1";
    	}
      if (response.command == "acknowledgeNoFinger"){
        console.log("Destinazione raggiunta - redirect");
        window.location.href = '/patientView.php?destination=' + destinationIdentifier + "&initState=0";
      }
    }
    
</script>