<?php
namespace ServiceDefinitions;

class ServiceDefinition
{
    public $name;
    public $prettyName;
    public $containerName;
    public $luks;

    public function __construct($name, $prettyName, $containerName, $luks)
    {
        $this->name = $name;
        $this->prettyName = $prettyName;
        $this->containerName = $containerName;
        $this->luks = $luks;
    }
}

class LuksDataDisk
{
    public $deviceName;
    public $uuid;
    public $mountPoint;

    public function __construct($deviceName, $uuid, $mountPoint)
    {
        $this->deviceName = $deviceName;
        $this->uuid = $uuid;
        $this->mountPoint = $mountPoint;
    }
}

$services = [
    new ServiceDefinition('vaultwarden', 'Vaultwarden', 'vaultwarden', null),
    new ServiceDefinition('nextcloud', 'Nextcloud', ['nextcloud', 'nextcloud_db'], new LuksDataDisk('mmcblk0p3', '19537ab9-d855-416c-99c3-ebc85e02bfbf', 'cloud')),
    new ServiceDefinition('jellyfin', 'Jellyfin', 'jellyfin', new LuksDataDisk('sda', '12158df0-2738-4c32-a7b9-36c11dde427f', 'media')),
    new ServiceDefinition('minecraft', 'Minecraft server', 'minecraft', null),
];

function getServiceDefinition($name) {
    global $services;

    $matching = array_filter($services, function ($service) use ($name) { return $service->name === $name; });
    if (count($matching) === 0) {
        return null;
    }
    return reset($matching);
}

?>

