<?php
require_once('util.php');
require_once('serviceDefinitions.php');
?>
<!DOCTYPE html>
<html>

<head>
    <title>Bongo status</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="container">
        <h1>Bongo status</h1>
        <a href="index.php">Home</a>
        <?php foreach ($services as $service) : ?>
            <hr>
            <?php
            echo '<h2>' . $service->prettyName . '</h2>';
            $containers = [];
            if (is_array($service->containerName)) {
                $containers = $service->containerName; 
            } else {
                $containers = [$service->containerName];
            }
            ?>
            <?php if ($service->luks !== null) : ?>
                <?php
                $luksDevice = $service->luks;
                $mountpoint = exec('cat /proc/mounts | grep "/dev/mapper/' . $luksDevice->mountPoint . ' /mnt/' . $luksDevice->mountPoint . '"');
                if (empty($mountpoint)) {
                    Util\createBanner('✗', '/dev/mapper/' . $luksDevice->mountPoint . ' is not mounted at /mnt/' . $luksDevice->mountPoint, 'bad');
                } else {
                    Util\createBanner('✓', '/dev/mapper/' . $luksDevice->mountPoint . ' is mounted at /mnt/' . $luksDevice->mountPoint, 'good');
                }
                ?>
                <p class="control-list">
                    <a href="mount.php?service=<?php echo $service->name ?>">[Mount device or provide encryption key]</a>
                </p>
                <p>
                <details>
                    <summary>Output</summary>

                    <code>
                        <?php echo $mountpoint ?>
                    </code>
                </details>
                </p>
            <?php endif; ?>
            <?php foreach ($containers as $containerName) : ?>
                <?php
                $status = Util\getDockerStatus($containerName);
                Util\createStatusBanner($status);
                ?>
                <?php if ($status->isNotFound === false) : ?>
                    <p class="control-list">
                        <a href="manage.php?container=<?php echo $containerName ?>">[Manage container]</a>
                    </p>
                    <p>
                    <details>
                        <summary>Status as reported by Docker</summary>

                        <?php Util\createStatusTable($status) ?>
                    </details>
                    </p>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</body>
