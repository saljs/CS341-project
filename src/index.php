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
    private $responseCode;
    
    //The payload object for this request
    private $payload;

    /*
     * Constructor.
     * @param code: the HTTP status code for this response.
     */
    public function __construct($code=200) {
        $this->responseCode = $code;
    }

    /*
     * Sets the payload for this response
     * @param object: The payload to be returned as JSON.
     */
    public function setPayload($object) {
        $this->payload = $object;
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
    $response = new HTTPResponse(400);
    $payload->message = 'Error: controller not provided';
    $response->setPayload($payload);
    $response->complete();
}

//Close the connection to the database
$database->close();
