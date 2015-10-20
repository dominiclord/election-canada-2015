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

// For finding data in this HUUUUUGE array
// @see http://stackoverflow.com/a/28970200
function objArraySearch($array, $index, $value){
    $item = null;
    foreach($array as $arrayInf) {
        if($arrayInf->{$index}==$value){
            return $arrayInf;
        }
    }
    return $item;
}

$data = fetch_data();

// Basic interface
$app->get('/', function ( ) use ($app, $data) {
    $app->response()->headers->set('Content-Type', 'application/json');
    $app->response()->setStatus(200);
    echo $data;
    die();
});

/**
 * Main API group
 * @param $app   Application
 * @param $data  ALL DATA
 */
$app->group('/api', function () use ($app, $data) {

    /**
     * Fetch all candidates
     * @param $app   Application
     * @param $data  ALL DATA
     */
    $app->get('/candidates/', function () use ($app, $data) {
        try {
            $data = json_decode($data);

            $response = [
                'results' => $data->C,
                'status' => 'OK'
            ];
        } catch(Exception $e) {
            $response = [
                'error_message' => 'Bad request, I dunno',
                'results' => [],
                'status' => 'ERROR'
            ];
        }

        $app->response()->headers->set('Content-Type', 'application/json');
        $app->response()->setStatus(200);
        echo json_encode($response);
        die();
    });

    /**
     * Fetch specific results for a candidate
     * @param $app   Application
     * @param $data  ALL DATA
     */
    $app->get('/candidates/:id', function ($id = null) use ($app, $data) {

        try {
            $data = json_decode($data);

            $candidate = objArraySearch($data->C, 'I', $id);

            $response = [
                'results' => $candidate,
                'status' => 'OK'
            ];
        } catch(Exception $e) {
            $response = [
                'error_message' => 'Bad request, I dunno',
                'results' => [],
                'status' => 'ERROR'
            ];
        }

        $app->response()->headers->set('Content-Type', 'application/json');
        $app->response()->setStatus(200);
        echo json_encode($response);
        die();
    });

    /**
     * Fetch all districts
     * @param $app   Application
     * @param $data  ALL DATA
     */
    $app->get('/districts/', function () use ($app, $data) {
        try {
            $data = json_decode($data);

            $response = [
                'results' =>$data->R,
                'status' => 'OK'
            ];
        } catch(Exception $e) {
            $response = [
                'error_message' => 'Bad request, I dunno',
                'results' => [],
                'status' => 'ERROR'
            ];
        }

        $app->response()->headers->set('Content-Type', 'application/json');
        $app->response()->setStatus(200);
        echo json_encode($response);
        die();
    });

    /**
     * Fetch specific results for a district
     * @param $app   Application
     * @param $data  ALL DATA
     */
    $app->get('/districts/:id', function ($id = null) use ($app, $data) {
        try {
            $data = json_decode($data);

            $district = objArraySearch($data->R, 'I', $id);

            $response = [
                'results' => $district,
                'status' => 'OK'
            ];
        } catch(Exception $e) {
            $response = [
                'error_message' => 'Bad request, I dunno',
                'results' => [],
                'status' => 'ERROR'
            ];
        }

        $app->response()->headers->set('Content-Type', 'application/json');
        $app->response()->setStatus(200);
        echo json_encode($response);
        die();
    });

    /**
     * Fetch all parties
     * @param $app   Application
     * @param $data  ALL DATA
     */
    $app->get('/parties/', function () use ($app, $data) {
        try {
            $data = json_decode($data);

            $response = [
                'results' => $data->P,
                'status' => 'OK'
            ];
        } catch(Exception $e) {
            $response = [
                'error_message' => 'Bad request, I dunno',
                'results' => [],
                'status' => 'ERROR'
            ];
        }

        $app->response()->headers->set('Content-Type', 'application/json');
        $app->response()->setStatus(200);
        echo json_encode($response);
        die();
    });

    /**
     * Fetch specific results for a district
     * @param $app   Application
     * @param $data  ALL DATA
     */
    $app->get('/parties/:id', function ($id = null) use ($app, $data) {
        try {
            $data = json_decode($data);

            $party = objArraySearch($data->P, 'I', $id);

            $response = [
                'results' => $party,
                'status' => 'OK'
            ];
        } catch(Exception $e) {
            $response = [
                'error_message' => 'Bad request, I dunno',
                'results' => [],
                'status' => 'ERROR'
            ];
        }

        $app->response()->headers->set('Content-Type', 'application/json');
        $app->response()->setStatus(200);
        echo json_encode($response);
        die();
    });

});

$app->run();