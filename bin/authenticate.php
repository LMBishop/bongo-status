<?php
require_once('util.php');
require_once('serviceDefinitions.php');

session_start();

$redirect = $_GET['redirect'];
$token = $_POST['token'];

if (isset($_POST['token']) && $_POST['token'] === Util\getSuperSecretToken()) {
    $_SESSION['token'] = $_POST['token'];
    header('Location: ' . $redirect);
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Authenticate</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="container">
        <h1>Authenticate</h1>
        <hr>
	<p>Please enter the secret key to continue.</p>
	<form action="authenticate.php?redirect=<?php echo $redirect ?>" method="POST">
        Key: <input type="password" name="token">
        <input type="submit">
        </form>
    </div>
</body>
