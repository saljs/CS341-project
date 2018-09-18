<?php 

// MySQL connection variables
$MYSQLSERVER = "localhost";
$MYSQLDB = "sals5552_cart";
$MYSQLUSER = "sals5552_admin";
$MYSQLPASS = "A;nRmHG}xWwi";

// Create MySQL connection
$database = new mysqli($MYSQLSERVER, $MYSQLUSER, $MYSQLPASS, $MYSQLDB);

// Check connection
if ($database->connect_error) {
    die("MySQL connection failed: " . $database->connect_error);
}

/*
 * This class defines a response for an HTTP payload. 
 */
class HTTPResponse {
    //The HTTP status code (https://en.wikipedia.org/wiki/List_of_HTTP_status_codes)
    private var $responseCode;
    
    //The payload object for this request
    private var $payload;

    /*
     * Default constructor, assumes a status code of 200, Success.
     */
    public function __construct() {
        $this->responseCode = 200;
    }

    /*
     * Constructor that allows different status codes.
     * @param code: the HTTP status code for this response.
     */
    public function __construct($code) {
        $this->responseCode = $code;
    }

    /*
     * Sets the payload for this response
     * @param object: The payload to be returned as JSON.
     */
    public function setPayload($object) {
        $this-payload = $object;
    }

    /*
     * Returns the response
     */
    public function complete() {
        http_response_code($this->responseCode);
        header('Content-Type: application/json');
        echo json_encode($this->payload);
    }
}

$urlText = array();
preg_match("\/([A-Z]|[a-z]*)", $_SERVER['REQUEST_URI'], $urlText);

require "controllers/" . $urlText[0] . ".php";

$run = $urlText[0] . "::" . $urlText[1];

if(is_callable($run)) {
    $response = call_user_func($run);
    $response->complete();
}
else {
    $response = new HTTPResponse(400);
    $payload->message = 'Error: no such function "' . $run . '"';
    $response->setPayload($payload);
    $response->complete();
}
