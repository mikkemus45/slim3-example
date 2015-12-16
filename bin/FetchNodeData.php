<?php namespace trt\loraweather;

use PDO;
use DateTime;
use DateInterval;

$nodes = [
    '02031001',
];

$feed = 'http://thethingsnetwork.org/api/v0/nodes/%s/?limit=100';

$json = file_get_contents(sprintf($feed, $nodes[0]));
$data = json_decode($json, true);

$db = require __DIR__ . '/../app/db.php';

usort($data, function ($a, $b) {
    $time1 = new DateTime($a['time']);
    $time2 = new DateTime($b['time']);

    return $time1 > $time2;
});

foreach ($data as $nodeData) {
    $stmt = $db->prepare("INSERT INTO sensordata(time, data_plain, gateway_eui, node_eui) VALUES(?, ?, ?, ?)");

    $time = new DateTime($nodeData['time']);
    #$time->add(new DateInterval('PT1H'));

    try {
        $stmt->execute([
            $time->format(DateTime::ISO8601),
            $nodeData['data_plain'],
            $nodeData['gateway_eui'],
            $nodeData['node_eui'],
        ]);
    } catch (\PDOException $e) {
        print 'Skipped duplicate.' . PHP_EOL;
    }
}
