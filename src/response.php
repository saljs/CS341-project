<?php

/*
 * This class defines a response for an HTTP payload. 
 */
class HTTPResponse {
    //The HTTP status code (https://en.wikipedia.org/wiki/List_of_HTTP_status_codes)
    private $responseCode;
    
    //The payload object for this request
    private $payload;

    /*
     * Constructor
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
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json');
        echo json_encode($this->payload);
    }
}

/*
 * A custom success message
 */
function success($message="Success"): void {
    $output = new HTTPResponse();
    $payload->message = $message;
    $output->setPayload($payload);
    $output->complete();
}

/*
 * A custom 400 error message
 */
function error($message): void {
    $output = new HTTPResponse(400);
    $payload->message = $message;
    $output->setPayload($payload);
    $output->complete();
}
