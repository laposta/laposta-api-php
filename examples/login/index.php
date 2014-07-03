<?php
require_once('../../lib/Laposta.php');
Laposta::setApiKey("JdMtbsMq2jqJdQZD9AHC");

if ($_POST) {

	$msg = '';

	// initialize login object
	$login = new Laposta_Login();

	try {
		// get login result, use login and password as argument
		// $result will contain een array with the response from the server
		$result = $login->get($_POST['login'], $_POST['password']);

		// redirect to given url
		$url = $result['login']['url'];
		header('Location: ' . $url, true, 302); 
		exit;

	} catch (Exception $e) {

		// every error has a response code, so you can formulate your own messages:
		// http://api.laposta.nl/doc/#login

		// here we'll just show the api message
		$msg = $e->json_body['error']['message'];
	}
}
?>
<!DOCTYPE html>
<head>
<title>Inloggen</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>

<body>
<form method="post">
<h2>Inloggen</h2>
<? // display message
if ($msg) print '<p style="color:red">' . $msg . '</p>' ?>
<p>
U kunt hier inloggen met de gegevens die u van ons hebt gekregen.
</p>
<p>
<label>Login</label><br /><input type="text" name="login" id="login" size="30" maxlength="255" />
</p>
<p>
<label>Wachtwoord</label><br /><input type="password" name="password" size="30" maxlength="50" />
</p>
<p>
<input type="submit" name="submit" value="Login" />
</p>
</form>
<script>document.getElementById('login').focus();</script>
</body>
</html>
