<?php
require_once('util.php');
require_once('serviceDefinitions.php');

session_start();

$service = $_GET['service'];

Util\doSessionCheck('mount.php?service=' . $service);

$serviceDefinition = ServiceDefinitions\getServiceDefinition($service);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Mount LUKS device for <?php echo $service ?></title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="container">
        <h1>Mount LUKS device for <?php echo $service ?></h1>
        <a href="index.php">Home</a>
        <a href="status.php">Status</a>
        <hr>
        <?php

        if ($serviceDefinition === null) {
            Util\createBanner('✗', "There is no service definition for '" . $service . "'", 'bad');
            return;
        }

        $luksDevice = $serviceDefinition->luks;

        if ($luksDevice === null) {
            Util\createBanner('✗', $service . ' has no LUKS device to mount', 'bad');
            return;
        }

        $key = $_POST['key'];
        $mount = $_GET['mount'];

        $disk = exec('blkid /dev/' . $luksDevice->deviceName . ' | grep "UUID=\"' . $luksDevice->uuid . '\""');
        $diskOk = !empty($disk);

        $cryptdevice = exec('lsblk -lno NAME,TYPE,MOUNTPOINT /dev/' . $luksDevice->deviceName . ' | grep "' . $luksDevice->mountPoint . '[[:space:]]*crypt"');
        $cryptdeviceOk = !empty($cryptdevice);

        $cryptdeviceMapping = exec('lsblk -lno NAME,TYPE,MOUNTPOINT /dev/' . $luksDevice->deviceName . ' | grep "crypt" | awk \'{print $1}\'');

        $mountpoint = exec('cat /proc/mounts | grep "/dev/mapper/' . $luksDevice->mountPoint . ' /mnt/' . $luksDevice->mountPoint . '"');
        $mountpointOk = !empty($mountpoint);

        if (!empty($key) && $diskOk && !$cryptdeviceOk) {
            $safeKey = escapeshellarg($key);
            Util\doShellExec('echo ' . $safeKey . ' | sudo cryptsetup --verbose luksOpen /dev/' . $luksDevice->deviceName . ' ' . $luksDevice->mountPoint . ' 2>&1', '/mount.php?service=' . $service, 'cryptsetup');
        }

        if (!empty($mount) && $diskOk && $cryptdeviceOk && !$mountpointOk) {
            Util\doShellExec('sudo mount -v /dev/mapper/' . $luksDevice->mountPoint .  ' /mnt/' . $luksDevice->mountPoint, '/mount.php?service=' . $service, 'mount');
        }

        if (!$diskOk) {
            Util\createBanner('✗', '/dev/' . $luksDevice->deviceName . ' is not attached or has incorrect UUID', 'bad');
            echo '<p>Attach /dev/' . $luksDevice->deviceName . ' with UUID="' . $luksDevice->uuid . '" to continue.</p>';
            return;
        } else {
            Util\createBanner('✓', '/dev/' . $luksDevice->deviceName . ' is attached', 'good');
        }

        if (!$cryptdeviceOk) {
            if (!empty($cryptdeviceMapping)) {
                Util\createBanner('✗', "/dev/" . $luksDevice->deviceName . " has incorrect mapping '" . $cryptdeviceMapping. "'", 'bad');
                echo '<p>Cannot continue. Close luks device /dev/' . $luksDevice->deviceName . ' first.</p>';
                return;
            }
            Util\createBanner('✗', '/dev/' . $luksDevice->deviceName . ' is locked', 'bad');
            echo "<p>";
            echo "Provide the encryption key for /dev/" . $luksDevice->deviceName . " (" . $luksDevice->uuid . ")";
            echo "</p>";
            echo "<form method='POST'>";
            echo "<fieldset>";
            echo "<legend>Unlock /dev/" . $luksDevice->deviceName . "</legend>";
            echo "<label for='key'>Key: </label>";
            echo "<input type='password' id='key' name='key'><br><br>";
            echo "<input type='submit' value='Go'>";
            echo "</fieldset>";
            echo "</form>";
            return;
        } else {
            Util\createBanner('✓', "/dev/" . $luksDevice->deviceName . " is open and has mapping '" . $luksDevice->mountPoint . "'", 'good');
        }

        if (!$mountpointOk) {
            Util\createBanner('✗', '/dev/mapper/' . $luksDevice->mountPoint . ' is not mounted at /mnt/' . $luksDevice->mountPoint, 'bad');
            echo "<p>Mount /dev/mapper/" . $luksDevice->mountPoint . " at /mnt/" . $luksDevice->mountPoint . ".</p>";
            echo "<p class='control-list'><a href='/mount.php?service=" . $service . "&mount=1'>[Mount device]</a></p>";
            return;
        } else {
            Util\createBanner('✓', '/dev/mapper/' . $luksDevice->mountPoint . ' is mounted at /mnt/' . $luksDevice->mountPoint, 'good');
        }
        ?>
        <p>There is nothing to do.</p>
    </div>
</body>
