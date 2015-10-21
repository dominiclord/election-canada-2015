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

    // ======================
    // Code that was used on results night
    // ======================
    /*
    $now = new DateTime('now');
    $base_url = 'http://electr.trafficmanager.net/dare?pts=' . $now->format('YmdHis');

    $base_connection = curl_init();
    curl_setopt($base_connection, CURLOPT_URL, $base_url);
    curl_setopt($base_connection, CURLOPT_RETURNTRANSFER, 1);

    $base_headers = [
        'Host: electr.trafficmanager.net',
        'Origin: http://ici.radio-canada.ca',
        'Referer: http://ici.radio-canada.ca/resultats-elections-canada-2015/'
    ];

    curl_setopt($base_connection, CURLOPT_HTTPHEADER, $base_headers);

    $data = curl_exec($base_connection);

    curl_close($base_connection);
    */

    $data = file_get_contents('data.json');
    return json_decode($data);
}


// Basically refetch all the data from SRC/CBC
function update_data() {
    // The results are nice and final and archived, so until I make my own version of this data...
    $data = file_get_contents('http://ici.radio-canada.ca/resultats-elections-canada-2015/scripts/archive.final.dare.js?ts=20151020120005');

    // Some pesky JS vars
    $data = substr($data, 19, -1);
    if (!empty($data)) {
        // Test the JSON
        if(is_valid_json($data)) {
            // SAVE THAT LOCAL CONTENT
            file_put_contents('data.json', $data);
        } else {
            throw new Exception('Invalid JSON');
            exit();
        }
    } else {
        throw new Exception('Empty data from SRC/CBC');
        exit();
    }

    return json_decode($data);
}

/**
 * Test if string is proper JSON
 * @param   string   $data_string  String of potential JSON data
 * @return  string
 * @see     http://stackoverflow.com/a/15198925
 */
function is_valid_json($data_string) {
    // decode the JSON data
    $result = json_decode($data_string);

    // switch and check possible JSON errors
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = ''; // JSON is valid // No error has occurred
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        // PHP >= 5.3.3
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        // PHP >= 5.5.0
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }

    if ($error !== '') {
        // throw the Exception or exit // or whatever :)
        return false;
    }

    // everything is OK
    return true;
}

/**
 * For finding data in this HUUUUUGE array
 *
 * @param  array   $array  Hay
 * @param  string  $index  Strain of hay
 * @param  mixed   $value  Needle
 * @see http://stackoverflow.com/a/28970200
 */
function objArraySearch($array, $index, $value){
    $item = null;
    foreach($array as $arrayInf) {
        if($arrayInf->{$index}==$value){
            return $arrayInf;
        }
    }
    return $item;
}

/**
 * Dump of all data
 *
 * @param  $app  Application
 */
$app->get('/', function () use ($app) {

    $data = fetch_data();

    try {
        $response = [
            'results' => $data,
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

// Basic interface
$app->get('/update', function () use ($app, $data) {
    try {
        $data = update_data();

        $response = [
            'results' => $data,
            'status' => 'OK'
        ];
    } catch(Exception $e) {
        $response = [
            'error_message' => $e,
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
$app->group('/api', function () use ($app) {
    $data = fetch_data();

    /**
     * Fetch all candidates
     * @param $app   Application
     * @param $data  ALL DATA
     */
    $app->get('/candidates/', function () use ($app, $data) {
        try {
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