<?php
session_start();
?>
<!DOCTYPE html>
<html>
   <head>
      <meta charset="utf-8">
      <title>BreathSense</title>
      <link rel="stylesheet" href="/styles/common.css">
      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
      <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
 	  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css">

	</head>
	<body>
		<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top ">
			<button class="btn btn-outline-success dropdown-toggle ml-3 invisible " type="button" disabled>
    <i class="fas fa-user mr-3"></i><span class="d-none d-md-inline"><?php echo $_SESSION['user_name']; ?></span>
  </button>
  <a class="navbar-brand ml-auto mr-auto pt-0 pb-0" href="/"><img src="/images/branding.png" style="height: 2em"></a>
  
    <div class="dropdown">
  <button class="btn btn-outline-success dropdown-toggle ml-3" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fas fa-user mr-3"></i><span class="d-none d-md-inline"><?php echo $_SESSION['user_name']; ?></span>
  </button>
  <div class="dropdown-menu dropdown-menu-right text-muted" aria-labelledby="dropdownMenuButton" style="min-width: 20rem;">

  	<h6 class="dropdown-header">Amministrazione account</h6>
    <a class="dropdown-item" href="/settings.php">Impostazioni account</a>
    <div class="dropdown-divider"></div>
    <?php if ($_SESSION['user_role'] === "administrator"): ?>
    <h6 class="dropdown-header">Amministrazione server</h6>
    <a class="dropdown-item" href="/addUser.php">Crea utente</a>
    <a class="dropdown-item" href="/manageUsers.php">Gestione utenti</a>
    <div class="dropdown-divider"></div>
    <?php endif; ?>
    <a class="dropdown-item" href="login.php?action=logout">Esci</a>
    <p class="pl-4 pr-4 mt-3">
    	Identificatore sessione: <?php echo session_id(); ?>
  	</p>
 
  </div>
</div>

<style>
	.navbar-header {
    float: left;
    padding: 15px;
    text-align: center;
    width: 100%;
}
.navbar-brand {float:none;}
</style>
    
  </div>
</nav>
<main role="main" class="container" style="margin-top: 4.5rem">
