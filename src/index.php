<?php 

/*
 * This page is responsible for parsing the URL and calling the 
 * correct controller and function. It also handles the database 
 * connection safely.
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/response.php';

// MySQL connection variables
$MYSQLSERVER = "localhost";
$MYSQLDB = "sals5552_cart_" . getBusinessID();
$MYSQLUSER = "sals5552_admin";
$MYSQLPASS = "A;nRmHG}xWwi";

// Create MySQL connection
$database = new mysqli($MYSQLSERVER, $MYSQLUSER, $MYSQLPASS, $MYSQLDB);
$GLOBALS['database'] = $database;

// Check connection
if ($database->connect_error) {
    die("MySQL connection failed: " . $database->connect_error);
}

/*
 * Return the id of the current buisness making the request.
 * Defaults to 'debug'
 */
function getBusinessID(): string {
    $site = "debug";
    if($_SERVER['HTTP_ORIGIN']) {
        $site = preg_replace(array('/(^\w+:|^)\/\/', '/\b\/.*/'), "", $_SERVER['HTTP_ORIGIN']);
    }
    return $site;
}

//Call the appropriate function
if(preg_match_all("/\/(([A-Z]|[a-z])+)/", $_SERVER['REQUEST_URI'], $urlText)) {

    //Check if the controller exists
    if(file_exists("controllers/" . $urlText[1][0] . ".php")) {
        require "controllers/" . $urlText[1][0] . ".php";
        
        $run = $urlText[1][0] . "::" . $urlText[1][1];

        //Check if the function exists in the controller
        if(is_callable($run)) {
            if($_SERVER['REQUEST_METHOD'] == 'POST') {
                call_user_func($run($_POST));
            }
            if($_SERVER['REQUEST_METHOD'] == 'PUT') {
                call_user_func($run($_PUT));
            }
            if($_SERVER['REQUEST_METHOD'] == 'DELETE') {
                call_user_func($run($_DELETE));
            }
            else {
                call_user_func($run($_GET));
            }
        }
        else {
            $response = new HTTPResponse(400);
            $payload->message = 'Error: no such function "' . $run . '"';
            $response->setPayload($payload);
            $response->complete();
        }
    }
    else {
        $response = new HTTPResponse(400);
        $payload->message = 'Error: no such controller "' . $urlText[1][0] . '"';
        $response->setPayload($payload);
        $response->complete();
    }
}
else {
    readfile("home.html");
}

//Close the connection to the database
$database->close();
