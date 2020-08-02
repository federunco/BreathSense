<?php
require "config.php";
if ($_GET["action"] === "logout"){
	session_start();
	$_SESSION = array();
	session_destroy();
}

if (!empty($_POST)) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
    	$conn = new mysqli($SQL_SERVER, $SQL_USERNAME, $SQL_PASSWORD, $SQL_DATABASE, $SQL_PORT);
    	$name = mysqli_real_escape_string($conn, $_POST['username']);
		$nameclean = filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$pass = mysqli_real_escape_string($conn, $_POST['password']);
	
    	$query = "SELECT * FROM users WHERE USERNAME=\"$nameclean\"";
  		$result = $conn->query($query);
  		$row = $result->fetch_assoc();
  		$conn->close();
		if ($result->num_rows > 0 && password_verify($_POST['password'], $row['PASSWORD']) ) {
			session_start();
			session_regenerate_id();
			$_SESSION['user_id'] = $_POST['username'];
    		$_SESSION['user_name'] = $row['FULLNAME'];
    		$_SESSION['user_role'] = $row['ROLE'];
    		if (isset($_GET["redirect"])){
    			$redirect = $_GET["redirect"];
				header("Location: $redirect");
    		} else {
    			header("Location: /");
    		}
		} else {
    		$passwr = 2;
    	}
    }
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Accedi a BreathSense Console</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
 	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
  </head>

  <style>
  	html,
	body {
	  height: 100%;
	}

	body {
	  display: -ms-flexbox;
	  display: -webkit-box;
	  display: flex;
	  -ms-flex-align: center;
	  -ms-flex-pack: center;
	  -webkit-box-align: center;
	  align-items: center;
	  -webkit-box-pack: center;
	  justify-content: center;
	  padding-top: 40px;
	  padding-bottom: 40px;
	  background-color: #f5f5f5;
	  background-image: linear-gradient(0deg, #ebedee 0%, #fff 100%);
	}

	.form-signin {
	  width: 100%;
	  max-width: 330px;
	  padding: 15px;
	  margin: 0 auto;
	}
	.form-signin .checkbox {
	  font-weight: 400;
	}
	.form-signin .form-control {
	  position: relative;
	  box-sizing: border-box;
	  height: auto;
	  padding: 10px;
	  font-size: 16px;
	}
	.form-signin .form-control:focus {
	  z-index: 2;
	}
	.form-signin input[type="email"] {
	  margin-bottom: -1px;
	  border-bottom-right-radius: 0;
	  border-bottom-left-radius: 0;
	}
	.form-signin input[type="password"] {
	  margin-bottom: 10px;
	  border-top-left-radius: 0;
	  border-top-right-radius: 0;
	}
  </style>

  <body class="text-center">
    <form class="form-signin" method="post">
      <h1 class="h3 mb-3 font-weight-normal">Accedi a BreathSense Console</h1>
      <label for="username" class="sr-only">Nome utente</label>
      <input type="text" id="username" name="username" class="form-control" placeholder="Nome utente" required autofocus>
      <label for="password" class="sr-only">Password</label>
      <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
      <button class="btn btn-lg btn-primary btn-block" type="submit">Accedi</button>
      <?php if ($_GET["action"] === "logout"): ?>
      <div class="alert alert-info mt-4" role="alert">
		  Disconnessione effettuata
      </div>
  	  <?php endif; ?>
  	  <?php if (isset($passwr)): ?>
	  <div class="alert alert-danger mt-4" role="alert">
		  Username e/o password sbagliati!
	  </div>
	  <?php endif; ?>
	  </body>
    </form>

</html>
