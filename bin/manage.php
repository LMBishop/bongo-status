<?php
require_once('util.php');
require_once('serviceDefinitions.php');

session_start();

$container = $_GET['container'];
$action = $_GET['action'];

Util\doSessionCheck('manage.php?container=' . $container);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage container</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="container">
        <h1>Manage container: <?php echo $container ?></h1>
        <a href="index.php">Home</a>
        <a href="status.php">Status</a>
        <hr>
        <?php
        //if (empty($container)) {
        //    Util\createBanner('✗', 'No service specified', 'bad');
        //    return;
        //}
        //if (!in_array($service, array_map(function ($s) {
        //    return $s->name;
        //}, $services))) {
        //    Util\createBanner('✗', "Service '$service' is unknown", 'bad');
        //    return;
        //}

        $status = Util\getDockerStatus($container);

        if ($status->status === '-') {
            Util\createBanner('✗', "Container '$container' not found", 'bad');
            return;
        }
        
        if ($action === 'start' || $action === 'stop' || $action === 'restart' || $action === 'logs') {
        // if ($action === 'start' || $action === 'stop' || $action === 'restart') {
            $safeService = escapeshellarg($container);
            Util\doShellExec('sudo docker ' . $action . ' ' . $safeService, '/manage.php?container=' . $container, $action);
        }
        
        Util\createStatusBanner($status);
        ?>
        <p>
        <details>
            <summary>Status as reported by Docker</summary>
            <?php Util\createStatusTable($status); ?>
        </details>
        </p>

        <p class="control-list">
            <a href="manage.php?container=<?php echo $container ?>&action=logs">[Logs]</a>
            <a href="manage.php?container=<?php echo $container ?>&action=start">[Start]</a>
            <a href="manage.php?container=<?php echo $container ?>&action=stop">[Stop]</a>
            <a href="manage.php?container=<?php echo $container ?>&action=restart">[Restart]</a>
        </p>
    </div>
</body>
