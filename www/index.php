<?php

/**
 * We need open data canada.gov, kthx
 *
 * @author Dominic Lord <dlord@outlook.com>
 * @copyright 2015 dominiclord
 * @version 2015-10-19
 * @link http://github.com/dominiclord/election-canada-2015
 * @since Version 2015-10-19
 */

use \Slim\Slim as Slim;

require_once '../vendor/autoload.php';

$app = new Slim([
    'view'           => new \Slim\Mustache\Mustache(),
    'debug'          => true,
    'templates.path' => 'templates'
]);

// Basically all the data from SRC/CBC

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

/**
 * Main API group
 * @param $app   Application
 * @param $data  ALL DATA
 */
$app->group('/api', function () use ($app, $data) {

    /**
     * Fetch all posts
     * @todo Add authentification
     * @param $app   Application
     * @param $data  ALL DATA
     */
    $app->get('/candidates', function () use ($app, $data) {
        $app->response()->headers->set('Content-Type', 'application/json');
        $app->response()->setStatus(200);
        echo json_encode($data);
    });

});