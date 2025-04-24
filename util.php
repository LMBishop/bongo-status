<?php
namespace Util;

class ServiceStatus
{
    public $name;
    public $containerId;
    public $status;
    public $startedAt;
    public $finishedAt;
    public $isNotFound;

    public function __construct($name, $containerId, $status, $startedAt, $finishedAt, $isNotFound)
    {
        $this->name = $name;
        $this->containerId = $containerId;
        $this->status = $status;
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;
	$this->isNotFound = $isNotFound;
    }
}

function getDockerStatus($containerName): ServiceStatus
{
    $dockerOutput = exec('sudo docker inspect --format=\'{{.Id}} {{.State.Status}} {{.State.StartedAt}} {{.State.FinishedAt}}\' ' . $containerName);
    if (empty($dockerOutput)) {
        return new ServiceStatus($containerName, '-', '-', '-', '-', true);
    }
    $parts = explode(' ', $dockerOutput);
    $status = new ServiceStatus($containerName, substr($parts[0], 0, 12), $parts[1], $parts[2], $parts[3], false);
    return $status;
}

function createStatusTable(ServiceStatus $status)
{
    echo ('<table>');
    echo ('<tr><th>Container ID</th><th>Name</th><th>Status</th><th>Started at</th><th>Finished at</th></tr>');
    echo ('<tr>');
    echo ('<td>' . $status->containerId . '</td>');
    echo ('<td>' . $status->name . '</td>');
    echo ('<td>' . $status->status . '</td>');
    echo ('<td>' . $status->startedAt . '</td>');
    echo ('<td>' . $status->finishedAt . '</td>');
    echo ('</tr>');
    echo ('</table>');
}

function createStatusBanner(ServiceStatus $status)
{
    if ($status->isNotFound) {
        createBanner('✗', "Container '" . $status->name . "' not found", 'bad');
        return;
    }
    $state = $status->status === 'running' ? 'good' : 'bad';
    $symbol = $status->status === 'running' ? '✓' : '✗';
    createBanner($symbol, "Status of '$status->name' is '$status->status'", $state);
}

function createBanner($symbol, $message, $state)
{
    echo ('<div class="status-banner ' . $state . '">');
    echo ("<p><b>$symbol</b> $message</p>");
    echo ('</div>');
}

function doShellExec($command, $redirect, $action)
{
    $output = shell_exec($command);
    //if (empty($output)) {
    //    header("Location: $redirect");
    //    exit;
    //}
    echo "<p>Output of $action</p>";
    echo "<pre>$output</pre>";
    echo "<p class='control-list'><a href='$redirect'>[Acknowledge]</a></p>";
    exit;
}

function doSessionCheck($redirect)
{
    if (!isset($_SESSION['token']) || $_SESSION['token'] !== getSuperSecretToken()) {
        header('Location: authenticate.php?redirect=/' . $redirect);
        exit;
    }
}

include('key.php');

function getSuperSecretToken()
{
    global $superSecretToken;
    return $superSecretToken;
}
