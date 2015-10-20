<?php

$url = 'http://electr.trafficmanager.net/dare';

$connection = curl_init();
curl_setopt($connection, CURLOPT_URL, $url);

$headers = [
    'Host: electr.trafficmanager.net',
    'Origin: http://ici.radio-canada.ca',
    'Referer: http://ici.radio-canada.ca/resultats-elections-canada-2015/'
];

curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);

$data = curl_exec($connection);

curl_close($connection);

header('Content-Type: application/json');

echo json_encode($data);