<?php

/**
 * We need open data canada.ca, kthx
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
function fetch_data() {
    $url = 'http://electr.trafficmanager.net/dare';

    $connection = curl_init();
    curl_setopt($connection, CURLOPT_URL, $url);
    curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);

    $headers = [
        'Host: electr.trafficmanager.net',
        'Origin: http://ici.radio-canada.ca',
        'Referer: http://ici.radio-canada.ca/resultats-elections-canada-2015/'
    ];

    curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);

    $data = curl_exec($connection);

    curl_close($connection);

    if (empty($data)) {
        $data = file_get_contents('sample.json');
    } else {
        // SAVE THAT LOCAL CONTENT
        file_put_contents('sample.json', $data);
    }

    return $data;
}

$data = fetch_data();

// Basic interface
$app->get('/', function ( ) use ($app, $data) {
    echo 'Hey';
});

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
    $app->get('/candidates/', function () use ($app, $data) {

        $data = json_decode($data);

        $app->response()->headers->set('Content-Type', 'application/json');
        $app->response()->setStatus(200);

        //var_dump($data);
        echo json_encode($data->C);
    });

});

$app->run();