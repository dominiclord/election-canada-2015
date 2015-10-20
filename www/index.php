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
    $now = new DateTime('now');

    $base_url = 'http://electr.trafficmanager.net/dare?pts=' . $now->format('YmdHis');

    //var_dump($base_url);
    //die();

    $base_connection = curl_init();
    curl_setopt($base_connection, CURLOPT_URL, $base_url);
    curl_setopt($base_connection, CURLOPT_RETURNTRANSFER, 1);

    $base_headers = [
        'Host: electr.trafficmanager.net',
        'Origin: http://ici.radio-canada.ca',
        'Referer: http://ici.radio-canada.ca/resultats-elections-canada-2015/'
    ];

    curl_setopt($base_connection, CURLOPT_HTTPHEADER, $base_headers);

    $base_data = curl_exec($base_connection);

    curl_close($base_connection);

    /*
    $update_url = 'http://electr.trafficmanager.net/ren?pts=20151019225130';

    $update_connection = curl_init();
    curl_setopt($update_connection, CURLOPT_URL, $update_url);
    curl_setopt($update_connection, CURLOPT_RETURNTRANSFER, 1);

    $update_headers = [
        'Host: electr.trafficmanager.net',
        'Origin: http://ici.radio-canada.ca',
        'Referer: http://ici.radio-canada.ca/resultats-elections-canada-2015/',
        'Last-Modified: Tue, 20 Oct 2015 03:53:02 GMT'
    ];

    curl_setopt($update_connection, CURLOPT_HTTPHEADER, $update_headers);

    $update_data = curl_exec($update_connection);

    curl_close($update_connection);

    //if (empty($base_data) || empty($update_data)) {
    //    $base_data = file_get_contents('sample.json');
    //} else {
    //    // SAVE THAT LOCAL CONTENT
    //    file_put_contents('sample.json', $base_data);
    //}

    $results = [];

    echo($update_data);
    die();

    foreach ($update_data as $array) {
        //$results = array_merge_recursive($results, $array);
        echo($array);
        die();
    }
    die();

    var_dump($results);
    die();
    */

    return $base_data;
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
    try {
        $response = [
            'results' => json_decode($data),
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
     *
     * @param $app   Application
     * @param $data  ALL DATA
     */
    $app->get('/parties/:id', function ($id = null) use ($app, $data) {
        try {
            $data = json_decode($data);

            /**
             * @param  I        int     District ID
             * @param  IEC      int     Official Elections Canada district ID
             * @param  NAME     string  French district name
             * @param  NAME_EN  string  English district name
             * @param  V        int     Number of registered voters
             * @param  C        array   List of candidate IDs
             * @param  IP       int     Incumbent party
             * @param  ITP      int     Winning party
             * @param  R        string  ? (values of "e" or "a" sometimes)
             * @param  N        int     ? (some sort of random integer)
             * @param  PC       int     Counted number of polling booths
             * @param  PT       int     Total number of polling booths
             * @param  TS       string  ? (some sort of timestamp)
             * @param  _TS      string  ? (some sort of timestamp)
             * @param  KR       int     ? (only seen as zero)
             * @param  LG       int     ? (only seen as zero)
             */
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